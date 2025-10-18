<?php

namespace Tests\Feature;

use App\Models\Client;
use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('client.index');

    $component->assertSee('Daftar Klien');
});

it('can search clients', function () {
    // Create clients
    $clientA = Client::factory()->create(['name' => 'Test Client A']);
    $clientB = Client::factory()->create(['name' => 'Test Client B']);

    $component = Volt::test('client.index');

    // Initial state should show both clients
    $component->assertSee($clientA->name)
        ->assertSee($clientB->name);

    // Search for client A
    $component->set('search', 'Client A')
        ->assertSee($clientA->name)
        ->assertDontSee($clientB->name);
});

it('can create a new client', function () {
    $component = Volt::test('client.index');

    $component->call('resetForm')
        ->set('name', 'New Client')
        ->set('email', 'new@example.com')
        ->set('phone', '1234567890')
        ->set('address', 'Test Address')
        ->set('npwp', '12345')
        ->call('submit');

    $component->assertDispatched('alert', function ($name, $data) {
        return $data['type'] === 'success' && $data['message'] === 'Klien berhasil disimpan.';
    });

    $this->assertDatabaseHas('clients', [
        'name'  => 'New Client',
        'email' => 'new@example.com',
    ]);
});

it('can edit a client', function () {
    $client = Client::factory()->create();

    $component = Volt::test('client.index');

    $component->call('edit', $client->id)
        ->set('name', 'Updated Client')
        ->set('email', 'updated@example.com')
        ->call('submit');

    $component->assertDispatched('alert', function ($name, $data) {
        return $data['type'] === 'success' && $data['message'] === 'Klien berhasil diperbarui.';
    });

    $this->assertDatabaseHas('clients', [
        'id'    => $client->id,
        'name'  => 'Updated Client',
        'email' => 'updated@example.com',
    ]);
});

it('validates form inputs', function () {
    $component = Volt::test('client.index');

    $component->set('name', '')
        ->set('email', 'invalid-email')
        ->call('submit')
        ->assertHasErrors(['name', 'email']);
});
