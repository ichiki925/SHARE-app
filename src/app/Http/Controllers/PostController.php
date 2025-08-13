<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $posts = Post::withCount(['likes', 'comments'])
                    ->with('comments')
                    ->latest()
                    ->get();

            return response()->json([
                'status' => 'success',
                'data' => $posts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => '投稿の取得に失敗しました'
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse

    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|string|max:50',
                'user_name' => 'required|string|max:20',
                'content' => 'required|string|max:120'
            ]);

            $post = Post::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => '投稿が作成されました',
                'data' => $post
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => '投稿の作成に失敗しました'
            ], 500);
        }
    }

    public function show(Post $post): JsonResponse
    {
        try {
            $post->loadCount(['likes', 'comments']);
            $post->load('comments');

            return response()->json([
                'status' => 'success',
                'data' => $post
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => '投稿の取得に失敗しました'
            ], 500);
        }
    }

    public function update(Request $request, Post $post): JsonResponse
    {
        try {
            $validated = $request->validate([
                'content' => 'sometimes|string|max:120',
            ]);

            $post->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => '投稿が更新されました',
                'data' => $post
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => '投稿の更新に失敗しました'
            ], 500);
        }
    }

    public function destroy(Post $post): JsonResponse
    {
        try {
            $post->delete();

            return response()->json([
                'status' => 'success',
                'message' => '投稿が削除されました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => '投稿の削除に失敗しました'
            ], 500);
        }
    }

    public function like(Request $request, Post $post): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|string|max:50',
                'user_name' => 'required|string|max:20'
            ]);

            $existingLike = Like::byPostAndUser($post->id, $validated['user_id'])->first();

            if ($existingLike) {
                return response()->json([
                    'status' => 'error',
                    'message' => '既にいいねしています'
                ], 400);
            }

            DB::transaction(function () use ($post, $validated) {
                Like::create([
                    'post_id' => $post->id,
                    'user_id' => $validated['user_id'],
                    'user_name' => $validated['user_name']
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'いいねしました',
                'data' => $post->fresh()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'いいねに失敗しました'
            ], 500);
        }
    }

    public function unlike(Request $request, Post $post): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|string|max:50'
            ]);

            $like = Like::byPostAndUser($post->id, $validated['user_id'])->first();

            if (!$like) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'いいねしていません'
                ], 400);
            }

            DB::transaction(function () use ($like) {
                $like->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'いいねを取り消しました',
                'data' => $post->fresh()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'いいね取り消しに失敗しました'
            ], 500);
        }
    }

    public function checkLikeStatus(Request $request, Post $post): JsonResponse
    {
        try {
            $userId = $request->query('user_id');

            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ユーザーIDが必要です'
                ], 400);
            }

            $isLiked = Like::isLikedByUser($post->id, $userId);
            $likesCount = Like::getPostLikesCount($post->id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'is_liked' => $isLiked,
                    'likes_count' => $likesCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'いいね状態の取得に失敗しました'
            ], 500);
        }
    }

    public function byUser(string $userId): JsonResponse
    {
        try {
            if (empty($userId) || strlen($userId) > 50) {
                return response()->json([
                    'status' => 'error',
                    'message' => '無効なユーザーIDです'
                ], 400);
            }

            $posts = Post::withCount(['likes', 'comments'])
                    ->byUser($userId)
                    ->latest()
                    ->get();

            return response()->json([
                'status' => 'success',
                'data' => $posts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => '投稿の取得に失敗しました'
            ], 500);
        }
    }

    public function getComments(string $postId): JsonResponse
    {
        try {
            $post = Post::findOrFail($postId);
            $comments = Comment::byPost($postId)->latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $comments
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => '投稿が見つかりません'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'コメントの取得に失敗しました'
            ], 500);
        }
    }

    public function storeComment(Request $request, string $postId): JsonResponse
    {
        try {
            $post = Post::findOrFail($postId);

            $validated = $request->validate([
                'user_id' => 'required|string|max:50',
                'user_name' => 'required|string|max:20',
                'content' => 'required|string|max:120'
            ]);

            $validated['post_id'] = $postId;

            $comment = DB::transaction(function () use ($validated, $post) {
                $comment = Comment::create($validated);
                return $comment;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'コメントが作成されました',
                'data' => $comment
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => '投稿が見つかりません'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'コメントの作成に失敗しました'
            ], 500);
        }
    }
}
