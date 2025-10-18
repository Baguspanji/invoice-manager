<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case PAID = 'paid';
    case OVERDUE = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Terkirim',
            self::PAID => 'Lunas',
            self::OVERDUE => 'Terlambat',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::DRAFT => 'Invoice masih dalam status draft dan belum dikirim ke klien.',
            self::SENT => 'Invoice telah dikirim ke klien dan menunggu pembayaran.',
            self::PAID => 'Invoice telah dibayar oleh klien.',
            self::OVERDUE => 'Invoice belum dibayar dan sudah melewati tanggal jatuh tempo.',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'blue',
            self::PAID => 'green',
            self::OVERDUE => 'red',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $status) => ['value' => $status->value, 'label' => $status->label()],
            self::cases()
        );
    }
}
