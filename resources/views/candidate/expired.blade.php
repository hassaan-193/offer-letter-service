<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Offer Expired — FTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/candidate.css') }}" />
</head>
<body>
<div class="status-page">
    <div class="status-card status-warning">
        <div class="status-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <h1>Offer Letter Expired</h1>
        <p>This offer letter has expired as of <strong>{{ $offer->validity_date->format('d M Y') }}</strong>.</p>
        <p class="sub-msg">Please contact HR to request an extension or a new offer letter.</p>
        <div class="contact-info">
            <strong>FTS HR Department</strong><br/>
            Email: hr@fts-uae.com
        </div>
    </div>
</div>
</body>
</html>
