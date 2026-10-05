<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OfferLetter extends Model
{
    protected $fillable = [
        'token', 'candidate_name', 'passport_number', 'nationality',
        'designation', 'place_of_posting', 'offer_date', 'validity_date',
        'joining_date', 'probation_period', 'contract_duration',
        'working_hours', 'weekly_day_off', 'overtime_info',
        'basic_salary', 'basic_salary_words', 'other_allowances',
        'other_allowances_words', 'total_salary', 'total_salary_words',
        'salary_currency', 'annual_leave', 'air_ticket_allowance',
        'medical_requirements', 'notice_period', 'additional_terms_title', 'additional_terms',
        'status', 'published_at', 'viewed_at', 'acknowledged_at',
        'signed_at', 'expired_at', 'revoked_at',
        'candidate_ip', 'candidate_user_agent', 'admin_id',
    ];

    protected $casts = [
        'offer_date'      => 'date',
        'validity_date'   => 'date',
        'joining_date'    => 'date',
        'published_at'    => 'datetime',
        'viewed_at'       => 'datetime',
        'acknowledged_at' => 'datetime',
        'signed_at'       => 'datetime',
        'expired_at'      => 'datetime',
        'revoked_at'      => 'datetime',
        'basic_salary'    => 'decimal:2',
        'other_allowances'=> 'decimal:2',
        'total_salary'    => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (OfferLetter $offer) {
            if (empty($offer->token)) {
                $offer->token = Str::random(64);
            }
        });
    }

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function signature()
    {
        return $this->hasOne(Signature::class);
    }

    public function events()
    {
        return $this->hasMany(OfferEvent::class)->orderByDesc('created_at');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function originalDocument()
    {
        return $this->hasOne(Document::class)->where('type', 'original');
    }

    public function signedDocument()
    {
        return $this->hasOne(Document::class)->where('type', 'signed');
    }

    // Helpers
    public function isExpired(): bool
    {
        return $this->validity_date && $this->validity_date->isPast() && $this->status !== 'signed';
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    public function isAvailableForCandidate(): bool
    {
        if ($this->status === 'draft') return false;
        if ($this->isRevoked()) return false;
        if ($this->status === 'signed') return true; // can view signed
        if ($this->isExpired()) return false;
        return true;
    }

    public function candidateLink(): string
    {
        return url('/offer/' . $this->token);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'draft'             => ['label' => 'Draft',             'class' => 'badge-draft'],
            'published'         => ['label' => 'Published',         'class' => 'badge-published'],
            'viewed'            => ['label' => 'Viewed',            'class' => 'badge-viewed'],
            'pending_signature' => ['label' => 'Pending Signature', 'class' => 'badge-pending'],
            'accepted'          => ['label' => 'Accepted',          'class' => 'badge-accepted'],
            'signed'            => ['label' => 'Signed',            'class' => 'badge-signed'],
            'expired'           => ['label' => 'Expired',           'class' => 'badge-expired'],
            'revoked'           => ['label' => 'Revoked',           'class' => 'badge-revoked'],
            default             => ['label' => ucfirst($this->status), 'class' => 'badge-default'],
        };
    }
}
