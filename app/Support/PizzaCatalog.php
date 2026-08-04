<?php

namespace App\Support;

class PizzaCatalog
{
    /**
     * Featured pizza catalog used across home + favorites.
     *
     * @return list<array{slug: string, name: string, description: string, price: int, rating: float, image: string}>
     */
    public static function all(): array
    {
        return [
            [
                'slug' => 'margherita-classic',
                'name' => 'Margherita Classic',
                'description' => 'Fresh mozzarella, basil & San Marzano tomato sauce.',
                'price' => 1890,
                'rating' => 4.9,
                'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=600&q=80',
            ],
            [
                'slug' => 'pepperoni-feast',
                'name' => 'Pepperoni Feast',
                'description' => 'Loaded pepperoni with melted mozzarella cheese.',
                'price' => 2290,
                'rating' => 4.8,
                'image' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=600&q=80',
            ],
            [
                'slug' => 'bbq-chicken',
                'name' => 'BBQ Chicken',
                'description' => 'Grilled chicken, BBQ sauce, red onions & cilantro.',
                'price' => 2490,
                'rating' => 4.7,
                'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&q=80',
            ],
            [
                'slug' => 'veggie-supreme',
                'name' => 'Veggie Supreme',
                'description' => 'Bell peppers, olives, mushrooms, onions & tomatoes.',
                'price' => 2190,
                'rating' => 4.6,
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600&q=80',
            ],
            [
                'slug' => 'four-cheese',
                'name' => 'Four Cheese',
                'description' => 'Mozzarella, gorgonzola, parmesan & ricotta blend.',
                'price' => 2590,
                'rating' => 4.9,
                'image' => 'https://images.unsplash.com/photo-1571407970349-bc81e7e96d47?w=600&q=80',
            ],
            [
                'slug' => 'spicy-diavola',
                'name' => 'Spicy Diavola',
                'description' => 'Hot salami, chili flakes & spicy tomato sauce.',
                'price' => 2390,
                'rating' => 4.8,
                'image' => 'https://images.unsplash.com/photo-1593560708920-61dd98c46a4e?w=600&q=80',
            ],
            [
                'slug' => 'hawaiian-delight',
                'name' => 'Hawaiian Delight',
                'description' => 'Ham, pineapple & mozzarella on a golden crust.',
                'price' => 2090,
                'rating' => 4.5,
                'image' => 'https://images.unsplash.com/photo-1565299507177-b0ac66763828?w=600&q=80',
            ],
            [
                'slug' => 'truffle-mushroom',
                'name' => 'Truffle Mushroom',
                'description' => 'Wild mushrooms, truffle oil & aged parmesan.',
                'price' => 2890,
                'rating' => 5.0,
                'image' => 'https://images.unsplash.com/photo-1458642849426-cfb724f15ef7?w=600&q=80',
            ],
        ];
    }

    public static function find(string $slug): ?array
    {
        foreach (self::all() as $pizza) {
            if ($pizza['slug'] === $slug) {
                return $pizza;
            }
        }

        return null;
    }
}
