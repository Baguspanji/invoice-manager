<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

new class extends Component {
    // columns for invoice creation
    #[Rule('required|string|unique:invoices,invoice_number')]
    public ?string $invoice_number = null;
    #[Rule('required|integer|exists:clients,id')]
    public ?int $client_id = null;
    #[Rule('required|date')]
    public ?string $issue_date = null;
    #[Rule('required|date')]
    public ?string $due_date = null;
    #[Rule('required|numeric|min:0')]
    public ?float $total = null;
    #[Rule('nullable|string|min:0')]
    public ?string $notes = null;

    #[Rule('array')]
    public array $invoiceItems = [];

    public array $clients = [];

    /**
     * Mount invoice number on component load
     */
    public function mount(): void
    {
        $invoice = new Invoice();
        $this->invoice_number = $invoice->generateInvoiceNumber();
        $this->issue_date = date('Y-m-d');
        $this->due_date = date('Y-m-d', strtotime('+30 days'));
        $this->clients = Client::select(['id', 'name'])
            ->get()
            ->toArray();

        // Initialize with one empty invoice item
        $this->invoiceItems[] = [
            'item_name' => '',
            'quantity' => 1,
            'unit_price' => 0.0,
            'description' => '',
        ];

        $this->calculateTotal();
    }

    /**
     * Add new item to invoiceItems
     */
    public function addItem(): void
    {
        $this->invoiceItems[] = [
            'item_name' => '',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0.0,
        ];

        $this->calculateTotal();
    }

    /**
     * Remove item from invoiceItems
     */
    public function removeItem(int $index): void
    {
        unset($this->invoiceItems[$index]);
        $this->invoiceItems = array_values($this->invoiceItems);

        $this->calculateTotal();
    }

    /**
     * Calculate total based on all items
     */
    public function calculateTotal(): void
    {
        $this->total = 0;

        foreach ($this->invoiceItems as $item) {
            $this->total += $item['quantity'] * $item['unit_price'];
        }

        // Format to 2 decimal places
        $this->total = round($this->total, 2);
    }

    /**
     * Update calculations when an item changes
     */
    public function updatedInvoiceItems(): void
    {
        $this->calculateTotal();
    }

    /**
     * Store form data
     */
    public function submit(): void
    {
        $this->validate();

        DB::transaction(function () {
            $invoice = Invoice::create([
                'invoice_number' => $this->invoice_number,
                'client_id' => $this->client_id,
                'issue_date' => $this->issue_date,
                'due_date' => $this->due_date,
                'subtotal' => $this->total,
                'total' => $this->total,
                'notes' => $this->notes,
            ]);

            // Assuming invoiceItems is an array of items to be saved
            foreach ($this->invoiceItems as $item) {
                $item['total_price'] = $item['quantity'] * $item['unit_price'];

                $invoice->items()->create($item);
            }
        });

        session()->flash('alert-message', [
            'message' => 'Invoice berhasil dibuat.',
            'type' => 'success',
        ]);

        $this->redirect(route('invoice.index'));
    }
}; ?>

<section>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-bold">Buat Invoice</h2>
            <p class="text-sm text-gray-600">
                Kelola informasi invoice Anda di sini. Tambahkan, edit, atau hapus data invoice sesuai kebutuhan.
            </p>
        </div>
        <a class="text-sm px-2 py-1.5 bg-transparent text-gray-700 border border-gray-400 rounded hover:bg-gray-100 cursor-pointer"
            href="{{ route('invoice.index') }}">
            <flux:icon name="arrow-left" class="w-4 h-4 inline-block -mt-1" />
            Daftar Invoice
        </a>
    </div>

    <!-- Form -->
    <form wire:submit.prevent="submit" class="space-y-6 bg-white p-6 rounded shadow">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div class="col-span-2">
                <div class="text-2xl font-semibold text-gray-700 mb-2">Invoice</div>

                <div class="w-full border-b-1 border-gray-200 mb-2"></div>
            </div>

            <div>
                <flux:input size="sm" label="Nomor Invoice" type="text" wire:model.defer="invoice_number"
                    readonly />
            </div>

            <div>
                <flux:select size="sm" label="Klien" wire:model.defer="client_id">
                    @foreach ($clients as $client)
                        <flux:select.option :value="$client['id']">{{ $client['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <flux:input size="sm" label="Tanggal Terbit" type="date" wire:model.defer="issue_date" />
            </div>

            <div>
                <flux:input size="sm" label="Jatuh Tempo" type="date" wire:model.defer="due_date" />
            </div>

            <div>
                <flux:input size="sm" label="Total" type="number" step="0.01" wire:model.defer="total" readonly />
            </div>

            <div class="md:col-span-2">
                <flux:textarea size="sm" label="Catatan" wire:model.defer="notes" rows="2" />
            </div>

            <div class="md:col-span-2 pt-4">
                <div class="flex justify-between">
                    <div class="text-2xl font-semibold text-gray-700 mb-2">Item Invoice</div>
                    <button type="button" wire:click.prevent="addItem"
                        class="text-sm text-green-600 px-2 py-1 rounded hover:bg-green-100 cursor-pointer ml-auto">
                        Tambah Item
                    </button>
                </div>

                <div class="w-full border-b-1 border-gray-200 mb-2"></div>

                <div class="space-y-4">
                    @foreach ($invoiceItems as $index => $item)
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-2 items-end">
                            <div class="col-span-3">
                                <flux:input size="sm" label="Item" type="text"
                                    wire:model.defer="invoiceItems.{{ $index }}.item_name" />
                            </div>
                            <div>
                                <flux:input size="sm" label="Kuantitas" type="number" step="1"
                                    wire:model.live="invoiceItems.{{ $index }}.quantity" />
                            </div>
                            <div>
                                <flux:input size="sm" label="Harga Satuan" type="number" step="0.01"
                                    wire:model.live="invoiceItems.{{ $index }}.unit_price" />
                            </div>
                            <div>
                                <button type="button" wire:click.prevent="removeItem({{ $index }})"
                                    class="text-sm text-red-600 px-2 py-1 rounded hover:bg-red-100 cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                            <div class="col-span-5">
                                <flux:textarea size="sm" label="Deskripsi" type="text"
                                    wire:model.defer="invoiceItems.{{ $index }}.description" rows="2" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <button type="submit"
                class="text-sm px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer">
                Simpan Invoice
            </button>
        </div>
    </form>
</section>
