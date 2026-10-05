<?php

namespace App\Services;

use App\Models\OfferEvent;
use App\Models\OfferLetter;

class AuditService
{
    public function log(
        OfferLetter $offer,
        string $eventType,
        ?string $ip = null,
        ?string $userAgent = null,
        array $metadata = []
    ): void {
        OfferEvent::create([
            'offer_letter_id' => $offer->id,
            'event_type'      => $eventType,
            'ip_address'      => $ip ?? request()->ip(),
            'user_agent'      => $userAgent ?? request()->userAgent(),
            'metadata'        => $metadata ?: null,
        ]);
    }
}
