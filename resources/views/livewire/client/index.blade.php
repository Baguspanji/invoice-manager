<?php

use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\Client;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;

new class extends Component {
    use WithPagination;

    public ?Client $client = null;

    #[Url(as: 'q')]
    public ?string $search = '';

    /**
     * Set page title
     */
    public function rendering(View $view): void
    {
        $view->title('Klien');
    }

    // columns with rules
    #[Rule('required|string|max:255', 'Nama harus diisi.')]
    public string $name = '';
    #[Rule('required|email|max:255', 'Email tidak valid.')]
    public string $email = '';
    #[Rule('nullable|string|max:20', 'Nomor telepon tidak valid.')]
    public string $phone = '';
    #[Rule('nullable|string|max:500', 'Alamat tidak valid.')]
    public string $address = '';
    #[Rule('nullable|string|max:50', 'NPWP tidak valid.')]
    public string $npwp = '';

    /**
     * Take data from model to component
     */
    #[Computed]
    public function with(): array
    {
        return [
            'requests' => Client::query()
                ->select(['id', 'name', 'email', 'phone', 'address', 'npwp'])
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%')
                        ->orWhere('npwp', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10),
        ];
    }

    /**
     * Edit client data
     */
    public function edit(int $id)
    {
        $this->client = Client::findOrFail($id);
        $this->name = $this->client->name;
        $this->email = $this->client->email;
        $this->phone = $this->client->phone ?? '';
        $this->address = $this->client->address ?? '';
        $this->npwp = $this->client->npwp ?? '';

        $this->modal('form-data')->show();
    }

    /**
     * Submit form to create or update client
     */
    public function submit()
    {
        $this->validate(
            null,
            [],
            [
                'name' => 'Nama',
                'email' => 'Email',
                'phone' => 'No Telp',
                'address' => 'Alamat',
                'npwp' => 'NPWP',
            ],
        );

        if ($this->client === null) {
            $this->createClient();

            $this->dispatch('alert', type: 'success', message: 'Klien berhasil disimpan.');
        } else {
            $this->updateClient();

            $this->dispatch('alert', type: 'success', message: 'Klien berhasil diperbarui.');
        }

        $this->modal('form-data')->close();
    }

    /**
     * Create new client
     */
    protected function createClient()
    {
        Client::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'npwp' => $this->npwp,
        ]);

        $this->resetForm();
    }

    /**
     * Update existing client
     */
    protected function updateClient()
    {
        $this->client->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'npwp' => $this->npwp,
        ]);

        $this->resetForm();
    }

    /**
     * Reset form fields
     */
    public function resetForm()
    {
        $this->client = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->npwp = '';
    }
}; ?>

<section>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-bold">Daftar Klien</h2>
            <p class="text-sm text-gray-600">
                Kelola informasi klien Anda di sini. Tambahkan, edit, atau hapus data klien sesuai kebutuhan.
            </p>
        </div>
        <button class="text-sm px-2 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer"
            x-on:click="$flux.modal('form-data').show(); $wire.resetForm();">
            <flux:icon name="plus" class="w-4 h-4 inline-block -mt-1" />
            Tambah Klien
        </button>
    </div>

    <!-- Search Bar -->
    <div class="mb-4 flex flex-grow">
        <flux:input size="sm" type="search" placeholder="Cari klien..." wire:model.live="search" class="max-w-xs"/>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama Klien</th>
                    <th scope="col" class="px-6 py-3">Email</th>
                    <th scope="col" class="px-6 py-3">No Telp</th>
                    <th scope="col" class="px-6 py-3">Alamat</th>
                    <th scope="col" class="px-6 py-3">NPWP</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $request)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap hover:font-semibold cursor-pointer"
                            wire:click="edit({{ $request->id }})">{{ $request->name }}</td>
                        <td class="px-6 py-4">{{ $request->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->phone ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->address ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $request->npwp ?? '-' }}</td>
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

    <!-- Modal Form -->
    <flux:modal name="form-data" class="md:w-xl">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ $client ? 'Edit Klien' : 'Tambah Klien' }}</flux:heading>
            </div>

            <flux:input size="sm" label="Nama" placeholder="Masukkan Nama" wire:model="name" />

            <flux:input size="sm" label="Email" placeholder="Masukkan Email" wire:model="email" />

            <flux:input size="sm" label="No Telp" placeholder="Masukkan No Telp" wire:model="phone" />

            <flux:textarea size="sm" label="Alamat" placeholder="Masukkan Alamat" wire:model="address" />

            <flux:input size="sm" label="NPWP" placeholder="Masukkan NPWP" wire:model="npwp" />

            <div class="flex">
                <flux:spacer />
                <flux:button size="sm" type="button" wire:click="submit" variant="primary" class="cursor-pointer">Simpan</flux:button>
            </div>
        </div>
    </flux:modal>
</section>
