@extends('layouts.app')

@section('title', 'PizzaFlow — Fresh Pizza Delivered Hot & Fast')
@section('meta_description', 'Order fresh, customized pizza online with PizzaFlow. Fast doorstep delivery, expert chefs, and secure payments.')

@section('content')
    {{-- Hero --}}
    @include('components.hero')

    {{-- Featured Pizzas / Dynamic Menu --}}
    @include('components.featured-pizzas', [
        'featuredPizzas' => $featuredPizzas,
        'menuItems' => $menuItems,
        'categories' => $categories
    ])

    {{-- Today's Special Offers --}}
    @include('components.offers')

    {{-- Why Choose PizzaFlow --}}
    @include('components.why-choose')

    {{-- Customer Testimonials --}}
    @include('components.testimonials')

    {{-- Mobile App Promotion --}}
    @include('components.mobile-app')

    {{-- Newsletter --}}
    @include('components.newsletter')

    {{-- Customization Modal --}}
    @include('components.customizer-modal', ['customizerData' => $customizerData])
@endsection
