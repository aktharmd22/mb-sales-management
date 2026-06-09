<?php

namespace App\Models;

use App\Support\Pipeline;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    /** No-visit window (days) after which a client is flagged dormant. */
    public const DORMANT_DAYS = 30;

    protected $fillable = [
        'business_name',
        'contact_person',
        'contact_phone',
        'address',
        'category',
        'assigned_to',
        'created_by',
        'pipeline_stage',
        'status',
        'notes',
        'last_visit_at',
    ];

    protected $casts = [
        'last_visit_at' => 'datetime',
    ];

    /* ---------------- Relationships ---------------- */

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class)->latest('visit_date');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /* ---------------- Scopes ---------------- */

    public function scopeForUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('assigned_to', $user->id);
    }

    public function scopeDormant($query)
    {
        return $query->where('status', 'dormant');
    }

    /* ---------------- Helpers ---------------- */

    public function isDormant(): bool
    {
        if ($this->last_visit_at === null) {
            return $this->created_at?->lt(now()->subDays(self::DORMANT_DAYS)) ?? false;
        }

        return $this->last_visit_at->lt(now()->subDays(self::DORMANT_DAYS));
    }

    public function stageLabel(): string
    {
        return Pipeline::label($this->pipeline_stage);
    }

    public function stageColor(): string
    {
        return Pipeline::color($this->pipeline_stage);
    }

    /**
     * Recompute pipeline_stage (highest reached), last_visit_at and dormant
     * status from the underlying visits. Call after a visit changes.
     */
    public function refreshPipeline(): void
    {
        $visits = $this->visits()->get();

        $highest = $this->pipeline_stage;
        foreach ($visits as $visit) {
            if (Pipeline::rank($visit->visit_level) > Pipeline::rank($highest)) {
                $highest = $visit->visit_level;
            }
        }

        $last = $visits->max('visit_date');

        $this->pipeline_stage = $highest;
        $this->last_visit_at = $last;
        $this->status = $this->isDormant() ? 'dormant' : 'active';
        $this->save();
    }
}
