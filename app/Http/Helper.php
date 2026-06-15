<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

if (!function_exists('getUserType')) {
    function getUserType()
    {
        return Auth::user()->type;
    }
}

if (!function_exists('make_slug')) {
    function make_slug($slug)
    {
        return Str::slug($slug);
    }
}

if (!function_exists('flattenCategories')) {
    function flattenCategories($categories, $level = 0) {
        $flat = [];
        foreach ($categories as $category) {
            $category->level = $level;
            $flat[] = $category;
            if ($category->children->count()) {
                $flat = array_merge($flat, flattenCategories($category->children, $level + 1));
            }
        }
        return $flat;
    }
}

if (!function_exists('categoryPath')) {
    function categoryPath($category)
    {
        $path = [];
        while ($category) {
            $path[] = $category->title;
            $category = $category->parent;
        }
        return implode(' => ', array_reverse($path));
    }
}

if (!function_exists('generateUniqueSlug')) {
    function generateUniqueSlug(string $modelClass, string $value, ?int $ignoreId = null, string $column = 'slug'): string
    {
        $slug = Str::slug($value);
        $original = $slug;
        $i = 1;

        while ($modelClass::where($column, $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $suffix = '-' . $i++;
            $slug = Str::limit($original, 255 - strlen($suffix), '') . $suffix;
        }

        return $slug;
    }
}

if (!function_exists('get_random_number')) {
    function get_random_number($length)
    {
        return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 5)), 0, $length);
    }
}

if (!function_exists('set_increment_slug')) {
    function set_increment_slug($target, $slug)
    {
        $max = $target::where('slug', 'LIKE', "{$slug}%")->latest('id')->value('slug');
        if ($max) {
            $parts = explode('-', $max);
            $slug = $slug . '-' . (intval(end($parts)) + 1);
        } else {
            $slug = $slug . '-1';
        }
        return $slug . '-' . get_random_number(10);
    }
}

if (!function_exists('upload_file')) {
    function upload_file($file, string $folder = 'uploads'): string
    {
        $filename = Str::random(64) . '.' . $file->getClientOriginalExtension();
        Storage::disk('public')->putFileAs($folder, $file, $filename);
        return $folder . '/' . $filename;
    }
}

if (!function_exists('delete_file')) {
    function delete_file(?string $path): bool
    {
        if (!$path) return false;
        $path = ltrim($path, '/');
        return Storage::disk('public')->exists($path)
            ? Storage::disk('public')->delete($path)
            : false;
    }
}

if (!function_exists('get_file')) {
    function get_file(?string $path, string $for = 'default'): string
    {
        if (!$path) return empty_image($for);
        if (str_starts_with($path, 'http')) return $path;
        $path = ltrim($path, '/');
        if (config('filesystems.disks.r2.key')) {
            return rtrim(config('filesystems.disks.r2.url'), '/') . '/' . $path;
        }
        return Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path)
            : empty_image($for);
    }
}

if (!function_exists('empty_image')) {
    function empty_image($type = 'default')
    {
        return match ($type) {
            'user'    => asset('images/user.png'),
            'blog'    => asset('images/blog.jpg'),
            'contest' => asset('images/contest.jpg'),
            default   => asset('images/no-image.jpg'),
        };
    }
}

if (!function_exists('try_decrypt')) {
    function try_decrypt(?string $value): string
    {
        if (!$value) return '';
        try {
            return decrypt($value);
        } catch (\Throwable $e) {
            return $value; // already plaintext (legacy records)
        }
    }
}
