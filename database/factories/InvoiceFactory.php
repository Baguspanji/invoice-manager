<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => \App\Models\Client::factory(),
            'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
            'name' => $this->faker->sentence(3),
            'issue_date' => $this->faker->date(),
            'due_date' => $this->faker->date(),
            'status' => 'draft',
            'subtotal' => $this->faker->randomFloat(2, 100, 1000),
            'total' => $this->faker->randomFloat(2, 100, 1000),
        ];
    }
}
