<?php

namespace App\Services;

use App\Models\Document;
use App\Models\OfferLetter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class OfferPdfService
{
    public function generateOriginal(OfferLetter $offer): Document
    {
        $pdf = Pdf::loadView('pdf.offer-template', ['offer' => $offer])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        $dir  = "offers/{$offer->id}/original";
        $name = "offer-{$offer->token}-original.pdf";
        $path = "{$dir}/{$name}";

        Storage::disk('local')->makeDirectory($dir);
        Storage::disk('local')->put($path, $pdf->output());

        // Upsert document record
        $doc = Document::updateOrCreate(
            ['offer_letter_id' => $offer->id, 'type' => 'original'],
            ['file_path' => $path, 'file_name' => $name]
        );

        return $doc;
    }

    public function generateSigned(OfferLetter $offer): Document
    {
        $signature = $offer->signature;

        $pdf = Pdf::loadView('pdf.offer-template-signed', [
            'offer'     => $offer,
            'signature' => $signature,
        ])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        $dir  = "offers/{$offer->id}/signed";
        $name = "offer-{$offer->token}-signed.pdf";
        $path = "{$dir}/{$name}";

        Storage::disk('local')->makeDirectory($dir);
        Storage::disk('local')->put($path, $pdf->output());

        $doc = Document::updateOrCreate(
            ['offer_letter_id' => $offer->id, 'type' => 'signed'],
            ['file_path' => $path, 'file_name' => $name]
        );

        return $doc;
    }

    public function getDocumentPath(Document $document): string
    {
        return Storage::disk('local')->path($document->file_path);
    }
}
