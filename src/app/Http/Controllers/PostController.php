<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $posts = Post::latest()->get();

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
                'likes_count' => 'sometimes|integer|min:0'
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

    public function like(Post $post): JsonResponse
    {
        try {
            $post->increment('likes_count');

            return response()->json([
                'status' => 'success',
                'message' => 'いいねしました',
                'data' => $post
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'いいねに失敗しました'
            ], 500);
        }
    }

    public function byUser(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|string'
            ]);

            $posts = Post::byUser($validated['user_id'])->latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $posts
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
                'message' => '投稿の取得に失敗しました'
            ], 500);
        }
    }

    public function getComments($postId): JsonResponse
    {
        try {
            $post = Post::findOrFail($postId);
            $comments = Comment::where('post_id', $postId)
                            ->orderBy('created_at', 'desc')
                            ->get();

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

    public function storeComment(Request $request, $postId): JsonResponse
    {
        try {
            $post = Post::findOrFail($postId);
            $validated = $request->validate([
                'user_id' => 'required|string|max:50',
                'user_name' => 'required|string|max:20',
                'content' => 'required|string|max:120'
            ]);

            $validated['post_id'] = $postId;
            $comment = Comment::create($validated);

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
