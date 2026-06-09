<?php

namespace App\Models;

use App\Support\Pipeline;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'visit_date',
        'person_met',
        'contact_phone',
        'visit_level',
        'decision_maker_met',
        'interested',
        'follow_up_done',
        'revenue_potential',
        'notes',
        'photo_path',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'decision_maker_met' => 'boolean',
        'interested' => 'boolean',
        'follow_up_done' => 'boolean',
        'revenue_potential' => 'decimal:2',
    ];

    /* ---------------- Relationships ---------------- */

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    /* ---------------- Scopes ---------------- */

    public function scopeForUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public function scopeBetween($query, $start, $end)
    {
        return $query->whereBetween('visit_date', [$start, $end]);
    }

    /* ---------------- Helpers ---------------- */

    public function levelLabel(): string
    {
        return Pipeline::label($this->visit_level);
    }

    public function levelColor(): string
    {
        return Pipeline::color($this->visit_level);
    }
}
