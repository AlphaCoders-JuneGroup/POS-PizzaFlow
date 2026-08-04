# 🍕 PizzaFlow

A modern and efficient **Pizza Ordering System** built with **Laravel, PHP, and MongoDB** to simplify online pizza ordering, kitchen operations, delivery management, and business analytics.

> 🚀 Developed during the **EgotechWorld Internship Program**.

---

## 📖 Overview

PizzaFlow is designed to provide customers with a seamless online ordering experience while helping restaurant staff efficiently manage orders, kitchen workflows, deliveries, and inventory from a centralized platform.

The system reduces manual errors, speeds up order processing, and improves overall customer satisfaction.

---

## ✨ Features

### 👤 User Management
- Customer Registration & Login
- Guest Checkout
- User Profile Management
- Saved Delivery Addresses

### 🍕 Menu & Pizza Customization
- Browse Pizza Categories
- Select Pizza Size
- Choose Crust Type
- Customize Toppings
- Half & Half Pizza Support
- Extra / Light Toppings
- Dynamic Price Calculation

### 🛒 Shopping Cart
- Add & Remove Items
- Quantity Management
- Promo Code Support
- Tax & Delivery Fee Calculation

### 📦 Order Management
- Place Orders
- Pickup & Delivery Options
- Live Order Tracking
- Cancel Orders Before Preparation

### 💳 Payment System
- Secure Payment Processing
- Cash on Delivery
- Digital Receipts
- Payment Status Tracking

### 👨‍🍳 Kitchen Display System (KDS)
- Real-time Order Queue
- Preparation Status Updates
- Order Completion Tracking

### 🚚 Delivery Management
- Driver Assignment
- Delivery Tracking
- Route Information
- Delivery Status Updates

### 📊 Reporting & Analytics
- Daily & Monthly Sales Reports
- Popular Pizza Analysis
- Peak Ordering Hours
- Delivery Performance Reports

### 📦 Inventory Management
- Ingredient Stock Tracking
- Out-of-Stock Notifications
- Topping Availability Management

---

## 🛠 Tech Stack

### Backend
- Laravel
- PHP

### Database
- MongoDB

### Authentication
- Laravel Authentication

### Tools
- Composer
- Git & GitHub
- Postman

---

## 👥 User Roles

- Admin
- Store Manager
- Customer
- Kitchen Staff
- Delivery Driver

---

## 📂 Project Structure

```
PizzaFlow/
│
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
└── vendor/
```

---

## 🚀 Installation

### Clone Repository

```bash
git clone https://github.com/AlphaCoders-JuneGroup/POS-PizzaFlow.git
```

### Navigate

```bash
cd PizzaFlow
```

### Install Dependencies

```bash
composer install
```

### Configure Environment

```bash
cp .env.example .env
```

Update MongoDB Atlas credentials in `.env`:

```env
DB_CONNECTION=mongodb
MONGODB_URI=mongodb+srv://username:password@cluster0.example.mongodb.net/
MONGODB_DATABASE=pizzaflow
```

> Requires the PHP `mongodb` extension (`extension=mongodb` in `php.ini`).

### Generate Application Key

```bash
php artisan key:generate
```

### Run the Application

```bash
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000) to view the PizzaFlow landing page.

### Seed Demo Users

```bash
php artisan db:seed --class=UserSeeder
```

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@pizzaflow.com | Password123! |
| Store Manager | manager@pizzaflow.com | Password123! |
| Customer | customer@pizzaflow.com | Password123! |
| Kitchen Staff | kitchen@pizzaflow.com | Password123! |
| Delivery Driver | driver@pizzaflow.com | Password123! |

Public registration creates **Customer** accounts only. Staff roles are seeded/created by admins.

---

## 🎯 Objectives

- Digitize pizza ordering
- Improve kitchen workflow
- Reduce order errors
- Simplify delivery management
- Enhance customer experience
- Generate business insights through reports

---

## 📸 Screenshots

> Screenshots will be added after implementation.

---

## 🔮 Future Enhancements

- Online Payment Gateway Integration
- Loyalty & Rewards Program
- AI-Based Pizza Recommendations
- Push Notifications
- Mobile Application
- QR Code Order Tracking

---

## 👨‍💻 Developed By

**Alpha Coders**

Intern - **EgotechWorld**

---

## 📄 License

This project was developed for educational and internship purposes.
