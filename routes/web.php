<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CashSessionController;
use App\Http\Controllers\CloudSyncController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandingController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Setup\StationSetupController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Livewire\WeighingStation;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('setup', [StationSetupController::class, 'show'])->name('setup.show');
Route::post('setup', [StationSetupController::class, 'store'])->name('setup.store');

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    Route::get('forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function (): void {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Weighbridge operations (live weighing screen)
    Route::get('weighbridge', WeighingStation::class)
        ->middleware('permission:tickets.create')
        ->name('weighbridge');

    // Weighbridge stations
    Route::resource('stations', StationController::class)->except(['show']);
    Route::post('stations/{station}/test', [StationController::class, 'testConnection'])->name('stations.test');

    // Cash sessions
    Route::get('cash-sessions', [CashSessionController::class, 'index'])->name('cash-sessions.index');
    Route::get('cash-sessions/{cashSession}', [CashSessionController::class, 'show'])->name('cash-sessions.show');
    Route::post('cash-sessions/{cashSession}/close', [CashSessionController::class, 'close'])->name('cash-sessions.close');

    // Weighbridge tickets
    Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('tickets/{ticket}/print', [TicketController::class, 'print'])->name('tickets.print');
    Route::post('tickets/{ticket}/cancel', [TicketController::class, 'cancel'])->name('tickets.cancel');

    // Invoices
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('tickets/{ticket}/invoice', [InvoiceController::class, 'createForTicket'])->name('invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');

    // Demandings — outstanding invoices cumulative per customer
    Route::get('demandings', [DemandingController::class, 'index'])->name('demandings.index');
    Route::get('demandings/{customer}/pay', [DemandingController::class, 'createPayment'])->name('demandings.pay');
    Route::post('demandings/{customer}/pay', [DemandingController::class, 'storePayment'])->name('demandings.pay.store');
    Route::get('demandings/{customer}', [DemandingController::class, 'show'])->name('demandings.show');

    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('invoices/{invoice}/pay', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // Master data
    Route::resource('customers', CustomerController::class);
    Route::resource('vehicles', VehicleController::class);
    Route::resource('drivers', DriverController::class);
    Route::resource('products', ProductController::class);

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function (): void {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('daily', [ReportController::class, 'daily'])->name('daily');
        Route::get('operator', [ReportController::class, 'operator'])->name('operator');
        Route::get('invoice', [ReportController::class, 'invoice'])->name('invoice');
        Route::get('payment', [ReportController::class, 'payment'])->name('payment');
        Route::get('audit', [ReportController::class, 'audit'])->name('audit');
        Route::get('product-summary', [ReportController::class, 'productSummary'])->name('product');
        Route::get('customer-summary', [ReportController::class, 'customerSummary'])->name('customer');
        Route::get('vehicle-summary', [ReportController::class, 'vehicleSummary'])->name('vehicle');
        Route::get('outstanding-invoices', [ReportController::class, 'outstandingInvoices'])->name('outstanding');
        Route::get('paid-invoices', [ReportController::class, 'paidInvoices'])->name('paid');
        Route::get('revenue-by-product', [ReportController::class, 'revenueByProduct'])->name('revenue-product');
        Route::get('daily-collections', [ReportController::class, 'dailyCollections'])->name('collections');
        Route::get('cancelled-tickets', [ReportController::class, 'cancelledTickets'])->name('cancelled');
    });

    // Audit trail
    Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('audit/{auditLog}', [AuditLogController::class, 'show'])->name('audit.show');

    // User management
    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // System settings
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

    // Cloud database sync
    Route::get('cloud-sync', [CloudSyncController::class, 'index'])->name('cloud-sync.index');
    Route::post('cloud-sync/full', [CloudSyncController::class, 'syncFull'])->name('cloud-sync.full');
    Route::post('cloud-sync/retry', [CloudSyncController::class, 'syncRetry'])->name('cloud-sync.retry');
});
