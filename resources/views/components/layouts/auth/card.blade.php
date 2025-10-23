<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-neutral-100 antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div class="bg-muted flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="flex w-full max-w-md flex-col gap-2">
            <div class="flex flex-col gap-6">
                <div class="rounded-xl border bg-white dark:bg-stone-950 dark:border-stone-800 text-stone-800 shadow-xs">
                    <div class="px-10 py-6">
                        <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 font-medium mb-4"
                            wire:navigate>
                            {{-- <x-app-logo /> --}}
                            <img src="{{ asset('images/logo-app.png') }}" alt="Logo Invoice Manager" class="h-24 w-24">
                        </a>

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
