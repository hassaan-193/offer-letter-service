<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\OfferLetter;
use App\Models\Signature;
use App\Services\AuditService;
use App\Services\OfferPdfService;
use Illuminate\Http\Request;

class OfferViewController extends Controller
{
    public function __construct(
        protected AuditService $audit,
        protected OfferPdfService $pdfService
    ) {}

    public function show(string $token)
    {
        $offer = OfferLetter::where('token', $token)->firstOrFail();

        // Check if already signed - allow viewing
        if ($offer->status === 'signed') {
            return view('candidate.offer', compact('offer'));
        }

        // Check revoked
        if ($offer->isRevoked()) {
            return view('candidate.revoked', compact('offer'));
        }

        // Check expired
        if ($offer->status === 'draft') {
            abort(404);
        }

        if ($offer->isExpired()) {
            $offer->update(['status' => 'expired', 'expired_at' => now()]);
            $this->audit->log($offer, 'offer_expired');
            return view('candidate.expired', compact('offer'));
        }

        // Track first view
        if (!$offer->viewed_at) {
            $offer->update([
                'status'    => 'viewed',
                'viewed_at' => now(),
            ]);
            $this->audit->log($offer, 'offer_viewed');
        }

        return view('candidate.offer', compact('offer'));
    }

    public function readingComplete(Request $request, string $token)
    {
        $offer = OfferLetter::where('token', $token)->firstOrFail();

        if (!$offer->isAvailableForCandidate() || $offer->status === 'signed') {
            return response()->json(['ok' => false, 'message' => 'Offer not available.'], 403);
        }

        if ($offer->status === 'viewed') {
            $offer->update(['status' => 'pending_signature']);
            $this->audit->log($offer, 'reading_complete');
        }

        return response()->json(['ok' => true]);
    }

    public function sign(Request $request, string $token)
    {
        $offer = OfferLetter::where('token', $token)->firstOrFail();

        if (!$offer->isAvailableForCandidate()) {
            return response()->json(['ok' => false, 'message' => 'Offer is no longer available.'], 403);
        }

        if ($offer->status === 'signed') {
            return response()->json(['ok' => false, 'message' => 'Offer has already been signed.'], 409);
        }

        $request->validate([
            'signature_data' => ['required', 'string'],
            'acknowledged'   => ['required', 'accepted'],
        ]);

        $signatureData = $request->signature_data;

        // Store signature image file
        $sigDir  = "offers/{$offer->id}/signatures";
        $sigFile = "signature-" . now()->timestamp . ".png";
        $sigPath = "{$sigDir}/{$sigFile}";

        // Decode base64 and save
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $signatureData);
        $imageData = base64_decode($imageData);
        \Illuminate\Support\Facades\Storage::disk('local')->makeDirectory($sigDir);
        \Illuminate\Support\Facades\Storage::disk('local')->put($sigPath, $imageData);

        // Save signature record
        Signature::create([
            'offer_letter_id' => $offer->id,
            'signature_data'  => $signatureData,
            'signature_path'  => $sigPath,
            'ip_address'      => $request->ip(),
            'user_agent'      => $request->userAgent(),
            'signed_at'       => now(),
        ]);

        // Update offer status
        $offer->update([
            'status'              => 'signed',
            'acknowledged_at'     => now(),
            'signed_at'           => now(),
            'candidate_ip'        => $request->ip(),
            'candidate_user_agent'=> $request->userAgent(),
        ]);

        $this->audit->log($offer, 'acknowledgment_accepted');
        $this->audit->log($offer, 'signature_submitted');
        $this->audit->log($offer, 'offer_signed');

        // Generate signed PDF
        $offer->refresh()->load('signature');
        $doc = $this->pdfService->generateSigned($offer);

        return response()->json([
            'ok'         => true,
            'message'    => 'Offer accepted and signed successfully.',
            'signed_url' => route('candidate.offer.download', $token),
        ]);
    }

    public function download(string $token)
    {
        $offer = OfferLetter::where('token', $token)->firstOrFail();

        if ($offer->status !== 'signed') {
            abort(403, 'Offer has not been signed yet.');
        }

        $doc = $offer->signedDocument;

        if (!$doc) {
            // Generate on demand
            $offer->load('signature');
            $doc = $this->pdfService->generateSigned($offer);
        }

        $this->audit->log($offer, 'offer_downloaded', null, null, ['type' => 'signed', 'by' => 'candidate']);

        $path = $this->pdfService->getDocumentPath($doc);

        return response()->download($path, "Signed-Offer-Letter-{$offer->candidate_name}.pdf", [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
