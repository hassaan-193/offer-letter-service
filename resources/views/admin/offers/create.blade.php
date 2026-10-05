@extends('layouts.admin')
@section('title', 'Create Offer Letter')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Create Offer Letter</h1>
        <p class="page-subtitle">Draft a new FTS employment offer using the official letterhead format</p>
    </div>
    <ol class="breadcrumb" style="margin: 0;">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.offers.index') }}">Offer Letters</a></li>
        <li class="breadcrumb-item active" style="color: var(--fts-maroon); font-weight: 600;">Create</li>
    </ol>
</div>

<form method="POST" action="{{ route('admin.offers.store') }}" id="offer-form">
    @csrf

    @include('admin.offers._form')

    <div class="card" style="border-top-color: var(--fts-maroon);">
        <div class="card-body" style="display: flex; justify-content: flex-end; gap: 12px; align-items: center;">
            <a href="{{ route('admin.offers.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" name="action" value="draft" class="btn btn-ghost" style="border-color: #ced4da;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save as Draft
            </button>
            <button type="submit" name="action" value="publish" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Save &amp; Publish Offer
            </button>
        </div>
    </div>
</form>
@endsection
