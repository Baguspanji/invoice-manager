<?php

use Illuminate\View\View;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

new class extends Component {
    /**
     * Set page title
     */
    public function rendering(View $view): void
    {
        $view->title('Buat Proyek');
    }

    // columns for project creation
    #[Rule('required|string|unique:projects,project_number')]
    public ?string $project_number = null;
    #[Rule('required|integer|exists:clients,id')]
    public ?int $client_id = null;
    #[Rule('nullable|string|min:0')]
    public ?string $name = null;
    #[Rule('required|date')]
    public ?string $start_date = null;
    #[Rule('required|date')]
    public ?string $due_date = null;
    #[Rule('required|numeric|min:0')]
    public ?float $total_value = null;
    #[Rule('nullable|string|min:0')]
    public ?string $description = null;

    #[Rule('array')]
    public array $projectItems = [];

    public array $clients = [];

    /**
     * Mount project number on component load
     */
    public function mount(): void
    {
        $project = new Project();
        $this->project_number = $project->generateProjectNumber();
        $this->start_date = date('Y-m-d');
        $this->due_date = date('Y-m-d', strtotime('+30 days'));
        $this->clients = Client::select(['id', 'name'])
            ->get()
            ->toArray();

        // Initialize with one empty project item
        $this->projectItems[] = [
            'name' => '',
            'quantity' => 1,
            'unit_price' => 0.0,
            'description' => '',
        ];

        $this->calculateTotal();
    }

    /**
     * Add new item to projectItems
     */
    public function addItem(): void
    {
        $this->projectItems[] = [
            'name' => '',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0.0,
        ];

        $this->calculateTotal();
    }

    /**
     * Remove item from projectItems
     */
    public function removeItem(int $index): void
    {
        unset($this->projectItems[$index]);
        $this->projectItems = array_values($this->projectItems);

        $this->calculateTotal();
    }

    /**
     * Calculate total based on all items
     */
    public function calculateTotal(): void
    {
        $this->total_value = 0;

        foreach ($this->projectItems as $item) {
            $this->total_value += $item['quantity'] * $item['unit_price'];
        }

        // Format to 2 decimal places
        $this->total_value = round($this->total_value, 2);
    }

    /**
     * Update calculations when an item changes
     */
    public function updatedProjectItems(): void
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
            $project = Project::create([
                'client_id' => $this->client_id,
                'project_number' => $this->project_number,
                'name' =>$this->name,
                'start_date' => $this->start_date,
                'due_date' => $this->due_date,
                'total_value' => $this->total_value,
                'billed_value' => $this->total_value,
                'description' => $this->description,
            ]);

            // Assuming projectItems is an array of items to be saved
            foreach ($this->projectItems as $item) {
                $item['total_price'] = $item['quantity'] * $item['unit_price'];

                $project->items()->create($item);
            }
        });

        session()->flash('alert-message', [
            'message' => 'Proyek berhasil dibuat.',
            'type' => 'success',
        ]);

        $this->redirect(route('project.index'));
    }
}; ?>

<section>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-bold">Buat Proyek</h2>
            <p class="text-sm text-gray-600">
                Kelola semua proyek Anda di sini.
            </p>
        </div>
        <a class="text-sm px-2 py-1.5 bg-transparent text-gray-700 border border-gray-400 rounded hover:bg-gray-100 cursor-pointer"
            href="{{ route('project.index') }}">
            <flux:icon name="arrow-left" class="w-4 h-4 inline-block -mt-1" />
            Daftar Proyek
        </a>
    </div>

    <!-- Form -->
    <form wire:submit.prevent="submit" class="space-y-6 bg-white p-6 rounded shadow">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div class="col-span-2">
                <div class="text-2xl font-semibold text-gray-700 mb-2">Proyek</div>

                <div class="w-full border-b-1 border-gray-200 mb-2"></div>
            </div>

            <div>
                <flux:input size="sm" label="Nomor Proyek" type="text" wire:model.defer="project_number"
                    readonly />
            </div>

            <div>
                <flux:select size="sm" label="Klien" wire:model.defer="client_id">
                    <flux:select.option value="">Pilih Klien</flux:select.option>
                    @foreach ($clients as $item)
                        <flux:select.option :value="$item['id']">{{ $item['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="col-span-2">
                <flux:input size="sm" label="Nama Proyek" type="text" wire:model.defer="name" />
            </div>

            <div>
                <flux:input size="sm" label="Tanggal Mulai" type="date" wire:model.defer="start_date" />
            </div>

            <div>
                <flux:input size="sm" label="Tanggal Berahir" type="date" wire:model.defer="due_date" />
            </div>

            <div>
                <flux:input size="sm" label="Total" type="number" step="0.01" wire:model.defer="total_value" readonly />
            </div>

            <div class="md:col-span-2">
                <flux:textarea size="sm" label="Deskripsi" wire:model.defer="description" rows="2" />
            </div>

            <div class="md:col-span-2 pt-4">
                <div class="flex justify-between">
                    <div class="text-2xl font-semibold text-gray-700 mb-2">Item Proyek</div>
                    <button type="button" wire:click.prevent="addItem"
                        class="text-sm text-green-600 px-2 py-1 rounded hover:bg-green-100 cursor-pointer ml-auto">
                        Tambah Item
                    </button>
                </div>

                <div class="w-full border-b-1 border-gray-200 mb-2"></div>

                <div class="space-y-4">
                    @foreach ($projectItems as $index => $item)
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-2 items-end">
                            <div class="col-span-3">
                                <flux:input size="sm" label="Item" type="text"
                                    wire:model.defer="projectItems.{{ $index }}.name" />
                            </div>
                            <div>
                                <flux:input size="sm" label="Kuantitas" type="number" step="1"
                                    wire:model.live="projectItems.{{ $index }}.quantity" />
                            </div>
                            <div>
                                <flux:input size="sm" label="Harga Satuan" type="number" step="0.01"
                                    wire:model.live="projectItems.{{ $index }}.unit_price" />
                            </div>
                            <div>
                                <button type="button" wire:click.prevent="removeItem({{ $index }})"
                                    class="text-sm text-red-600 px-2 py-1 rounded hover:bg-red-100 cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                            <div class="col-span-5">
                                <flux:textarea size="sm" label="Deskripsi" type="text"
                                    wire:model.defer="projectItems.{{ $index }}.description" rows="2" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <button type="submit"
                class="text-sm px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer">
                Simpan Proyek
            </button>
        </div>
    </form>
</section>
