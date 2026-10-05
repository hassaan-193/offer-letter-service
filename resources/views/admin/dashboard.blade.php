@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Overview of employment offer letters and signing activities</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <ol class="breadcrumb" style="margin: 0;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active" style="color: var(--fts-maroon); font-weight: 600;">Dashboard</li>
        </ol>
        <a href="{{ route('admin.offers.create') }}" class="btn btn-primary" style="margin-left: 15px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Create Offer Letter
        </a>
    </div>
</div>

{{-- Stat boxes matching modern_portal --}}
<div class="stats-grid">
    <div class="small-box bg-maroon">
        <div class="inner">
            <h3>{{ $stats['total'] }}</h3>
            <p>Total Offers</p>
        </div>
        <a href="{{ route('admin.offers.index') }}" class="small-box-footer">View All <i style="font-style: normal;">&rarr;</i></a>
    </div>

    <div class="small-box bg-secondary">
        <div class="inner">
            <h3>{{ $stats['drafts'] }}</h3>
            <p>Drafts</p>
        </div>
        <a href="{{ route('admin.offers.index', ['status' => 'draft']) }}" class="small-box-footer">Filter <i style="font-style: normal;">&rarr;</i></a>
    </div>

    <div class="small-box bg-success">
        <div class="inner">
            <h3>{{ $stats['signed'] }}</h3>
            <p>Accepted &amp; Signed</p>
        </div>
        <a href="{{ route('admin.offers.index', ['status' => 'signed']) }}" class="small-box-footer">View Signed <i style="font-style: normal;">&rarr;</i></a>
    </div>

    <div class="small-box bg-danger">
        <div class="inner">
            <h3>{{ $stats['expired'] + $stats['revoked'] }}</h3>
            <p>Expired / Revoked</p>
        </div>
        <a href="{{ route('admin.offers.index') }}" class="small-box-footer">Review <i style="font-style: normal;">&rarr;</i></a>
    </div>
</div>

{{-- Recent Offers Table --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: var(--fts-maroon);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Recent Offer Letters
        </h3>
        <a href="{{ route('admin.offers.index') }}" class="btn btn-ghost btn-sm">View All Records</a>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($recentOffers->isEmpty())
            <div style="text-align: center; padding: 40px 20px; color: var(--fts-text-muted);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px; margin-bottom: 12px; color: #ced4da;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <h4 style="font-size: 16px; font-weight: 600;">No offer letters found</h4>
                <p style="font-size: 13px; margin-top: 4px;">Click the button above to create the first offer letter.</p>
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
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOffers as $offer)
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
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 4px; align-items: center;">
                                    <a href="{{ route('admin.offers.show', $offer) }}" class="btn btn-ghost btn-sm">View</a>
                                    <a href="{{ route('admin.offers.preview', $offer) }}" target="_blank" class="btn btn-ghost btn-sm" title="Preview Letterhead">Preview</a>
                                    <form method="POST" action="{{ route('admin.offers.destroy', $offer) }}" style="display:inline;"
                                        onsubmit="return confirm('Are you sure you want to permanently delete the offer letter for {{ addslashes($offer->candidate_name) }}? This action cannot be undone.');">
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
        @endif
    </div>
</div>
@endsection
