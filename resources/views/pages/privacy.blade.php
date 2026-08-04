@extends('layouts.app')

@section('title', 'Privacy Policy — PizzaFlow')
@section('meta_description', 'Learn how PizzaFlow collects, uses, and protects your personal information.')

@section('content')
<section class="pf-legal-page">
    <div class="container">
        <div class="pf-legal-card" data-aos="fade-up">
            <span class="pf-eyebrow">Legal</span>
            <h1 class="pf-dash-heading">Privacy Policy</h1>
            <p class="pf-legal-updated">Last updated: August 4, 2025</p>

            <div class="pf-legal-content">
                <h2>1. Information We Collect</h2>
                <p>When you use PizzaFlow, we may collect your name, email, phone number, delivery addresses, order history, and account preferences to provide our pizza ordering service.</p>

                <h2>2. How We Use Your Information</h2>
                <p>We use your information to process orders, arrange delivery, send order updates, improve our menu and service, and keep your account secure.</p>

                <h2>3. Sharing of Information</h2>
                <p>We do not sell your personal data. Information may be shared only with delivery partners, payment processors, or when required by law.</p>

                <h2>4. Data Security</h2>
                <p>We use secure practices to protect your account and order details. You are responsible for keeping your password private.</p>

                <h2>5. Your Choices</h2>
                <p>You can update your profile, delivery addresses, and preferences anytime from your account. You may also contact us to request account deletion.</p>

                <h2>6. Contact Us</h2>
                <p>For privacy questions, email <a href="mailto:hello@pizzaflow.com">hello@pizzaflow.com</a> or call +94 11 223 3445.</p>
            </div>

            <a href="{{ route('home') }}" class="btn btn-pf-primary mt-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Home
            </a>
        </div>
    </div>
</section>
@endsection
