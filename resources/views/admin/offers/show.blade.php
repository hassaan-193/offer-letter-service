@extends('layouts.admin')
@section('title', $offer->candidate_name . ' — Offer Details')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $offer->candidate_name }}</h1>
        <p class="page-subtitle">{{ $offer->designation }} &bull; {{ $offer->place_of_posting }}</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <ol class="breadcrumb" style="margin: 0;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.offers.index') }}">Offer Letters</a></li>
            <li class="breadcrumb-item active" style="color: var(--fts-maroon); font-weight: 600;">Details</li>
        </ol>

        <div style="display: flex; gap: 8px; margin-left: 15px;">
            @if($offer->status === 'draft')
                <a href="{{ route('admin.offers.edit', $offer) }}" class="btn btn-secondary">Edit Draft</a>
                <form method="POST" action="{{ route('admin.offers.publish', $offer) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">Publish Offer</button>
                </form>
            @elseif($offer->status !== 'signed' && $offer->status !== 'revoked')
                <a href="{{ route('admin.offers.edit', $offer) }}" class="btn btn-secondary">Edit</a>
                <form method="POST" action="{{ route('admin.offers.revoke', $offer) }}" style="display:inline"
                    onsubmit="return confirm('Are you sure you want to revoke this offer? The candidate will no longer be able to access it.')">
                    @csrf
                    <button type="submit" class="btn btn-danger">Revoke</button>
                </form>
            @endif
            <a href="{{ route('admin.offers.preview', $offer) }}" target="_blank" class="btn btn-ghost" title="Preview official letterhead document">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Preview Letterhead
            </a>
            <a href="{{ route('admin.offers.download.original', $offer) }}" class="btn btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Original PDF
            </a>
            @if($offer->status === 'signed')
            <a href="{{ route('admin.offers.download.signed', $offer) }}" class="btn btn-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Signed PDF
            </a>
            @endif
            <form method="POST" action="{{ route('admin.offers.destroy', $offer) }}" style="display:inline;"
                onsubmit="return confirm('Are you sure you want to permanently delete this offer letter for {{ addslashes($offer->candidate_name) }}? All associated documents and history will be removed.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost" style="color: var(--fts-danger); border-color: #f5c6cb;" title="Permanently Delete Offer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; color: var(--fts-danger);"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Published alert with copy link --}}
@if(session('published') || in_array($offer->status, ['published','viewed','pending_signature','accepted']))
<div class="card" style="background: #eef2ff; border-top-color: var(--fts-maroon);">
    <div class="card-body" style="padding: 16px 20px;">
        <div style="font-weight: 600; color: #1e1b4b; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: var(--fts-maroon);"><circle cx="12" cy="12" r="10"/><path d="M10 15l5-3-5-3v6z"/></svg>
            Candidate Digital Signing Link
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <input type="text" id="candidate-link" class="form-control" value="{{ $offer->candidateLink() }}" readonly style="background: #ffffff; font-family: monospace; font-size: 13px;" />
            <button type="button" class="btn btn-primary" onclick="copyCandidateLink(this)">Copy Link</button>
            <a href="{{ $offer->candidateLink() }}" target="_blank" class="btn btn-ghost">Open Link</a>
        </div>
    </div>
</div>
@endif

