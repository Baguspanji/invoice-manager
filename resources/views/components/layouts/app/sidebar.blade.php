<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')" class="grid">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Project')" class="grid">
                <flux:navlist.item icon="folder" :href="route('project.index')" :current="request()->routeIs('project.*')"
                    wire:navigate>{{ __('Proyek') }}</flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Manajemen')" class="grid">
                <flux:navlist.item icon="users" :href="route('client.index')" :current="request()->routeIs('client.*')"
                    wire:navigate>{{ __('Klien') }}</flux:navlist.item>
                <flux:navlist.item icon="document-text"
                    :href="route('invoice.index')" :current="request()->routeIs('invoice.*')"
                    wire:navigate>{{ __('Invoice') }}</flux:navlist.item>
                {{-- <flux:navlist.item icon="receipt-percent"
                    :href="route('payment.index')" :current="request()->routeIs('payment.*')"
                    wire:navigate>{{ __('Pembayaran') }}</flux:navlist.item> --}}
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />
    </flux:sidebar>

    <!-- Include the navbar component -->
    <x-layouts.app.navbar />

    {{ $slot }}

    @fluxScripts
</body>

</html>
