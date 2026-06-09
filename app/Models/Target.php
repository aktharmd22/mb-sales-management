<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Target extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period',
        'visits_target',
        'revenue_target',
    ];

    protected $casts = [
        'visits_target' => 'integer',
        'revenue_target' => 'decimal:2',
    ];

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Current period string, e.g. "2026-06". */
    public static function currentPeriod(): string
    {
        return now()->format('Y-m');
    }
}
