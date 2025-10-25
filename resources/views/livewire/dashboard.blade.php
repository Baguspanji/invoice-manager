<?php

use Livewire\Volt\Component;

new class extends Component {
    public $totalClients = 0;
    public $totalProjects = 0;
    public $totalInvoices = 0;
    public $totalPaidInvoices = 0;
    public $monthlyInvoices = [];
    public $selectedYear;

    public function mount()
    {
        $this->selectedYear = date('Y'); // Set default to current year
        $this->fetchData($this->selectedYear);
    }

    public function updatedSelectedYear($year)
    {
        $this->fetchData($year);
    }

    public function fetchData($year)
    {
        $this->totalClients = \App\Models\Client::count();
        $this->totalProjects = \App\Models\Project::count();
        $this->totalInvoices = \App\Models\Invoice::count();
        $this->totalPaidInvoices = \App\Models\Invoice::where('status', 'paid')->count();

        $this->monthlyInvoices = \App\Models\Invoice::selectRaw('strftime("%m", created_at) as month, COUNT(*) as count')->whereYear('created_at', $year)->groupBy('month')->orderBy('month')->pluck('count')->toArray();
    }
}; ?>

<section>
    <!-- Statistics Section -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="p-4 border rounded-lg bg-white shadow">
            <h3 class="text-lg font-semibold">Total Klien</h3>
            <p class="text-2xl">{{ $totalClients }}</p>
        </div>
        <div class="p-4 border rounded-lg bg-white shadow">
            <h3 class="text-lg font-semibold">Total Proyek</h3>
            <p class="text-2xl">{{ $totalProjects }}</p>
        </div>
        <div class="p-4 border rounded-lg bg-white shadow">
            <h3 class="text-lg font-semibold">Total Invoice</h3>
            <p class="text-2xl">{{ $totalInvoices }}</p>
        </div>
        <div class="p-4 border rounded-lg bg-white shadow">
            <h3 class="text-lg font-semibold">Total Invoice Terbayar</h3>
            <p class="text-2xl">{{ $totalPaidInvoices }}</p>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="mt-8 p-6 bg-white border rounded-lg shadow">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold mb-4">Monthly Invoices for {{ $selectedYear }}</h3>
            <div class="md:min-w-[12rem]">
                <flux:select wire:model.live="selectedYear">
                    @foreach (range(2020, date('Y')) as $year)
                        <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>
        @if ($monthlyInvoices)
            <div class="relative h-96 w-full">
                <canvas id="monthlyInvoicesChart"></canvas>
            </div>
        @else
            <div class="text-center text-gray-500 py-20">
                Tidak ada data untuk tahun ini.
            </div>
        @endif
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('monthlyInvoicesChart').getContext('2d');
        const monthlyData = @json($monthlyInvoices);
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Invoices per Month',
                    data: monthlyData.concat(Array(12 - monthlyData.length).fill(0)),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });

        // Update chart data when year changes
        Livewire.on('updateChart', (newData) => {
            chart.data.datasets[0].data = newData.concat(Array(12 - newData.length).fill(0));
            chart.update();
        });
    });
</script>
