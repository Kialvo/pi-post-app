<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $posts = $this->postService->getPosts();
            return datatables()->of($posts)
                ->addColumn('actions', function ($row) {
                    return '
                        <a href="/scrape/public/posts/' . $row->id . '/edit" class="btn btn-sm btn-primary me-2">Edit</a>
                        <form action="/scrape/public/posts/' . $row->id . '" method="POST" style="display:inline;">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('posts.index');
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'source' => 'required',
            'url' => 'required|url',
            'title' => 'required|string',
            'translated_title' => 'nullable|string',
            'original_post_content' => 'nullable|string',
            'translated_post_content' => 'nullable|string',
            'summary' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $this->postService->createPost($request->all());

        return redirect()->route('posts.index')->with('success', 'Post created successfully.');
    }

    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'source' => 'required',
            'url' => 'required|url',
            'title' => 'required|string',
            'translated_title' => 'nullable|string',
            'original_post_content' => 'nullable|string',
            'translated_post_content' => 'nullable|string',
            'summary' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $this->postService->updatePost($post, $request->all());

        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $this->postService->deletePost($post);

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }
}
