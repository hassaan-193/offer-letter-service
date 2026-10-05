<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    protected $fillable = [
        'offer_letter_id', 'signature_data', 'signature_path',
        'ip_address', 'user_agent', 'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function offerLetter()
    {
        return $this->belongsTo(OfferLetter::class);
    }
}
