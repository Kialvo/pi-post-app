<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Flag;
use Illuminate\Support\Facades\Log;
use Monolog\Logger;

class PostService
{
    /**
     * Create a new post.
     *
     * @param array $data
     * @return Post
     */
    public function createPost($data)
    {
        return Post::create($data);
    }

    /**
     * Update an existing post.
     *
     * @param Post $post
     * @param array $data
     * @return bool
     */
    public function updatePost(Post $post, $data)
    {
        return $post->update($data);
    }

    /**
     * Delete a post.
     *
     * @param Post $post
     * @return bool|null
     */
    public function deletePost(Post $post)
    {
        return $post->delete();
    }

    /**
     * Retrieve all posts with optional relationships.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getPosts()
    {
        return Post::query();
    }

    public function toggleFlag(int $postId): bool
    {
        $post = Post::find($postId);

        if (!$post) {
           // Log::error("toggleFlag: Post not found with ID {$postId}");
            return false;
        }

        if ($post->flag) {
            // If already flagged, remove the flag
            $post->flag->delete();
           // Log::info("toggleFlag: Removed flag for Post ID {$postId}");
            return false;
        } else {
            // If not flagged, add a new flag
            $flag = Flag::create(['post_id' => $postId]);

            if ($flag) {
               // Log::info("toggleFlag: Added flag for Post ID {$postId}");
                return true;
            } else {
               // Log::error("toggleFlag: Failed to add flag for Post ID {$postId}");
                return false;
            }
        }
    }

    /**
     * Check if a post is flagged.
     *
     * @param int $postId
     * @return bool
     */
    public function isFlagged(int $postId): bool
    {
        $post = Post::find($postId);
        //Log::info($post->flag);
        return $post && $post->flag ? true : false;
    }
}
