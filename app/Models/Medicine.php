<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'generic_name',
        'category',
        'dosage_form',
        'strength',
        'batch_number',
        'stock_quantity',
        'reorder_level',
        'unit',
        'expiry_date',
        'manufacturer',
        'instructions_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'stock_quantity' => 'integer',
            'reorder_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class, 'medicine_id');
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function isNearExpiry(int $days = 60): bool
    {
        return ! $this->isExpired() && $this->expiry_date->diffInDays(Carbon::now()) <= $days;
    }
}
