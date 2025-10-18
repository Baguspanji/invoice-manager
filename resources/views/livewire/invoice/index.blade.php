<?php

use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Invoice;

new class extends Component {
    use WithPagination;

    public ?Invoice $invoice = null;

    #[Url(as: 'q')]
    public ?string $search = '';
    #[Url(as: 'status')]
    public ?string $status = '';

    /**
     * Take data from model to component
     */
    #[Computed]
    public function with(): array
    {
        return [
            'requests' => Invoice::query()
                ->with('client')
                ->select(['id', 'invoice_number', 'client_id', 'total', 'status', 'issue_date', 'due_date'])
                ->when($this->search, function ($query) {
                    $query->where('invoice_number', 'like', '%' . $this->search . '%');
                })
                ->when($this->status, function ($query) {
                    $query->where('status', $this->status);
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

    /**
     * Show detail modal
     */
    public function detail(int $id): void
    {
        $this->invoice = Invoice::with('client', 'items')->find($id);

        $this->modal('detail-data')->show();
    }

    /**
     * Show print preview modal
     */
    public function print(int $id): void
    {
        $this->invoice = Invoice::find($id);

        $this->modal('print-preview')->show();
    }
}; ?>


<section>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-bold">Daftar Invoice</h2>
            <p class="text-sm text-gray-600">
                Kelola informasi invoice Anda di sini. Tambahkan, edit, atau hapus data invoice sesuai kebutuhan.
            </p>
        </div>
        <a class="text-sm px-2 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer"
            href="{{ route('invoice.create') }}">
            <flux:icon name="plus" class="w-4 h-4 inline-block -mt-1" />
            Tambah Invoice
        </a>
    </div>

    <!-- Search Bar -->
    <div class="mb-4 flex flex-grow">
        <flux:input size="sm" type="search" placeholder="Cari invoice..." wire:model.live="search"
            class="max-w-xs" />
    </div>

    <!-- Table -->
    <div class="overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">No Invoice</th>
                    <th scope="col" class="px-6 py-3">Nama Klien</th>
                    <th scope="col" class="px-6 py-3">Tanggal Terbit</th>
                    <th scope="col" class="px-6 py-3">Jatuh Tempo</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Total</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $request)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap hover:font-semibold cursor-pointer"
                            wire:click="detail({{ $request->id }})">{{ $request->invoice_number }}</td>
                        <td class="px-6 py-4">{{ $request->client?->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->issue_date?->format('Y-m-d') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->due_date?->format('Y-m-d') ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-white text-xs font-mono bg-{{ $request->status->color() }}-400" >
                                {{ $request->status->label() ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            Rp {{ number_format($request->total, 2) }}
                        </td>
                        <td class="px-6 py-4 space-x-2">
                            <button wire:click="edit({{ $request->id }})"
                                class="text-xs text-yellow-600 px-2 py-1 rounded hover:bg-yellow-100 cursor-pointer">
                                <flux:icon name="pencil-square" class="w-4 h-4 inline-block -mt-1" />
                                Edit
                            </button>
                            <button wire:click="print({{ $request->id }})"
                                class="text-xs text-blue-600 px-2 py-1 rounded hover:bg-blue-100 cursor-pointer">
                                <flux:icon name="printer" class="w-4 h-4 inline-block -mt-1" />
                                Print
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white border-b">
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada data invoice.
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
                <flux:heading size="lg">Detail Invoice</flux:heading>
            </div>

            @if ($invoice)
                <div class="space-y-2">
                    <div><strong>Nomor Invoice:</strong> {{ $invoice->invoice_number }}</div>
                    <div><strong>Klien:</strong> {{ $invoice->client?->name }}</div>
                    <div><strong>Tanggal Terbit:</strong> {{ $invoice->issue_date?->format('Y-m-d') ?? '-' }}</div>
                    <div><strong>Jatuh Tempo:</strong> {{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</div>
                    <div><strong>Status:</strong>
                        <span class="px-2 py-1 rounded text-white text-xs font-mono bg-{{ $invoice->status->color() }}-400" >
                            {{ $invoice->status->label() ?? '-' }}
                        </span>
                    </div>
                    <div><strong>Total:</strong> Rp {{ number_format($invoice->total, 2) }}</div>
                    <div><strong>Catatan:</strong> {{ $invoice->notes ?? '-' }}</div>

                    <div>
                        <flux:heading size="md">Item Invoice</flux:heading>
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
                                    @foreach ($invoice->items as $item)
                                        <tr class="bg-white border-b">
                                            <td class="px-3 py-2">{{ $item->item_name }}</td>
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

    <!-- Print Preview Modal -->
    <flux:modal name="print-preview" class="md:w-2xl max-h-screen" :dismissible="true">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Preview Invoice</flux:heading>
                <div class="space-x-2">
                    <a href="{{ $invoice ? route('invoice.download', $invoice->id) : '#' }}" target="_blank" class="px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                        <flux:icon name="arrow-down-tray" class="w-4 h-4 inline-block -mt-1 mr-1" />
                        Download PDF
                    </a>
                    <button onclick="printIframe()" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                        <flux:icon name="printer" class="w-4 h-4 inline-block -mt-1 mr-1" />
                        Print
                    </button>
                </div>
            </div>

            @if ($invoice)
                <div class="h-[70vh]">
                    <iframe id="pdf-iframe" src="{{ route('invoice.pdf', $invoice->id) }}" class="w-full h-full border rounded"></iframe>
                </div>
            @else
                <div class="text-gray-500">Memuat data...</div>
            @endif
        </div>
    </flux:modal>

    <script>
        function printIframe() {
            const iframe = document.getElementById('pdf-iframe');
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }
    </script>
</section>
