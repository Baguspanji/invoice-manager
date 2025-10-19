<?php
namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'client_id',
        'project_number',
        'name',
        'description',
        'total_value',
        'billed_value',
        'start_date',
        'due_date',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'due_date'     => 'date',
            'billed_value' => 'decimal:2',
            'total_value'  => 'decimal:2',
            'status'       => ProjectStatus::class,
        ];
    }

    public function items()
    {
        return $this->hasMany(ProjectItem::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function generateProjectNumber(): string
    {
        $latestProject = self::latest()->first();
        $nextNumber    = $latestProject ? ((int) substr($latestProject->project_number, -4)) + 1 : 1;
        return 'PRJ-' . date('Ymd') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
