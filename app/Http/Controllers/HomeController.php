<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the PizzaFlow landing page.
     */
    public function index(): View
    {
        $featuredPizzas = [
            [
                'name' => 'Margherita Classic',
                'description' => 'Fresh mozzarella, basil & San Marzano tomato sauce.',
                'price' => 1890,
                'rating' => 4.9,
                'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=600&q=80',
            ],
            [
                'name' => 'Pepperoni Feast',
                'description' => 'Loaded pepperoni with melted mozzarella cheese.',
                'price' => 2290,
                'rating' => 4.8,
                'image' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=600&q=80',
            ],
            [
                'name' => 'BBQ Chicken',
                'description' => 'Grilled chicken, BBQ sauce, red onions & cilantro.',
                'price' => 2490,
                'rating' => 4.7,
                'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&q=80',
            ],
            [
                'name' => 'Veggie Supreme',
                'description' => 'Bell peppers, olives, mushrooms, onions & tomatoes.',
                'price' => 2190,
                'rating' => 4.6,
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600&q=80',
            ],
            [
                'name' => 'Four Cheese',
                'description' => 'Mozzarella, gorgonzola, parmesan & ricotta blend.',
                'price' => 2590,
                'rating' => 4.9,
                'image' => 'https://images.unsplash.com/photo-1571407970349-bc81e7e96d47?w=600&q=80',
            ],
            [
                'name' => 'Spicy Diavola',
                'description' => 'Hot salami, chili flakes & spicy tomato sauce.',
                'price' => 2390,
                'rating' => 4.8,
                'image' => 'https://images.unsplash.com/photo-1593560708920-61dd98c46a4e?w=600&q=80',
            ],
            [
                'name' => 'Hawaiian Delight',
                'description' => 'Ham, pineapple & mozzarella on a golden crust.',
                'price' => 2090,
                'rating' => 4.5,
                'image' => 'https://images.unsplash.com/photo-1565299507177-b0ac66763828?w=600&q=80',
            ],
            [
                'name' => 'Truffle Mushroom',
                'description' => 'Wild mushrooms, truffle oil & aged parmesan.',
                'price' => 2890,
                'rating' => 5.0,
                'image' => 'https://images.unsplash.com/photo-1458642849426-cfb724f15ef7?w=600&q=80',
            ],
        ];

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

        return view('home.index', compact('featuredPizzas', 'testimonials'));
    }
}
