<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LocaleController;

// Passenger Controllers
use App\Http\Controllers\Passenger\DashboardController as PassengerDashboardController;
use App\Http\Controllers\Passenger\BookingController as PassengerBookingController;
use App\Http\Controllers\Passenger\PaymentController as PassengerPaymentController;
use App\Http\Controllers\Passenger\ShipmentController as PassengerShipmentController;
use App\Http\Controllers\Passenger\DisputeController as PassengerDisputeController;
use App\Http\Controllers\Passenger\RatingController as PassengerRatingController;

// Manager Controllers
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\BranchController as ManagerBranchController;
use App\Http\Controllers\Manager\VehicleController as ManagerVehicleController;
use App\Http\Controllers\Manager\TripController as ManagerTripController;
use App\Http\Controllers\Manager\CheckinController as ManagerCheckinController;
use App\Http\Controllers\Manager\ShipmentController as ManagerShipmentController;
use App\Http\Controllers\Manager\DisputeController as ManagerDisputeController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TripMonitorController as AdminTripMonitorController;
use App\Http\Controllers\Admin\DisputeController as AdminDisputeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

/*
|--------------------------------------------------------------------------
| Public Routes (Visiteur / Guest)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show');
Route::get('/track', [ShipmentController::class, 'trackingSearch'])->name('shipments.track');

// Bilingual Language Switcher (French / English)
Route::get('/lang/{locale}', [LocaleController::class, 'switch'])
    ->where('locale', 'fr|en')
    ->name('lang.swap');

use App\Http\Controllers\Auth\GoogleAuthController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Google OAuth2 Authentication Routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/google/setup', [GoogleAuthController::class, 'showSetupGuide'])->name('auth.google.setup');
Route::post('/auth/google/setup', [GoogleAuthController::class, 'saveCredentials'])->name('auth.google.setup.save');
Route::get('/auth/google/demo', [GoogleAuthController::class, 'demoGoogleLogin'])->name('auth.google.demo');

Route::get('/register', [AuthController::class, 'showRegisterPassenger'])->name('register');
Route::get('/register/passenger', [AuthController::class, 'showRegisterPassenger'])->name('register.passenger');
Route::post('/register', [AuthController::class, 'registerPassenger'])->name('register.passenger.submit');

// User Profile & Account Settings (Any authenticated user)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.destroy');
});

/*
|--------------------------------------------------------------------------
| Passenger Portal (Role: passager)
|--------------------------------------------------------------------------
*/
Route::middleware(['role:passager,admin'])->prefix('passenger')->name('passenger.')->group(function () {
    Route::get('/dashboard', [PassengerDashboardController::class, 'index'])->name('dashboard');
    
    // Bookings
    Route::post('/trips/{trip}/book', [PassengerBookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings', [PassengerBookingController::class, 'history'])->name('bookings.history');
    Route::get('/bookings/{booking}/checkout', [PassengerBookingController::class, 'checkout'])->name('bookings.checkout');
    Route::get('/bookings/{booking}/ticket', [PassengerBookingController::class, 'ticket'])->name('bookings.ticket');
    Route::get('/bookings/{booking}/pdf', [PassengerBookingController::class, 'pdf'])->name('bookings.pdf');
    Route::post('/bookings/{booking}/cancel', [PassengerBookingController::class, 'cancel'])->name('bookings.cancel');

    // Payments & Mobile Money Checkout
    Route::post('/bookings/{booking}/pay', [PassengerPaymentController::class, 'processBookingPayment'])->name('payments.booking');
    Route::post('/shipments/{shipment}/pay', [PassengerPaymentController::class, 'processShipmentPayment'])->name('payments.shipment');
    Route::get('/payments/{payment}', [PassengerPaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/simulate', [PassengerPaymentController::class, 'simulate'])->name('payments.simulate');

    // Shipments / Freight Cargo
    Route::get('/shipments', [PassengerShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/create', [PassengerShipmentController::class, 'create'])->name('shipments.create');
    Route::post('/shipments', [PassengerShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/shipments/{shipment}/checkout', [PassengerShipmentController::class, 'checkout'])->name('shipments.checkout');
    Route::get('/shipments/{shipment}', [PassengerShipmentController::class, 'show'])->name('shipments.show');

    // Ratings & Reviews
    Route::post('/branches/{branch}/rate', [PassengerRatingController::class, 'store'])->name('branches.rate');
    Route::post('/agencies/{agency}/rate', [PassengerRatingController::class, 'store'])->name('agencies.rate');

    // Disputes
    Route::get('/disputes', [PassengerDisputeController::class, 'index'])->name('disputes.index');
    Route::get('/disputes/create', [PassengerDisputeController::class, 'create'])->name('disputes.create');
    Route::post('/disputes', [PassengerDisputeController::class, 'store'])->name('disputes.store');
    Route::get('/disputes/{dispute}', [PassengerDisputeController::class, 'show'])->name('disputes.show');
});

/*
|--------------------------------------------------------------------------
| Manager Portal (Role: manager)
|--------------------------------------------------------------------------
*/
Route::middleware(['role:manager,admin'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
    
    // Branch / Agency Profile
    Route::get('/branch/profile', [ManagerBranchController::class, 'edit'])->name('branch.edit');
    Route::put('/branch/profile', [ManagerBranchController::class, 'update'])->name('branch.update');
    Route::get('/agency/profile', [ManagerBranchController::class, 'edit'])->name('agency.edit');
    Route::put('/agency/profile', [ManagerBranchController::class, 'update'])->name('agency.update');

    // Fleet / Vehicles
    Route::resource('vehicles', ManagerVehicleController::class);

    // Trips & Publishing
    Route::resource('trips', ManagerTripController::class);
    Route::get('/trips/{trip}/manifest', [ManagerTripController::class, 'manifest'])->name('trips.manifest');
    Route::post('/trips/{trip}/status', [ManagerTripController::class, 'updateStatus'])->name('trips.status');

    // Check-in & Boarding
    Route::get('/checkin', [ManagerCheckinController::class, 'index'])->name('checkin.index');
    Route::post('/checkin/{booking}', [ManagerCheckinController::class, 'process'])->name('checkin.process');

    // Cargo & Shipments Delivery
    Route::get('/shipments', [ManagerShipmentController::class, 'index'])->name('shipments.index');
    Route::post('/shipments/{shipment}/status', [ManagerShipmentController::class, 'updateStatus'])->name('shipments.status');
    Route::post('/shipments/{shipment}/deliver', [ManagerShipmentController::class, 'confirmDelivery'])->name('shipments.deliver');

    // Disputes (48-hour queue)
    Route::get('/disputes', [ManagerDisputeController::class, 'index'])->name('disputes.index');
    Route::post('/disputes/{dispute}/resolve', [ManagerDisputeController::class, 'resolve'])->name('disputes.resolve');
});

/*
|--------------------------------------------------------------------------
| Admin Portal (Role: admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Trips Operations & Monitor
    Route::get('/monitor', [AdminTripMonitorController::class, 'index'])->name('monitor.index');

    // Customer Support & Disputes Center
    Route::get('/disputes', [AdminDisputeController::class, 'index'])->name('disputes.index');
    Route::post('/disputes/{dispute}/arbitrate', [AdminDisputeController::class, 'arbitrate'])->name('disputes.arbitrate');

    // Staff & Users Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
});
