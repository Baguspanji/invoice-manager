<?php
namespace App\Enums;

enum ProjectStatus: string {
    case PENDING     = 'pending';
    case PLANNING    = 'planning';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING     => 'Tertunda',
            self::PLANNING    => 'Perencanaan',
            self::IN_PROGRESS => 'Sedang Berjalan',
            self::COMPLETED   => 'Selesai',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PENDING     => 'Proyek belum dimulai dan sedang menunggu persetujuan.',
            self::PLANNING    => 'Proyek dalam tahap perencanaan dan persiapan.',
            self::IN_PROGRESS => 'Proyek sedang aktif dikerjakan.',
            self::COMPLETED   => 'Proyek telah selesai dikerjakan.',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING     => 'gray',
            self::PLANNING    => 'blue',
            self::IN_PROGRESS => 'warning',
            self::COMPLETED   => 'green',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $status) => ['value' => $status->value, 'label' => $status->label()],
            self::cases()
        );
    }
}
