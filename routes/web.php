<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\ChargeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductModelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::redirect('/dashboard', '/');

    Route::resource('product-models', ProductModelController::class)->except(['show']);
    Route::patch('product-models/{product_model}/toggle', [ProductModelController::class, 'toggle'])
        ->name('product-models.toggle');

    Route::resource('batches', BatchController::class);

    Route::resource('charges', ChargeController::class);
    Route::post('charges/{charge}/cancel', [ChargeController::class, 'cancel'])
        ->name('charges.cancel');

    Route::resource('suppliers', SupplierController::class);
    Route::resource('customers', CustomerController::class);

    Route::resource('sales', SaleController::class)->except(['edit', 'update', 'destroy']);
    Route::get('sales/{sale}/pdf', [SaleController::class, 'pdf'])->name('sales.pdf');
    Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('payments/{payment}/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');

    Route::get('cash', [CashController::class, 'index'])->name('cash.index');
    Route::post('cash/movements', [CashController::class, 'storeMovement'])->name('cash.movements.store');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{type}', [ReportController::class, 'show'])->name('reports.show');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
