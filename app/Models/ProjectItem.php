<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectItem extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'description',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function project()
    {
        $this->belongsTo(Project::class);
    }
}
