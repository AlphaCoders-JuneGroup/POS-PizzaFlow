@extends('layouts.app')

@section('title', 'Terms of Service — PizzaFlow')
@section('meta_description', 'Read the PizzaFlow terms of service for ordering, delivery, payments, and account use.')

@section('content')
<section class="pf-legal-page">
    <div class="container">
        <div class="pf-legal-card" data-aos="fade-up">
            <span class="pf-eyebrow">Legal</span>
            <h1 class="pf-dash-heading">Terms of Service</h1>
            <p class="pf-legal-updated">Last updated: August 4, 2025</p>

            <div class="pf-legal-content">
                <h2>1. Acceptance of Terms</h2>
                <p>By using PizzaFlow, creating an account, or placing an order, you agree to these Terms of Service.</p>

                <h2>2. Orders & Payments</h2>
                <p>All prices are shown in Sri Lankan Rupees (Rs.). Orders are confirmed after successful placement. Payment may be made online or as cash on delivery where available.</p>

                <h2>3. Delivery</h2>
                <p>Delivery times are estimates. Please provide accurate address and contact details. Additional delivery fees may apply based on location or order value.</p>

                <h2>4. Cancellations & Refunds</h2>
                <p>Orders can be cancelled before kitchen preparation begins. Refunds for eligible cancelled or failed orders are processed according to the original payment method.</p>

                <h2>5. Account Responsibility</h2>
                <p>You are responsible for activity under your account. Provide truthful information and keep your login details secure.</p>

                <h2>6. Acceptable Use</h2>
                <p>You agree not to misuse PizzaFlow, attempt unauthorized access, or place fraudulent orders.</p>

                <h2>7. Contact</h2>
                <p>For support, email <a href="mailto:hello@pizzaflow.com">hello@pizzaflow.com</a> or call +94 11 223 3445.</p>
            </div>

            <a href="{{ route('home') }}" class="btn btn-pf-primary mt-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Home
            </a>
        </div>
    </div>
</section>
@endsection
