<?php

use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Project;

new class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public ?string $search = '';

    /**
     * Take data from model to component
     */
    #[Computed]
    public function with(): array
    {
        return [
            'requests' => Project::query()
                ->with('client')
                ->select(['id', 'project_number', 'name', 'total_value', 'billed_value', 'start_date', 'due_date', 'status'])
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10),
        ];
    }

    /**
     * Redirect to edit page
     */
    public function edit(int $id): void
    {
        $this->dispatch('alert', type: 'warning', message: 'Fitur akan segera hadir.');
        // $this->redirect(route('invoice.edit', ['invoice' => $id]));
    }
}; ?>

<section>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-bold">Daftar Proyek</h2>
            <p class="text-sm text-gray-600">
                Kelola semua proyek Anda di sini.
            </p>
        </div>
        <a class="text-sm px-2 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer"
            href="{{ route('project.create') }}">
            <flux:icon name="plus" class="w-4 h-4 inline-block -mt-1" />
            Tambah Proyek
        </a>
    </div>

    <!-- Search Bar -->
    <div class="mb-4 flex flex-grow">
        <flux:input size="sm" type="search" placeholder="Cari klien..." wire:model.live="search" class="max-w-xs" />
    </div>

    <!-- Table -->
    <div class="overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">No Proyek</th>
                    <th scope="col" class="px-6 py-3">Nama Klien</th>
                    <th scope="col" class="px-6 py-3">Nama Project</th>
                    <th scope="col" class="px-6 py-3">Tanggal Mulai</th>
                    <th scope="col" class="px-6 py-3">Tanggal Berahir</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Total Nilai</th>
                    <th scope="col" class="px-6 py-3">Total Tagihan</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $request)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap hover:font-semibold cursor-pointer"
                            wire:click="edit({{ $request->id }})">{{ $request->project_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->client?->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->start_date?->format('Y-m-d') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->due_date?->format('Y-m-d') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                match ($request->status) {
                                    \App\Enums\ProjectStatus::PLANNING => ($statusClass = 'bg-blue-400'),
                                    \App\Enums\ProjectStatus::IN_PROGRESS => ($statusClass = 'bg-warning-400'),
                                    \App\Enums\ProjectStatus::COMPLETED => ($statusClass = 'bg-green-400'),
                                    default => ($statusClass = 'bg-gray-400'),
                                };
                            @endphp
                            <span class="px-2 py-1 rounded text-white text-xs font-mono {{ $statusClass }}">
                                {{ $request->status->label() ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            Rp {{ number_format($request->total_value, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            Rp {{ number_format($request->billed_value, 2) }}
                        </td>
                        <td class="px-6 py-4 space-x-2">
                            <button wire:click="edit({{ $request->id }})"
                                class="text-xs text-yellow-600 px-2 py-1 rounded hover:bg-yellow-100 cursor-pointer">
                                <flux:icon name="pencil-square" class="w-4 h-4 inline-block -mt-1" />
                                Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white border-b">
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada data klien.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $requests->links() }}
    </div>
</section>
