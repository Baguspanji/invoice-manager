<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('invoice.index');

    $component->assertSee('Daftar Invoice');
});

it('can search invoices', function () {
    // Create invoices
    $invoiceA = Invoice::factory()->create(['name' => 'Test Invoice A']);
    $invoiceB = Invoice::factory()->create(['name' => 'Test Invoice B']);

    $component = Volt::test('invoice.index');

    // Initial state should show both invoices
    $component->assertSee($invoiceA->name)
        ->assertSee($invoiceB->name);

    // Search for invoice A
    $component->set('search', 'Invoice A')
        ->assertSee($invoiceA->name)
        ->assertDontSee($invoiceB->name);
});

it('can create a new invoice', function () {
    $component = Volt::test('invoice.index');

    $component->call('resetForm')
        ->set('client_id', 1)
        ->set('name', 'New Invoice')
        ->call('submit');

    $component->assertDispatched('alert', function ($name, $data) {
        return $data['type'] === 'success' && $data['message'] === 'Klien berhasil disimpan.';
    });

    $this->assertDatabaseHas('invoices', [
        'name'  => 'New Invoice',
        'client_id' => 1,
    ]);
});

it('can edit a client', function () {
    $invoice = Invoice::factory()->create();

    $component = Volt::test('invoice.create');

    $component->call('edit', $invoice->id)
        ->set('client_id', 1)
        ->set('name', 'Updated Client')
        ->call('submit');

    $component->assertDispatched('alert', function ($name, $data) {
        return $data['type'] === 'success' && $data['message'] === 'Invoice berhasil diperbarui.';
    });

    $this->assertDatabaseHas('invoices', [
        'id'    => $invoice->id,
        'name'  => 'Updated Invoice',
    ]);
});

it('validates form inputs', function () {
    $component = Volt::test('invoice.index');

    $component->set('name', '')
        ->set('invoice_number', 'invalid-invoice_number')
        ->call('submit')
        ->assertHasErrors(['name', 'invoice_number']);
});
