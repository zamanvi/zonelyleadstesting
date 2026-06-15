<?php

namespace App\Http\Controllers;

use App\Models\SellerGallery;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GalleryController extends Controller
{
    public function index()
    {
        $user    = Auth::user()->load('gallery');
        return view('frontend.profile.edit.gallery', compact('user'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->gallery()->count() >= 12) {
            return back()->withErrors(['photo' => 'Maximum 12 photos allowed.']);
        }

        $request->validate([
            'photo'   => 'required|image|max:10240',
            'caption' => 'nullable|string|max:150',
        ]);

        $path      = ImageOptimizer::saveGalleryPhoto($request->file('photo'));
        $nextOrder = ($user->gallery()->max('sort_order') ?? -1) + 1;

        SellerGallery::create([
            'user_id'    => $user->id,
            'image_path' => $path,
            'caption'    => $request->caption,
            'sort_order' => $nextOrder,
        ]);

        return back()->with('success', 'Photo added.');
    }

    public function destroy($id)
    {
        $photo = SellerGallery::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Delete actual file from R2 or local storage
        if ($photo->image_path) {
            if (str_starts_with($photo->image_path, 'http')) {
                $key = ltrim(parse_url($photo->image_path, PHP_URL_PATH), '/');
                \Illuminate\Support\Facades\Storage::disk('r2')->delete($key);
            } else {
                delete_file($photo->image_path);
            }
        }

        $photo->delete();

        return back()->with('success', 'Photo removed.');
    }

    public function captionUpdate(Request $request, $id)
    {
        $request->validate(['caption' => 'nullable|string|max:150']);

        SellerGallery::where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['caption' => $request->caption]);

        return response()->json(['ok' => true]);
    }
}
