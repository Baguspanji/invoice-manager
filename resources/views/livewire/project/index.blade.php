<?php

use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Project;

new class extends Component {
    use WithPagination;

    public ?Project $project = null;

    #[Url(as: 'q')]
    public ?string $search = '';

    /**
     * Set page title
     */
    public function rendering(View $view): void
    {
        $view->title('Proyek');
    }

    /**
     * Take data from model to component
     */
    #[Computed]
    public function with(): array
    {
        return [
            'requests' => Project::query()
                ->with([
                    'client',
                    'invoices' => function ($query) {
                        $query->select('project_id');
                    },
                ])
                ->select(['id', 'project_number', 'client_id', 'name', 'total_value', 'billed_value', 'tax', 'start_date', 'due_date', 'status'])
                ->where('deleted_at', null)
                ->when($this->search, function ($query) {
                    $query->where('project_number', 'like', '%' . $this->search . '%')->orWhere('name', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10),
        ];
    }

    /**
     * Show detail modal
     */
    public function detail(int $id): void
    {
        $this->project = Project::with('client', 'items')->find($id);

        $this->modal('detail-data')->show();
    }

    /**
     * Soft Delete project
     */
    public function delete(int $id): void
    {
        try {
            $project = Project::findOrFail($id);
            $project->deleted_at = now();
            $project->save();

            $this->dispatch('alert', type: 'success', message: 'Proyek berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Gagal menghapus proyek: ' . $e->getMessage());
        }
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
                    <th scope="col" class="px-6 py-3">Proyek</th>
                    <th scope="col" class="px-6 py-3">Nama Klien</th>
                    <th scope="col" class="px-6 py-3">Periode</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Total Nilai</th>
                    <th scope="col" class="px-6 py-3">PPN(11%)</th>
                    <th scope="col" class="px-6 py-3">Total Tagihan</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $request)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap cursor-pointer" wire:click="detail({{ $request->id }})">
                            <span class="text-xs hover:underline hover:font-semibold">
                                #{{ $request->project_number }}
                            </span>
                                <h4 class=font-semibold text-gray-500">{{ $request->name }}</h4>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <h4 class=font-semibold text-gray-500">{{ $request->client?->name }}</h4>
                            <span>
                                Total Invoice: {{ count($request->invoices) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->start_date?->format('Y-m-d') ?? '-' }} -
                            {{ $request->due_date?->format('Y-m-d') ?? '-' }}</td>
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
                            {{ $request->tax ? 'Rp ' . number_format($request->tax, 2) : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            Rp {{ number_format($request->billed_value, 2) }}
                        </td>
                        <td class="px-6 py-4  space-x-2 flex flex-row">
                            <a href="{{ route('project.edit', ['project' => $request->id]) }}"
                                class="text-xs text-yellow-600 px-2 py-1 rounded hover:bg-yellow-100 cursor-pointer">
                                <flux:icon name="pencil-square" class="w-4 h-4 inline-block -mt-1" />
                                Edit
                            </a>
                            @if ($request->status == \App\Enums\ProjectStatus::PENDING && count($request->invoices) == 0)
                                <button wire:click="delete({{ $request->id }})"
                                    wire:confirm='Apakah Anda yakin ingin menghapus proyek ini?'
                                    class="text-xs text-red-600 px-2 py-1 rounded hover:bg-red-100 cursor-pointer">
                                    <flux:icon name="trash" class="w-4 h-4 inline-block -mt-1" />
                                    Hapus
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white border-b">
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
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

    <!-- Modal Detail -->
    <flux:modal name="detail-data" class="md:w-xl">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Detail Proyek</flux:heading>
            </div>

            @if ($project)
                <div class="space-y-2">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 mb-4">
                            <tbody>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700 w-1/3">Nomor Proyek</th>
                                    <td class="px-3 py-2">{{ $project->project_number }}</td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700">Nama Proyek</th>
                                    <td class="px-3 py-2">{{ $project->name }}</td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700">Klien</th>
                                    <td class="px-3 py-2">{{ $project->client?->name }}</td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700">Tanggal Mulai</th>
                                    <td class="px-3 py-2">{{ $project->start_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700">Tanggal Berakhir</th>
                                    <td class="px-3 py-2">{{ $project->due_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700">Status</th>
                                    <td class="px-3 py-2">
                                        <span
                                            class="px-2 py-1 rounded text-white text-xs font-mono bg-{{ $project->status->color() }}-400">
                                            {{ $project->status->label() ?? '-' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700">Total Nilai</th>
                                    <td class="px-3 py-2">Rp {{ number_format($project->total_value, 2) }}</td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700">Total Tagihan</th>
                                    <td class="px-3 py-2">Rp {{ number_format($project->billed_value, 2) }}</td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700">Deskripsi</th>
                                    <td class="px-3 py-2">{{ $project->description ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <flux:heading size="md">Item Proyek</flux:heading>
                        <div class="w-full border-b-1 border-gray-200 mb-2"></div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-3 py-2">Item</th>
                                        <th scope="col" class="px-3 py-2">Qty</th>
                                        <th scope="col" class="px-3 py-2">Harga Satuan</th>
                                        <th scope="col" class="px-3 py-2">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($project->items as $item)
                                        <tr class="bg-white border-b">
                                            <td class="px-3 py-2">{{ $item->name }}</td>
                                            <td class="px-3 py-2">{{ $item->quantity }}</td>
                                            <td class="px-3 py-2">Rp {{ number_format($item->unit_price, 2) }}</td>
                                            <td class="px-3 py-2">Rp {{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-gray-500">Memuat data...</div>
            @endif
        </div>
    </flux:modal>
</section>
