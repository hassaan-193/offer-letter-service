@extends('layouts.admin')
@section('title', 'Offer Letters')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Offer Letters</h1>
        <p class="page-subtitle">{{ $offers->total() }} total employee offer letter records</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <ol class="breadcrumb" style="margin: 0;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active" style="color: var(--fts-maroon); font-weight: 600;">Offer Letters</li>
        </ol>
        <a href="{{ route('admin.offers.create') }}" class="btn btn-primary" style="margin-left: 15px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Create Offer
        </a>
    </div>
</div>

{{-- Filters Card --}}
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h3 class="card-title">Filter Records</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.offers.index') }}">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Search Keyword</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, passport, designation..." />
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Offer Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        @foreach(['draft','published','viewed','pending_signature','accepted','signed','expired','revoked'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" />
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" />
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Filter</button>
                    <a href="{{ route('admin.offers.index') }}" class="btn btn-ghost" style="flex: 1;">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table Card --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Offer Letters List</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($offers->isEmpty())
            <div style="text-align: center; padding: 40px 20px; color: var(--fts-text-muted);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px; margin-bottom: 12px; color: #ced4da;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <h4 style="font-size: 16px; font-weight: 600;">No records match your criteria</h4>
                <p style="font-size: 13px; margin-top: 4px;">Try resetting the filters or create a new offer letter.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Candidate Name</th>
                            <th>Passport No.</th>
                            <th>Designation</th>
                            <th>Offer Date</th>
                            <th>Valid Until</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($offers as $offer)
                        <tr>
                            <td>
                                <strong><a href="{{ route('admin.offers.show', $offer) }}" style="color: var(--fts-darker);">{{ $offer->candidate_name }}</a></strong>
                                <div style="font-size: 11px; color: var(--fts-text-muted);">{{ $offer->nationality }}</div>
                            </td>
                            <td><code>{{ $offer->passport_number }}</code></td>
                            <td>{{ $offer->designation }}</td>
                            <td>{{ $offer->offer_date->format('d/m/Y') }}</td>
                            <td>{{ $offer->validity_date->format('d/m/Y') }}</td>
                            <td><span class="badge {{ $offer->status_badge['class'] }}">{{ $offer->status_badge['label'] }}</span></td>
                            <td style="font-size: 12px; color: var(--fts-text-muted);">{{ $offer->created_at->format('d M Y') }}</td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 4px;">
                                    <a href="{{ route('admin.offers.show', $offer) }}" class="btn btn-ghost btn-sm">View</a>
                                    <a href="{{ route('admin.offers.preview', $offer) }}" target="_blank" class="btn btn-ghost btn-sm" title="Preview on Letterhead">Preview</a>
                                    @if($offer->status !== 'signed')
                                        <a href="{{ route('admin.offers.edit', $offer) }}" class="btn btn-ghost btn-sm">Edit</a>
                                    @endif
                                    @if(in_array($offer->status, ['published','viewed','pending_signature','accepted']))
                                        <button class="btn btn-ghost btn-sm" onclick="copyLink('{{ $offer->candidateLink() }}', this)" type="button">Copy Link</button>
                                    @endif
                                    <form method="POST" action="{{ route('admin.offers.destroy', $offer) }}" style="display:inline;"
                                        onsubmit="return confirm('Are you sure you want to permanently delete the offer letter for {{ addslashes($offer->candidate_name) }}? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--fts-danger);" title="Delete Offer">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding: 16px;">
                {{ $offers->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function copyLink(url, btn) {
    navigator.clipboard.writeText(url).then(() => {
        const orig = btn.textContent;
        btn.textContent = 'Copied!';
        btn.style.color = 'var(--fts-success)';
        setTimeout(() => { btn.textContent = orig; btn.style.color = ''; }, 2000);
    });
}
</script>
@endpush
@endsection
