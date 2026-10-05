<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'offer_letter_id', 'type', 'file_path', 'file_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function offerLetter()
    {
        return $this->belongsTo(OfferLetter::class);
    }
}
