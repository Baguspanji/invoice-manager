<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('invoice.create');

    $component->assertSee('');
});
