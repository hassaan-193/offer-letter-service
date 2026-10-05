<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfferLetter;
use App\Services\AuditService;
use App\Services\OfferPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferLetterController extends Controller
{
    public function __construct(
        protected AuditService $audit,
        protected OfferPdfService $pdfService
    ) {}

    public function index(Request $request)
    {
        $query = OfferLetter::with('admin')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('passport_number', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('offer_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('offer_date', '<=', $request->date_to);
        }

        $offers = $query->paginate(15)->withQueryString();

        return view('admin.offers.index', compact('offers'));
    }

    public function create()
    {
        return view('admin.offers.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateOffer($request);
        $validated['admin_id'] = Auth::guard('admin')->id();
        $validated['status']   = 'draft';

        $offer = OfferLetter::create($validated);

        $this->audit->log($offer, 'offer_created');

        if ($request->action === 'publish') {
            return $this->doPublish($offer);
        }

        return redirect()->route('admin.offers.show', $offer->id)
            ->with('success', 'Offer letter saved as draft.');
    }

    public function show(OfferLetter $offer)
    {
        $offer->load(['signature', 'events', 'documents', 'admin']);
        return view('admin.offers.show', compact('offer'));
    }

    public function edit(OfferLetter $offer)
    {
        if ($offer->status === 'signed') {
            return back()->with('error', 'Signed offer letters cannot be edited.');
        }

        return view('admin.offers.edit', compact('offer'));
    }

    public function update(Request $request, OfferLetter $offer)
    {
        if ($offer->status === 'signed') {
            return back()->with('error', 'Signed offer letters cannot be edited.');
        }

        $validated = $this->validateOffer($request);
        $offer->update($validated);

        $this->audit->log($offer, 'offer_updated');

        if ($request->action === 'publish') {
            return $this->doPublish($offer);
        }

        return redirect()->route('admin.offers.show', $offer->id)
            ->with('success', 'Offer letter updated successfully.');
    }

    public function preview(OfferLetter $offer)
    {
        return view('admin.offers.preview', compact('offer'));
    }

    public function publish(OfferLetter $offer)
    {
        return $this->doPublish($offer);
    }

    protected function doPublish(OfferLetter $offer): \Illuminate\Http\RedirectResponse
    {
        if ($offer->status === 'signed') {
            return back()->with('error', 'This offer has already been signed.');
        }

        $offer->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);

        // Generate original PDF
        $this->pdfService->generateOriginal($offer);

        $this->audit->log($offer, 'offer_published');
        $this->audit->log($offer, 'link_generated', null, null, ['token' => $offer->token]);

        return redirect()->route('admin.offers.show', $offer->id)
            ->with('published', true)
            ->with('success', 'Offer letter published. Copy the link below to share with the candidate.');
    }

    public function revoke(OfferLetter $offer)
    {
        if ($offer->status === 'signed') {
            return back()->with('error', 'Cannot revoke a signed offer.');
        }

        $offer->update([
            'status'     => 'revoked',
            'revoked_at' => now(),
        ]);

        $this->audit->log($offer, 'offer_revoked');

        return back()->with('success', 'Offer letter has been revoked.');
    }

    public function downloadOriginal(OfferLetter $offer)
    {
        $doc = $offer->originalDocument;

        if (!$doc) {
            // Generate on-demand if missing
            $doc = $this->pdfService->generateOriginal($offer);
        }

        $this->audit->log($offer, 'offer_downloaded', null, null, ['type' => 'original']);

        $path = $this->pdfService->getDocumentPath($doc);

        return response()->download($path, $doc->file_name, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function downloadSigned(OfferLetter $offer)
    {
        $doc = $offer->signedDocument;

        if (!$doc) {
            return back()->with('error', 'Signed document is not available yet.');
        }

        $this->audit->log($offer, 'offer_downloaded', null, null, ['type' => 'signed']);

        $path = $this->pdfService->getDocumentPath($doc);

        return response()->download($path, $doc->file_name, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function destroy(OfferLetter $offer)
    {
        $candidateName = $offer->candidate_name;

        // Clean up any files stored in storage/app/offers/{id}
        \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory("offers/{$offer->id}");

        $offer->delete();

        return redirect()->route('admin.offers.index')
            ->with('success', "Offer letter for \"{$candidateName}\" has been permanently deleted.");
    }

    protected function validateOffer(Request $request): array
    {
        return $request->validate([
            'candidate_name'       => ['required', 'string', 'max:255'],
            'passport_number'      => ['required', 'string', 'max:50'],
            'nationality'          => ['required', 'string', 'max:100'],
            'designation'          => ['required', 'string', 'max:255'],
            'place_of_posting'     => ['required', 'string', 'max:255'],
            'offer_date'           => ['required', 'date'],
            'validity_date'        => ['required', 'date', 'after_or_equal:offer_date'],
            'joining_date'         => ['nullable', 'date'],
            'probation_period'     => ['nullable', 'string', 'max:100'],
            'contract_duration'    => ['nullable', 'string', 'max:100'],
            'working_hours'        => ['nullable', 'string', 'max:100'],
            'weekly_day_off'       => ['nullable', 'string', 'max:100'],
            'overtime_info'        => ['nullable', 'string'],
            'basic_salary'         => ['required', 'numeric', 'min:0'],
            'basic_salary_words'   => ['required', 'string', 'max:500'],
            'other_allowances'     => ['required', 'numeric', 'min:0'],
            'other_allowances_words' => ['required', 'string', 'max:500'],
            'total_salary'         => ['required', 'numeric', 'min:0'],
            'total_salary_words'   => ['required', 'string', 'max:500'],
            'salary_currency'      => ['nullable', 'string', 'max:10'],
            'annual_leave'         => ['nullable', 'string', 'max:255'],
            'air_ticket_allowance' => ['nullable', 'string', 'max:255'],
            'medical_requirements' => ['nullable', 'string'],
            'notice_period'          => ['nullable', 'string', 'max:100'],
            'additional_terms_title' => ['nullable', 'string', 'max:255'],
            'additional_terms'       => ['nullable', 'string'],
        ]);
    }
}
