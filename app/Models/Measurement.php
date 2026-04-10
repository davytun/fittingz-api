<?php

namespace App\Models;

use App\Enums\MeasurementUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Measurement extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'client_id',
        'user_id',
        'name',
        'fields',
        'unit',
        'notes',
        'measurement_date',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'unit' => MeasurementUnit::class,
            'measurement_date' => 'date',
            'is_default' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Auto-set as default if it's the first measurement for this client
            if (!isset($model->is_default)) {
                $hasExisting = static::where('client_id', $model->client_id)->exists();
                $model->is_default = !$hasExisting;
            }

            // If setting as default, unset other defaults for this client
            if ($model->is_default) {
                static::where('client_id', $model->client_id)
                    ->where('id', '!=', $model->id)
                    ->update(['is_default' => false]);
            }
        });

        static::updating(function ($model) {
            // If setting as default, unset other defaults for this client
            if ($model->is_default && $model->isDirty('is_default')) {
                static::where('client_id', $model->client_id)
                    ->where('id', '!=', $model->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}