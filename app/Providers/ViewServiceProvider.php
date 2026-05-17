<?php

namespace App\Providers;

use App\Models\Blog;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $route = Route::current();

            if (!$route) {
                return;
            }

            $routeName = $route->getName();

            // Ensure it's a string
            if (!is_string($routeName)) {
                return;
            }

            // ADMIN
            if (str_starts_with($routeName, 'admin.')) {
                $view->with(Cache::remember('admin_nav_counts', 120, function () {
                    return [
                        'blogCount'     => Blog::count(),
                        'categoryCount' => Category::count(),
                        'userCount'     => User::where('type', 'seller')->count(),
                    ];
                }));
            }

            // FRONTEND (includes auth routes like verification.notice, login, etc.)
            if (!str_starts_with($routeName, 'admin.')) {
                $allMenuCategories = Cache::remember('menu_categories', 300, function () {
                    return Category::whereNull('parent_id')
                        ->where('is_active', 1)
                        ->with(['children' => function ($q) {
                            $q->where('is_active', 1);
                        }])
                        ->get();
                });

                $view->with('allMenuCategories', $allMenuCategories);
            }
        });
    }
}
