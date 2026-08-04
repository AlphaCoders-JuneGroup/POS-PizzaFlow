@extends('layouts.app')

@section('title', 'PizzaFlow — Fresh Pizza Delivered Hot & Fast')
@section('meta_description', 'Order fresh, customized pizza online with PizzaFlow. Fast doorstep delivery, expert chefs, and secure payments.')

@section('content')
    {{-- Hero --}}
    @include('components.hero')

    {{-- Featured Categories --}}
    @include('components.categories')

    {{-- Featured Pizzas --}}
    @include('components.featured-pizzas')

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
@endsection
