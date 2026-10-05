@extends('layouts.admin')
@section('title', 'Edit Offer Letter — ' . $offer->candidate_name)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Edit Offer Letter</h1>
        <p class="page-subtitle">Modifying offer details for <strong>{{ $offer->candidate_name }}</strong></p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <ol class="breadcrumb" style="margin: 0;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.offers.index') }}">Offer Letters</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.offers.show', $offer) }}">{{ $offer->candidate_name }}</a></li>
            <li class="breadcrumb-item active" style="color: var(--fts-maroon); font-weight: 600;">Edit</li>
        </ol>
        <a href="{{ route('admin.offers.preview', $offer) }}" target="_blank" class="btn btn-ghost btn-sm" style="margin-left: 10px;">
            Preview Letterhead
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.offers.update', $offer) }}" id="offer-form">
    @csrf
    @method('PUT')

    @include('admin.offers._form')

    <div class="card" style="border-top-color: var(--fts-maroon);">
        <div class="card-body" style="display: flex; justify-content: flex-end; gap: 12px; align-items: center;">
            <a href="{{ route('admin.offers.show', $offer) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" name="action" value="draft" class="btn btn-ghost" style="border-color: #ced4da;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Update Draft
            </button>
            @if($offer->status === 'draft')
                <button type="submit" name="action" value="publish" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Update &amp; Publish
                </button>
            @else
                <button type="submit" name="action" value="update" class="btn btn-primary">
                    Save Changes
                </button>
            @endif
        </div>
    </div>
</form>
@endsection
