<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Support\PizzaCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the PizzaFlow landing page.
     * Staff roles are redirected to their dashboards.
     */
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user && ! $user->isCustomer()) {
            return redirect()->route($user->dashboardRoute());
        }

        $favoriteSlugs = [];

        if ($user) {
            $favoriteSlugs = Favorite::query()
                ->where('user_id', $user->_id)
                ->pluck('pizza_slug')
                ->all();
        }

        $testimonials = [
            [
                'name' => 'Amaya Fernando',
                'photo' => 'https://i.pravatar.cc/150?img=47',
                'rating' => 5,
                'comment' => 'Best pizza in town! The crust is perfect and delivery was under 25 minutes. PizzaFlow has become our Friday night tradition.',
            ],
            [
                'name' => 'Kasun Perera',
                'photo' => 'https://i.pravatar.cc/150?img=12',
                'rating' => 5,
                'comment' => 'Love the customization options. Half-and-half pizza with extra toppings arrived hot and delicious. Highly recommend!',
            ],
            [
                'name' => 'Nimali Silva',
                'photo' => 'https://i.pravatar.cc/150?img=32',
                'rating' => 4,
                'comment' => 'The app tracking is so convenient. Fresh ingredients every time and the weekend combo deals are unbeatable.',
            ],
            [
                'name' => 'Dilshan Jayawardena',
                'photo' => 'https://i.pravatar.cc/150?img=15',
                'rating' => 5,
                'comment' => 'Professional packaging, amazing flavors, and secure payments. PizzaFlow sets the standard for online pizza ordering.',
            ],
        ];

        return view('home.index', [
            'featuredPizzas' => PizzaCatalog::all(),
            'favoriteSlugs' => $favoriteSlugs,
            'testimonials' => $testimonials,
        ]);
    }
}
