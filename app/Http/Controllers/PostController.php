<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Flag;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class PostController extends Controller
{
    protected $postService;

    /**
     * Constructor to inject PostService.
     *
     * @param PostService $postService
     */
    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Display a listing of posts.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $posts = $this->postService->getPosts()->with('flag');

            return datatables()->of($posts)
                ->addColumn('actions', function ($row) {
                    return '
                    <a href="' . route('posts.edit', $row->id) . '" class="btn btn-sm btn-primary me-2">Edit</a>
                    <form action="' . route('posts.destroy', $row->id) . '" method="POST" style="display:inline;">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                ';
                })
                ->addColumn('flag', function ($row) {
                    return $row->flag ? 1 : 0; // Return 1 for flagged, 0 for unflagged
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('posts.index');
    }


    /**
     * Show the form for creating a new post.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created post in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Show the form for editing the specified post.
     *
     * @param Post $post
     * @return \Illuminate\View\View
     */
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified post in storage.
     *
     * @param Request $request
     * @param Post $post
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Remove the specified post from storage.
     *
     * @param Post $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Post $post)
    {
        $this->postService->deletePost($post);

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }

    /**
     * Toggle the flag status of a post.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleFlag(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'message' => 'Invalid post ID.'], 400);
        }

        $postId = $request->input('id');
        $isFlagged = $this->postService->toggleFlag($postId);

        return Response::json(['success' => true, 'is_flagged' => $isFlagged]);
    }
}