{{-- Offer Details Card --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Offer Summary</h3>
        <span class="badge {{ $offer->status_badge['class'] }}">{{ $offer->status_badge['label'] }}</span>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted); font-weight: 600;">Candidate Name</div>
                <div style="font-size: 15px; font-weight: 600; color: var(--fts-darker); margin-top: 2px;">{{ $offer->candidate_name }}</div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted); font-weight: 600;">Passport Number</div>
                <div style="font-size: 15px; font-weight: 600; color: var(--fts-darker); margin-top: 2px;"><code>{{ $offer->passport_number }}</code></div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted); font-weight: 600;">Nationality</div>
                <div style="font-size: 15px; font-weight: 500; color: var(--fts-darker); margin-top: 2px;">{{ $offer->nationality }}</div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted); font-weight: 600;">Designation</div>
                <div style="font-size: 15px; font-weight: 600; color: var(--fts-maroon); margin-top: 2px;">{{ $offer->designation }}</div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted); font-weight: 600;">Place of Posting</div>
                <div style="font-size: 15px; font-weight: 500; color: var(--fts-darker); margin-top: 2px;">{{ $offer->place_of_posting }}</div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted); font-weight: 600;">Offer Date</div>
                <div style="font-size: 15px; font-weight: 500; color: var(--fts-darker); margin-top: 2px;">{{ $offer->offer_date->format('d/m/Y') }}</div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted); font-weight: 600;">Offer Validity Date</div>
                <div style="font-size: 15px; font-weight: 600; color: #dc3545; margin-top: 2px;">{{ $offer->validity_date->format('d/m/Y') }}</div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted); font-weight: 600;">Total Monthly Package</div>
                <div style="font-size: 16px; font-weight: 700; color: var(--fts-maroon); margin-top: 2px;">{{ $offer->salary_currency }} {{ number_format($offer->total_salary, 2) }}</div>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--fts-border); margin: 20px 0;">

        {{-- Lifecycle Timestamps --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted);">Created At</div>
                <div style="font-size: 13px; font-weight: 500;">{{ $offer->created_at->format('d M Y, H:i') }}</div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted);">Published At</div>
                <div style="font-size: 13px; font-weight: 500;">{{ $offer->published_at ? $offer->published_at->format('d M Y, H:i') : '—' }}</div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted);">Viewed At</div>
                <div style="font-size: 13px; font-weight: 500;">{{ $offer->viewed_at ? $offer->viewed_at->format('d M Y, H:i') : '—' }}</div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted);">Acknowledged At</div>
                <div style="font-size: 13px; font-weight: 500;">{{ $offer->acknowledged_at ? $offer->acknowledged_at->format('d M Y, H:i') : '—' }}</div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted);">Signed At</div>
                <div style="font-size: 13px; font-weight: 600; color: var(--fts-success);">{{ $offer->signed_at ? $offer->signed_at->format('d M Y, H:i') : '—' }}</div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--fts-text-muted);">Signer IP Address</div>
                <div style="font-size: 13px; font-weight: 500;"><code>{{ $offer->candidate_ip ?? '—' }}</code></div>
            </div>
        </div>
    </div>
</div>

@if(!empty($offer->additional_terms))
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--fts-maroon);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            {{ $offer->additional_terms_title ?: 'Custom Content & Additional Terms (Clause 10)' }}
        </h3>
    </div>
    <div class="card-body" style="white-space: pre-line; background: #fafafa; border-radius: 4px; line-height: 1.6; font-size: 13px; color: #222;">{{ $offer->additional_terms }}</div>
</div>
@endif

{{-- Candidate Signature preview if signed --}}
@if($offer->signature)
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Candidate Digital Signature</h3>
        <span class="badge badge-signed">Verified</span>
    </div>
    <div class="card-body" style="display: flex; gap: 30px; align-items: center; flex-wrap: wrap;">
        <div style="background: #ffffff; border: 1px solid var(--fts-border); border-radius: 4px; padding: 10px; display: inline-block;">
            <img src="{{ $offer->signature->signature_data }}" alt="Digital Signature" style="max-height: 80px; max-width: 250px; display: block;" />
        </div>
        <div>
            <div style="font-size: 13px; margin-bottom: 4px;"><strong>Signer:</strong> {{ $offer->candidate_name }}</div>
            <div style="font-size: 13px; margin-bottom: 4px;"><strong>Signed On:</strong> {{ $offer->signature->signed_at->format('d M Y, H:i:s') }}</div>
            <div style="font-size: 13px; margin-bottom: 4px;"><strong>IP Address:</strong> <code>{{ $offer->signature->ip_address }}</code></div>
            <div style="font-size: 12px; color: var(--fts-text-muted);"><strong>User Agent:</strong> {{ $offer->signature->user_agent }}</div>
        </div>
    </div>
</div>
@endif

{{-- Audit Trail Card --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Audit Trail &amp; Events</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                        <th>User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offer->events as $event)
                    <tr>
                        <td><strong>{{ $event->event_label }}</strong></td>
                        <td><code>{{ $event->ip_address ?? '—' }}</code></td>
                        <td>{{ $event->created_at->format('d M Y, H:i:s') }}</td>
                        <td style="font-size: 11px; color: var(--fts-text-muted); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $event->user_agent ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align: center; color: var(--fts-text-muted); padding: 20px;">No events recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyCandidateLink(btn) {
    const input = document.getElementById('candidate-link');
    if (!input) return;
    navigator.clipboard.writeText(input.value).then(() => {
        const orig = btn.textContent;
        btn.textContent = 'Copied!';
        btn.style.backgroundColor = 'var(--fts-success)';
        btn.style.borderColor = 'var(--fts-success)';
        setTimeout(() => {
            btn.textContent = orig;
            btn.style.backgroundColor = '';
            btn.style.borderColor = '';
        }, 2000);
    });
}
</script>
@endpush
@endsection
