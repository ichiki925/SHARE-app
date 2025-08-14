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
    private function getFirebaseUser(Request $request)
    {
        return $request->attributes->get('firebase_user');
    }

    public function index()
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

    public function store(Request $request)

    {
        try {
            $validated = $request->validate([
                'content' => 'required|string|max:120'
            ]);

            $user = $this->getFirebaseUser($request);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ユーザー認証が必要です'
                ], 401);
            }

            $validated['user_id'] = $user->id;
            $validated['user_name'] = $user->name ?? $user->email ?? 'Anonymous';

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

    public function show(Post $post)
    {
        try {
            $post->loadCount(['likes', 'comments']);
            $post->load('comments');

            $user = $this->getFirebaseUser(request());
            $postData = $post->toArray();
            $postData['is_owner'] = $user && $user->id == $post->user_id;

            return response()->json([
                'status' => 'success',
                'data' => $postData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => '投稿の取得に失敗しました'
            ], 500);
        }
    }

    public function update(Request $request, Post $post)
    {
        try {
            $user = $this->getFirebaseUser($request);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ユーザー認証が必要です'
                ], 401);
            }

            if ($user->id != $post->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => '他人の投稿は編集できません'
                ], 403);
            }

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

    public function destroy(Request $request, Post $post)
    {
        try {
            $user = $this->getFirebaseUser($request);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ユーザー認証が必要です'
                ], 401);
            }

            if ($user->id != $post->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => '他人の投稿は削除できません'
                ], 403);
            }

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

    public function like(Request $request, Post $post)
    {
        try {
            $user = $this->getFirebaseUser($request);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ユーザー認証が必要です'
                ], 401);
            }

            $existingLike = Like::byPostAndUser($post->id, $user->id)->first();

            if ($existingLike) {
                return response()->json([
                    'status' => 'error',
                    'message' => '既にいいねしています'
                ], 400);
            }

            DB::transaction(function () use ($post, $user) {
                Like::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                    'user_name' => $user->name ?? $user->email ?? 'Anonymous'
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'いいねしました',
                'data' => $post->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'いいねに失敗しました'
            ], 500);
        }
    }

    public function unlike(Request $request, Post $post)
    {
        try {
            $user = $this->getFirebaseUser($request);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ユーザー認証が必要です'
                ], 401);
            }

            $like = Like::byPostAndUser($post->id, $user->id)->first();

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
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'いいね取り消しに失敗しました'
            ], 500);
        }
    }

    public function checkLikeStatus(Request $request, Post $post)
    {
        try {
            $user = $this->getFirebaseUser($request);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ユーザー認証が必要です'
                ], 401);
            }

            $isLiked = Like::isLikedByUser($post->id, $user->id);
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

    public function byUser(string $userId)
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

    public function getComments(string $postId)
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

    public function storeComment(Request $request, string $postId)
    {
        try {
            $post = Post::findOrFail($postId);
            $user = $this->getFirebaseUser($request);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ユーザー認証が必要です'
                ], 401);
            }

            $validated = $request->validate([
                'content' => 'required|string|max:120'
            ]);

            $validated['post_id'] = $postId;
            $validated['user_id'] = $user->id;
            $validated['user_name'] = $user->name ?? $user->email ?? 'Anonymous';

            $comment = DB::transaction(function () use ($validated) {
                return Comment::create($validated);
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
