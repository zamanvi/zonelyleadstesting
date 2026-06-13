<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blogs = Blog::latest()->paginate(20);
        return view('admin.blog2.index', compact('blogs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $blogs = Blog::latest()->paginate(20);
        return view('admin.blog2.index', compact('blogs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        $blogs = Blog::createStore($request);
        if ($blogs) {
            return back()->with('success', 'New blog "' . $request['name'] . '" created successfull.!');
        } else {
            return back()->with('warning', 'Error check all data again and submit again...!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $blog = Blog::findOrFail($id);
        $blogs = Blog::paginate(20);
        return view('admin.blog2.show', compact('blogs', 'blog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $blog = Blog::findOrFail($id);
        $blogs = Blog::paginate(20);
        return view('admin.blog2.edit', compact('blogs', 'blog'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);
        $name = $blog->name;
        Blog::updateStore($request, $id);
        return redirect(route('admin.blogs.show', $id))->with('success', 'Blog "' . $name . '" updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);
        $name = $blog->name;
        if ($blog->image_path) {
            delete_file($blog->image_path);
        }
        $blog->delete();
        return redirect(route('admin.blogs.index'))->with('warning', 'Blog "' . $name . '" deleted.');
    }
}
