<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Volt::route('dashboard', 'dashboard')->name('dashboard');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');

    // Client Management
    Volt::route('client', 'client.index')->name('client.index');

    // Invoice Management
    Volt::route('invoice', 'invoice.index')->name('invoice.index');
    Volt::route('invoice/create', 'invoice.create')->name('invoice.create');
    Volt::route('invoice/{invoice}/edit', 'invoice.edit')->name('invoice.edit');

    // Project Management
    Volt::route('project', 'project.index')->name('project.index');
    Volt::route('project/create', 'project.create')->name('project.create');
    Volt::route('project/{project}/edit', 'project.edit')->name('project.edit');

    // Invoice PDF
    Route::get('invoice/{invoice}/pdf', [App\Http\Controllers\InvoiceController::class, 'generatePdf'])->name('invoice.pdf');
    Route::get('invoice/{invoice}/download', [App\Http\Controllers\InvoiceController::class, 'downloadPdf'])->name('invoice.download'); // Add this route to your existing routes
});

Route::get('/invoice/public/{hash}', [App\Http\Controllers\InvoiceController::class, 'publicAccess'])->name('invoice.public');

require __DIR__ . '/auth.php';
