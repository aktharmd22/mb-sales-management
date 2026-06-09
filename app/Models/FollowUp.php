<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'visit_id',
        'user_id',
        'due_date',
        'note',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /* ---------------- Relationships ---------------- */

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* ---------------- Scopes ---------------- */

    public function scopeForUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDueToday($query)
    {
        return $query->pending()->whereDate('due_date', today());
    }

    public function scopeOverdue($query)
    {
        return $query->pending()->whereDate('due_date', '<', today());
    }

    /* ---------------- Helpers ---------------- */

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isBefore(today());
    }

    public function isDueToday(): bool
    {
        return $this->status === 'pending' && $this->due_date->isToday();
    }
}
