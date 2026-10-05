<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'offer_letter_id', 'event_type', 'ip_address', 'user_agent', 'metadata',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function offerLetter()
    {
        return $this->belongsTo(OfferLetter::class);
    }

    // Human readable event label
    public function getEventLabelAttribute(): string
    {
        return match($this->event_type) {
            'offer_created'    => 'Offer Created',
            'offer_updated'    => 'Offer Updated',
            'offer_published'  => 'Offer Published',
            'link_generated'   => 'Link Generated',
            'offer_viewed'     => 'Offer Viewed',
            'reading_started'  => 'Reading Started',
            'reading_complete' => 'Reading Completed',
            'acknowledgment_accepted' => 'Acknowledgment Accepted',
            'signature_submitted'     => 'Signature Submitted',
            'offer_signed'     => 'Offer Signed',
            'offer_downloaded' => 'Offer Downloaded',
            'offer_revoked'    => 'Offer Revoked',
            'offer_expired'    => 'Offer Expired',
            default            => ucwords(str_replace('_', ' ', $this->event_type)),
        };
    }
}
