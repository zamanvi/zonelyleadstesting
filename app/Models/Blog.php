<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'image_path', 'short_description', 'description', 'keyword', 'pageview', 'pageview', 'user_id'];


    public static function createStore($request): bool
    {
        if ($request->hasFile('image_path')) {
            $image = upload_file($request->file('image_path'));
        } else {
            $image = '';
        }
        $slug = $request->slug != null ? make_slug($request->slug) : make_slug($request->name);
        while (self::where('slug', $slug)->exists()) {
            $slug = set_increment_slug(Blog::class, $slug);
        }
        $newEntry = self::create([
            'user_id' => auth()->user()->id,
            'name' => $request->name,
            'image_path' => $image,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'keyword' => $request->keyword,
            'slug' => $slug,
        ]);
        return $newEntry instanceof self;
    }

    public static function updateStore($request, $id): bool
    {
        $blog = Blog::find($id);
        if (!$blog) {
            return false;
        }
        $image = $blog->image_path;
        if ($request->hasFile('image_path') && $request->file('image_path')->isValid()) {
            if ($blog->image_path != null) {
                delete_file($blog->image_path);
            }
            $image = upload_file($request->file('image_path'));
        }
        $slug = $request->slug ? make_slug($request->slug) : ($blog->slug ?: make_slug($request->name));
        if ($slug != $blog->slug) {
            while (self::where('slug', $slug)->exists()) {
                $slug = set_increment_slug(Blog::class, $slug);
            }
        }
        $updateEntry = $blog->update([
            'name' => $request->name,
            'image_path' => $image,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'keyword' => $request->keyword,
            'slug' => $slug,
        ]);
        return $updateEntry;
    }

}
