<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'university_id',
        'dob',
        'gender',
        'blood_group',
        'nationality',
        'role_category',
        'department',
        'year_semester',
        'email',
        'phone',
        'emergency_name',
        'emergency_phone',
        'address',
        'allergies',
        'conditions',
        'medications',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'conditions' => 'array',
            'is_verified' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id')->latest();
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id')->latest();
    }

    public function queues(): HasMany
    {
        return $this->hasMany(MedicalQueue::class, 'patient_id')->latest();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
