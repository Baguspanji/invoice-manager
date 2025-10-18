<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');

    // Client Management
    Volt::route('client', 'client.index')->name('client.index');

    // Invoice Management
    Volt::route('invoice', 'invoice.index')->name('invoice.index');
    Volt::route('invoice/create', 'invoice.create')->name('invoice.create');

    // Invoice PDF
    Route::get('invoice/{invoice}/pdf', [App\Http\Controllers\InvoiceController::class, 'generatePdf'])->name('invoice.pdf');
    Route::get('invoice/{invoice}/download', [App\Http\Controllers\InvoiceController::class, 'downloadPdf'])->name('invoice.download');
});

require __DIR__.'/auth.php';
