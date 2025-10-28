<?php

use Illuminate\View\View;
use App\Enums\InvoiceStatus;
use App\Enums\ProjectStatus;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Invoice;
use App\Models\Project;

new class extends Component {
    use WithPagination;

    public ?Invoice $invoice = null;
    public ?string $newStatus = null;
    public array $availableStatusOptions = [];

    #[Url(as: 'q')]
    public ?string $search = '';
    #[Url(as: 'status')]
    public ?string $status = '';

    /**
     * Set page title
     */
    public function rendering(View $view): void
    {
        $view->title('Invoice');
    }

    /**
     * Take data from model to component
     */
    #[Computed]
    public function with(): array
    {
        return [
            'requests' => Invoice::query()
                ->with(['client', 'project'])
                ->select(['id', 'invoice_number', 'client_id', 'total', 'status', 'issue_date', 'due_date', 'project_id'])
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

    /**
     * Show status change modal
     */
    public function statusChange(int $id): void
    {
        $this->invoice = Invoice::find($id);
        $this->availableStatusOptions = $this->getAvailableStatusOptions($this->invoice->status);
        if (count($this->availableStatusOptions) > 0) {
            $this->newStatus = $this->availableStatusOptions[0]['value'];
        } else {
            $this->newStatus = null;
        }

        $this->modal('status-change')->show();
    }

    /**
     * Get available status options based on current status
     */
    private function getAvailableStatusOptions(InvoiceStatus $currentStatus): array
    {
        return match ($currentStatus) {
            InvoiceStatus::DRAFT => [['value' => InvoiceStatus::SENT->value, 'label' => InvoiceStatus::SENT->label()]],
            InvoiceStatus::SENT => [['value' => InvoiceStatus::PAID->value, 'label' => InvoiceStatus::PAID->label()], ['value' => InvoiceStatus::OVERDUE->value, 'label' => InvoiceStatus::OVERDUE->label()]],
            InvoiceStatus::PAID, InvoiceStatus::OVERDUE => [],
        };
    }

    /**
     * Update invoice status
     */
    public function updateStatus(): void
    {
        if (!$this->invoice || !$this->newStatus) {
            $this->dispatch('alert', type: 'error', message: 'Terjadi kesalahan saat mengubah status.');
            return;
        }

        try {
            if ($this->newStatus == InvoiceStatus::PAID->value && $this->invoice->project_id !== null) {
                $project = Project::find($this->invoice->project_id);
                $project->decrement('billed_value', $this->invoice?->subtotal ?? 0);

                if ($project->billed_value <= 0) {
                    $project->status = ProjectStatus::COMPLETED->value;
                    $project->save();
                }
            }

            $this->invoice->update([
                'status' => $this->newStatus,
            ]);

            $this->dispatch('alert', type: 'success', message: 'Status invoice berhasil diubah.');
            $this->modal('status-change')->close();

            // Reset properties
            $this->newStatus = null;
            $this->availableStatusOptions = [];
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Gagal mengubah status invoice: ' . $e->getMessage());
        }
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
                    <th scope="col" class="px-6 py-3">Invoice</th>
                    <th scope="col" class="px-6 py-3">Periode</th>
                    <th scope="col" class="px-6 py-3">Proyek</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Total</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $request)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap cursor-pointer"
                            wire:click="detail({{ $request->id }})">
                            <span class="text-xs hover:underline hover:font-semibold">
                                #{{ $request->invoice_number }}
                            </span>
                            <h4>
                                <span class=font-semibold text-gray-500">{{ $request->client?->name }}</span>
                            </h4>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->issue_date?->format('Y-m-d') ?? '-' }} - {{ $request->due_date?->format('Y-m-d') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->project?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                match ($request->status) {
                                    \App\Enums\InvoiceStatus::SENT => ($statusClass = 'bg-blue-400'),
                                    \App\Enums\InvoiceStatus::PAID => ($statusClass = 'bg-green-400'),
                                    \App\Enums\InvoiceStatus::OVERDUE => ($statusClass = 'bg-red-400'),
                                    default => ($statusClass = 'bg-gray-400'),
                                };
                            @endphp
                            <span class="px-2 py-1 rounded text-white text-xs font-mono {{ $statusClass }}">
                                {{ $request->status->label() ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            Rp {{ number_format($request->total, 2) }}
                        </td>
                        <td class="px-6 py-4 space-x-2 flex flex-row">
                            <a href="{{ route('invoice.edit', ['invoice' => $request->id]) }}"
                                class="text-xs text-yellow-600 px-2 py-1 rounded hover:bg-yellow-100 cursor-pointer">
                                <flux:icon name="pencil-square" class="w-4 h-4 inline-block -mt-1" />
                                Edit
                            </a>
                            <button wire:click="print({{ $request->id }})"
                                class="text-xs text-blue-600 px-2 py-1 rounded hover:bg-blue-100 cursor-pointer">
                                <flux:icon name="printer" class="w-4 h-4 inline-block -mt-1" />
                                Print
                            </button>
                            <button wire:click="statusChange({{ $request->id }})"
                                class="text-xs text-gray-600 px-2 py-1 rounded hover:bg-gray-100 cursor-pointer">
                                <flux:icon name="tag" class="w-4 h-4 inline-block -mt-1" />
                                Status
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white border-b">
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
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
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 mb-4">
                            <tbody>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700 w-1/3">Nomor Invoice</th>
                                    <td class="px-3 py-2">{{ $invoice->invoice_number }}</td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700 w-1/3">Klien</th>
                                    <td class="px-3 py-2">{{ $invoice->client?->name }}</td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700 w-1/3">Tanggal Terbit</th>
                                    <td class="px-3 py-2">{{ $invoice->issue_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700 w-1/3">Jatuh Tempo</th>
                                    <td class="px-3 py-2">{{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700 w-1/3">Status</th>
                                    <td class="px-3 py-2">
                                        <span
                                            class="px-2 py-1 rounded text-white text-xs font-mono bg-{{ $invoice->status->color() }}-400">
                                            {{ $invoice->status->label() ?? '-' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700 w-1/3">Total</th>
                                    <td class="px-3 py-2">Rp {{ number_format($invoice->total, 2) }}</td>
                                </tr>
                                <tr class="border-b">
                                    <th class="px-3 py-2 bg-gray-50 font-medium text-gray-700 w-1/3">Catatan</th>
                                    <td class="px-3 py-2">{{ $invoice->notes ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

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
            <div>
                <flux:heading size="lg">Preview Invoice</flux:heading>
            </div>

            @if ($invoice)
                <div class="h-[70vh]">
                    <iframe id="pdf-iframe" src="{{ route('invoice.pdf', $invoice->id) }}"
                        class="w-full h-full border rounded"></iframe>
                </div>

                <div class="flex items-center justify-end">
                    <div class="space-x-2">
                        <a href="{{ $invoice ? route('invoice.download', $invoice->id) : '#' }}" target="_blank"
                            class="px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                            <flux:icon name="arrow-down-tray" class="w-4 h-4 inline-block -mt-1 mr-1" />
                            Download PDF
                        </a>
                        <button onclick="printIframe()"
                            class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                            <flux:icon name="printer" class="w-4 h-4 inline-block -mt-1 mr-1" />
                            Print
                        </button>
                    </div>
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

    <!-- Status Change Modal -->
    <flux:modal name="status-change" class="md:w-md">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Ubah Status Invoice</flux:heading>
                <p class="text-sm text-gray-600">
                    Ubah status invoice dari
                    @if ($invoice)
                        <span class="font-semibold">{{ $invoice->status->label() }}</span>
                    @endif
                </p>
            </div>

            @if ($invoice)
                <form wire:submit="updateStatus" class="space-y-4">
                    <div>
                        <flux:label for="new-status">Status Baru</flux:label>
                        @if (count($availableStatusOptions) > 0)
                            <flux:select size="sm" wire:model="newStatus">
                                @foreach ($availableStatusOptions as $key => $option)
                                    <option value="{{ $option['value'] }}"
                                        @if ($key == 0) selected @endif>{{ $option['label'] }}
                                    </option>
                                @endforeach
                            </flux:select>
                        @else
                            <div class="p-3 bg-gray-100 rounded border text-sm text-gray-700">
                                Status invoice ini tidak dapat diubah lagi.
                            </div>
                        @endif
                    </div>

                    <div class="pt-2 flex justify-end space-x-2">
                        @if (count($availableStatusOptions) > 0)
                            <flux:button type="submit" variant="primary" size="sm" :disabled="!$newStatus"
                                class="cursor-pointer">
                                Simpan Perubahan
                            </flux:button>
                        @endif
                    </div>
                </form>
            @else
                <div class="text-gray-500">Memuat data...</div>
            @endif
        </div>
    </flux:modal>
</section>
