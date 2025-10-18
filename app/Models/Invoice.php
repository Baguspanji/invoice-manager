<?php
namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'client_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date'   => 'date',
            'subtotal'   => 'decimal:2',
            'tax'        => 'decimal:2',
            'discount'   => 'decimal:2',
            'total'      => 'decimal:2',
            'status'     => InvoiceStatus::class,
        ];
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function generateInvoiceNumber(): string
    {
        $latestInvoice = self::latest()->first();
        $nextNumber = $latestInvoice ? ((int) substr($latestInvoice->invoice_number, -4)) + 1 : 1;
        return 'INV-' . date('Ymd') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
