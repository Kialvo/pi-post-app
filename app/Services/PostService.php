<?php
namespace App\Services;

use App\Models\Post;

class PostService
{
    public function createPost($data)
    {
        return Post::create($data);
    }

    public function updatePost(Post $post, $data)
    {
        return $post->update($data);
    }

    public function deletePost(Post $post)
    {
        return $post->delete();
    }

    public function getPosts()
    {
        return Post::query();
    }
}
