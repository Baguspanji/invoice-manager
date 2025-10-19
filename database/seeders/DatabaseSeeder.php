<?php
namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectItem;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name'  => 'Administrator',
            'email' => 'admin@admin.com',
        ]);

        Client::factory(10)->create();

        // Create project with dummy data
        $project = Project::create([
            'client_id'    => 1,
            'project_number' => (new Project())->generateProjectNumber(),
            'name'         => 'Website Company Profile',
            'description'  => 'Website Company Profile for a tech company',
            'total_value'  => 5000000,
            'billed_value' => 2500000,
            'start_date'   => now(),
            'due_date'     => now()->addMonths(2),
            'status'       => 'pending',
        ]);

        // Create items for project
        // Create multiple items for the project
        $items = [
            [
            'project_id' => $project->id,
            'name' => 'Website Design',
            'description' => 'Creating UI/UX design for the company website',
            'quantity' => 1,
            'unit_price' => 2000000,
            'total_price' => 2000000,
            ],
            [
            'project_id' => $project->id,
            'name' => 'Frontend Development',
            'description' => 'Developing the frontend part of the website',
            'quantity' => 1,
            'unit_price' => 1500000,
            'total_price' => 1500000,
            ],
            [
            'project_id' => $project->id,
            'name' => 'Backend Development',
            'description' => 'Developing the backend part of the website',
            'quantity' => 1,
            'unit_price' => 1500000,
            'total_price' => 1500000,
            ],
        ];

        foreach ($items as $item) {
            ProjectItem::create($item);
        }
    }
}
