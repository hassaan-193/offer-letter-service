# Offer Letter Service — Complete Technologies, Tools & Implementation Guide

This document is a comprehensive, deep-dive technical reference for the **Offer Letter Service**. It breaks down **every single tool, technology, library, and architectural pattern** used in the application, explaining:
1. **What is it?** (Definition & core concept)
2. **Why do we use it in this project?** (Business, legal & technical justification)
3. **How do we do it?** (Exact implementation, code snippets, file locations, and mechanisms)

---

## Table of Contents

1. [PHP 8.2+ (Core Runtime)](#1-php-82-core-runtime)
2. [Laravel 11.x (Web Framework)](#2-laravel-11x-web-framework)
3. [MySQL Database & Foreign Key Cascades](#3-mysql-database--foreign-key-cascades)
4. [Eloquent ORM & Active Record Pattern](#4-eloquent-orm--active-record-pattern)
5. [Barryvdh/Laravel-DomPDF (PDF Generation Engine)](#5-barryvdhlaravel-dompdf-pdf-generation-engine)
6. [DejaVu Sans Font (Arabic RTL & Unicode Script Engine)](#6-dejavu-sans-font-arabic-rtl--unicode-script-engine)
7. [SignaturePad.js & HTML5 Canvas (Digital Signature Engine)](#7-signaturepadjs--html5-canvas-digital-signature-engine)
8. [HTML5 IntersectionObserver API (Scroll & Reading Enforcement)](#8-html5-intersectionobserver-api-scroll--reading-enforcement)
9. [Vanilla CSS & AdminLTE 3 Layout System (Modern Portal Theme)](#9-vanilla-css--adminlte-3-layout-system-modern-portal-theme)
10. [PyMuPDF (fitz) & Python Imaging Library (Document Geometry Analysis)](#10-pymupdf-fitz--python-imaging-library-document-geometry-analysis)
11. [Cryptographic Tokenization & IDOR Security (Str::random)](#11-cryptographic-tokenization--idor-security-strrandom)
12. [Immutable Audit Logging & Event Tracking (AuditService)](#12-immutable-audit-logging--event-tracking-auditservice)
13. [PHPUnit & Feature Testing Suite](#13-phpunit--feature-testing-suite)
14. [Artisan CLI & Windows Socket Environment Passthrough](#14-artisan-cli--windows-socket-environment-passthrough)
15. [End-to-End Architectural Integration Map](#15-end-to-end-architectural-integration-map)

---

## 1. PHP 8.2+ (Core Runtime)

### What is it?
PHP 8.2 is a modern, strongly-typed, server-side scripting language. It introduces read-only classes, constructor property promotion, match expressions, union/intersection types, and performance enhancements through an improved JIT (Just-In-Time) compiler.

### Why do we use it in this project?
* **Type Safety**: Guarantees that currency numbers, dates, foreign keys, and cryptographic tokens are strictly validated before touching the database or PDF generation engine.
* **Constructor Promotion**: Dramatically reduces boilerplate in services and controllers (e.g. injecting `AuditService` and `OfferPdfService` cleanly).
* **High Performance**: Renders dynamic Blade templates and compiles 5-page PDF documents in milliseconds.

### How do we do it?
In `app/Http/Controllers/Admin/OfferLetterController.php`:
```php
public function __construct(
    protected AuditService $audit,
    protected OfferPdfService $pdfService
) {}
```
Here, constructor property promotion automatically assigns private/protected service dependencies without manual `$this->audit = $audit;` boilerplate.

---

## 2. Laravel 11.x (Web Framework)

### What is it?
Laravel is a modern PHP web application framework built on top of robust Symfony components. It provides an expressive, elegant syntax for routing, authentication, middleware, ORM database access, file storage, and automated testing.

### Why do we use it in this project?
* **Standalone Architecture**: Allows the Offer Letter Service to live completely isolated from `ft_portal_base`, having its own `.env`, routes, views, controllers, database, and sessions.
* **Security Middleware**: Easily restricts administrative routes behind session-based authentication guards while exposing public candidate signing endpoints without credential leaks.
* **Built-in Security**: Automatic protection against Cross-Site Request Forgery (CSRF), Cross-Site Scripting (XSS), SQL Injection (via PDO parameter binding), and Session Hijacking.

### How do we do it?
In `routes/web.php`:
```php
// Protected Admin Group
Route::middleware(\App\Http\Middleware\AdminAuthenticate::class)
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/offers', [OfferLetterController::class, 'index'])->name('offers.index');
        Route::delete('/offers/{offer}', [OfferLetterController::class, 'destroy'])->name('offers.destroy');
    });

// Public Unauthenticated Candidate Group
Route::prefix('offer')->name('candidate.offer.')->group(function () {
    Route::get('/{token}', [OfferViewController::class, 'show'])->name('show');
    Route::post('/{token}/sign', [OfferViewController::class, 'sign'])->name('sign');
});
```

---

## 3. MySQL Database & Foreign Key Cascades

### What is it?
MySQL is an open-source Relational Database Management System (RDBMS). It structures data into tables, columns, indexes, and maintains relational integrity using foreign keys.

### Why do we use it in this project?
* **Data Integrity**: Ensures every signature, generated document, and audit log strictly belongs to an existing offer letter.
* **Cascading Deletion (`ON DELETE CASCADE`)**: When an admin deletes an offer letter, MySQL automatically purges all related digital signatures, document metadata, and audit logs in a single atomic transaction. No orphan records are left in the database.
* **Fast Token Lookups**: The `token` column is indexed (`UNIQUE`), allowing the candidate signing page to resolve records in under 2 milliseconds.

### How do we do it?
In `database/migrations/2026_01_01_000003_create_signatures_table.php`:
```php
Schema::create('signatures', function (Blueprint $table) {
    $table->id();
    $table->foreignId('offer_letter_id')->constrained('offer_letters')->onDelete('cascade');
    $table->text('signature_data'); // Base64 PNG
    $table->string('ip_address')->nullable();
    $table->timestamp('signed_at');
    $table->timestamps();
});
```
Because of `->onDelete('cascade')`, executing `$offer->delete()` in Laravel immediately triggers the database engine to clean up child rows across `signatures`, `documents`, and `offer_events`.

---

## 4. Eloquent ORM & Active Record Pattern

### What is it?
Eloquent is Laravel's Object-Relational Mapper (ORM). Each database table has a corresponding "Model" class that represents records as PHP objects and encapsulates relationships, custom queries, accessors, and lifecycle events.

### Why do we use it in this project?
* Avoids raw SQL queries that are prone to syntax errors and security vulnerabilities.
* Provides readable model relationships like `$offer->signature`, `$offer->documents`, and `$offer->events`.
* Encapsulates status badge logic and candidate URLs directly into the model.

### How do we do it?
In `app/Models/OfferLetter.php`:
```php
public function signature(): HasOne
{
    return $this->hasOne(Signature::class);
}

public function candidateLink(): string
{
    return url("/offer/{$this->token}");
}

public function getStatusBadgeAttribute(): array
{
    return match($this->status) {
        'signed'    => ['label' => 'Signed',    'class' => 'badge-success'],
        'published' => ['label' => 'Published', 'class' => 'badge-info'],
        'draft'     => ['label' => 'Draft',     'class' => 'badge-secondary'],
        'revoked'   => ['label' => 'Revoked',   'class' => 'badge-danger'],
        default     => ['label' => ucfirst($this->status), 'class' => 'badge-warning'],
    };
}
```
In Blade views, `$offer->status_badge['class']` renders the appropriate CSS badge automatically.

---

## 5. Barryvdh/Laravel-DomPDF (PDF Generation Engine)

### What is it?
DomPDF is a PHP library that parses HTML and CSS to render standard Adobe PDF documents. `barryvdh/laravel-dompdf` integrates DomPDF into Laravel as a Service Provider and Facade.

### Why do we use it in this project?
* **Zero Node/Chromium Dependencies**: Does not require headless Chrome or Node.js running on the server, making it extremely lightweight and portable on any Windows or Linux host.
* **Exact Point-Based Geometry**: Supports `@page { size: 612pt 792pt; margin: 0; }` matching standard US Letter paper.
* **Letterhead Overlay**: Supports fixed CSS background positioning (`position: fixed; top: 0; left: 0; z-index: -1000;`) which repeats the official FTS letterhead across all 5 pages.

### How do we do it?
In `app/Services/OfferPdfService.php`:
```php
public function generateOriginal(OfferLetter $offer): Document
{
    $pdf = Pdf::loadView('pdf.offer-template', ['offer' => $offer]);
    $pdf->setPaper('letter', 'portrait');
    
    $path = "offers/{$offer->id}/documents/offer_original.pdf";
    Storage::disk('local')->put($path, $pdf->output());

    return Document::create([
        'offer_letter_id' => $offer->id,
        'type'            => 'original',
        'file_path'       => $path,
        'file_name'       => "FTS_Offer_{$offer->passport_number}.pdf",
    ]);
}
```

---

## 6. DejaVu Sans Font (Arabic RTL & Unicode Script Engine)

### What is it?
DejaVu Sans is an open-source TrueType font family with extensive Unicode coverage. It includes glyphs for Latin, Cyrillic, Greek, and complex Arabic script.

### Why do we use it in this project?
* Standard PDF fonts like Helvetica, Times-Roman, or Arial do **not** support Arabic Unicode characters in DomPDF; text turns into question marks (`???`) or disjointed letters.
* The official FTS Offer Letter is bilingual: every clause (01 to 09), the appointment statement, and the acceptance clause must appear in English and legal Arabic.
* DejaVu Sans allows DomPDF to render Arabic text from right to left (`direction: rtl; text-align: right;`) with proper ligature joining.

### How do we do it?
In `resources/views/pdf/offer-template.blade.php`:
```html
<style>
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 8.5pt;
    line-height: 1.35;
    color: #000000;
}
.ar {
    direction: rtl;
    text-align: right;
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 8pt;
    margin-top: 3pt;
    margin-bottom: 5pt;
}
</style>

<div class="ar bold">01 / مكان العمل</div>
<div class="ar mb-8">
    سيكون مقر عملك في {{ $offer->place_of_posting }} في الوقت الحالي...
</div>
```

---

## 7. SignaturePad.js & HTML5 Canvas (Digital Signature Engine)

### What is it?
`signature_pad` is an open-source JavaScript library developed by Szymon Nowak. It records smooth curve strokes on an HTML5 `<canvas>` element using Bezier curves and variable line widths based on stroke velocity.

### Why do we use it in this project?
* Eliminates the need for printing, scanning, or mailing physical paper contracts.
* Candidates can sign from any touch screen (iPhone, Android, iPad) or desktop mouse/trackpad.
* Exports clean, high-resolution PNG images with transparent backgrounds that embed directly into the final PDF.

### How do we do it?
In `resources/views/candidate/offer.blade.php`:
```javascript
// High-DPI Scaling for Crisp Strokes
function resizeCanvas(canvas) {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width  = canvas.offsetWidth  * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
    if (signaturePad) signaturePad.clear();
}

signaturePad = new SignaturePad(canvas, {
    backgroundColor: 'rgba(255,255,255,0)',
    penColor: '#000000',
    minWidth: 1.5,
    maxWidth: 3.5,
});

// Transmitting the Signature
const sigData = signaturePad.toDataURL('image/png');
const resp = await fetch(`/offer/${TOKEN}/sign`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
    body: JSON.stringify({ signature_data: sigData, acknowledged: true })
});
```

---

## 8. HTML5 IntersectionObserver API (Scroll & Reading Enforcement)

### What is it?
The IntersectionObserver API is a native browser feature that asynchronously observes changes in the intersection of a target element with an ancestor element or the top-level document's viewport.

### Why do we use it in this project?
* **Legal Compliance**: Employment contracts cannot be accepted blindly without confirming the candidate has seen all terms, especially penalty clauses such as AED 5,000 / AED 2,500 training reimbursements and AED 10,000 document discrepancy fines.
* **Automated Workflow Unlocking**: Prevents candidates from checking the acceptance box until they scroll past Page 5 to the end marker.

### How do we do it?
In `resources/views/candidate/offer.blade.php`:
```javascript
const endMarker = document.getElementById('document-end');

const endObserver = new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting && !readingDone) {
        readingDone = true;
        
        // Update reading indicator to 100%
        progressBar.style.width = '100%';
        progressLabel.textContent = '100% Read — Ready to Sign';
        
        // Unlock acknowledgment checkbox
        document.getElementById('ack-checkbox').disabled = false;
        document.getElementById('ack-check-label').classList.remove('disabled');
        document.getElementById('reading-reminder')?.remove();

        // Notify server in background
        fetch(`/offer/${TOKEN}/reading-complete`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
        });
    }
}, { threshold: 0.8 });

endObserver.observe(endMarker);
```

---

## 9. Vanilla CSS & AdminLTE 3 Layout System (Modern Portal Theme)

### What is it?
Vanilla CSS is standard, uncompiled Cascading Style Sheets. AdminLTE 3 is an established enterprise dashboard layout paradigm built with clean semantic classes (`layout-top-nav`, `small-box`, `card`, `card-header`).

### Why do we use it in this project?
* **Zero Build Step**: No `npm run build` or Webpack/Vite compiling required for CSS. Any changes take effect immediately on reload.
* **Exact Match to `modern_portal`**: Utilizes the identical color tokens, top navbar header, Poppins font, and card outlines found in `ft_portal_base(2)/modern_portal`.
* **Paper Sheet Viewer**: Recreates the realistic physical appearance of five 8.5" x 11" paper sheets on a desk (`#525659`), providing candidates with an authentic document experience.

### How do we do it?
In `public/css/admin.css` and `public/css/candidate.css`:
```css
:root {
    --fts-maroon:        #d81b60;
    --fts-maroon-hover:  #b5134e;
    --fts-maroon-light:  rgba(216, 27, 96, 0.08);
    --fts-dark:          #343a40;
    --fts-gray-bg:       #525659;
    --font:              'Poppins', system-ui, sans-serif;
}

.offer-page-sheet {
    width: 816px; /* 8.5in * 96 DPI */
    min-height: 1056px; /* 11in * 96 DPI */
    background-color: #ffffff;
    background-image: url('../images/fts_letterhead.jpg');
    background-repeat: no-repeat;
    background-position: top center;
    background-size: 816px 1056px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.35);
    position: relative;
    padding: 195px 70px 45px 70px; /* 195px clears top letterhead */
    font-family: Arial, 'DejaVu Sans', sans-serif;
    font-size: 11.5px;
    line-height: 1.38;
}
```

---

## 10. PyMuPDF (fitz) & Python Imaging Library (Document Geometry Analysis)

### What is it?
PyMuPDF (`fitz`) is a high-performance Python binding for MuPDF (a lightweight PDF, XPS, and eBook viewer). PIL (Pillow) is Python’s standard image processing library.

### Why do we use it in this project?
During initial development, the web preview had text overlapping the flame logo and phone numbers. Instead of guessing margins, we wrote Python scripts using `fitz` and `PIL` to inspect `OFFER Letter-2026-26 - Priyadarsan Perinjanam - DRAFT.pdf` and measure the exact bounding boxes down to the point/pixel.

### How do we do it?
Python script used to extract block positions:
```python
import fitz
doc = fitz.open('OFFER Letter-2026-26 - Priyadarsan Perinjanam - DRAFT.pdf')
p1 = doc[0]
for b in p1.get_text('blocks'):
    print(f"y0={b[1]:.1f}, text={b[4][:40]}")
```
**Findings & Solution**:
* The letterhead contact details end at `y = 142pt` (`190px`).
* Setting CSS `padding-top: 195px` (web) and `padding: 142pt` (PDF) positions `Rev-06-23`, `Date:`, and `To: Mr./Ms. [NAME]` cleanly below the letterhead graphics with zero collision.

---

## 11. Cryptographic Tokenization & IDOR Security (Str::random)

### What is it?
A secure pseudo-random 64-character alphanumeric string generated via PHP's cryptographically secure pseudo-random number generator (CSPRNG).

### Why do we use it in this project?
* **Prevents IDOR (Insecure Direct Object Reference)**: If signing URLs used incremental database IDs (e.g. `/offer/1`, `/offer/2`), any user could view or sign other candidates' private employment letters.
* **No Login Required for Candidate**: Candidates receive a secure, private link without needing to register a user account.
* **High Entropy**: A 64-character base-62 token has $62^{64}$ possible combinations, making brute-force discovery mathematically impossible.

### How do we do it?
In `app/Http/Controllers/Admin/OfferLetterController.php`:
```php
$offer->token = Str::random(64);
$offer->save();

// Candidate access URL:
// https://domain.com/offer/Gr72CAuyOmQe1ODuOIfo8j5wcnXu21DeXyc3d5DHcA56Prn3Hw02El4kw0LDOOre
```

---

## 12. Immutable Audit Logging & Event Tracking (AuditService)

### What is it?
An audit trail is an append-only, tamper-evident log capturing who performed an action, what action was performed, when it occurred, from which IP address, and with what device signature.

### Why do we use it in this project?
* **Legal Non-Repudiation**: If a candidate disputes having agreed to employment terms, the audit log provides proof of IP address, timestamp, browser user-agent, and reading confirmation.
* **Administrative Oversight**: Tracks which HR admin created, edited, published, revoked, or downloaded offer letters.

### How do we do it?
In `app/Services/AuditService.php`:
```php
public function log(
    OfferLetter $offer,
    string $eventType,
    ?string $ip = null,
    ?string $userAgent = null,
    array $metadata = []
): OfferEvent {
    return OfferEvent::create([
        'offer_letter_id' => $offer->id,
        'event_type'      => $eventType,
        'ip_address'      => $ip ?? request()->ip(),
        'user_agent'      => $userAgent ?? request()->userAgent(),
        'metadata'        => $metadata,
    ]);
}
```
Viewable directly on the Offer Details page under **"Audit Trail & Compliance History"**.

---

## 13. PHPUnit & Feature Testing Suite

### What is it?
PHPUnit is the industry-standard automated testing framework for PHP applications. Laravel integrates PHPUnit with convenient testing assertions (`assertStatus`, `assertRedirect`, `assertDatabaseHas`, `assertSee`).

### Why do we use it in this project?
* Verifies that code modifications do not introduce regressions.
* Confirms security protections (e.g. ensuring guest users are redirected to `/login`, and candidates cannot sign revoked offers).
* Validates PDF compilation and file downloads automatically.

### How do we do it?
In `tests/Feature/SecurityWorkflowTest.php`:
```php
public function test_guest_cannot_access_any_admin_endpoint(): void
{
    $offer = $this->createSampleOffer();
    $routes = [
        ['GET',    route('admin.dashboard')],
        ['GET',    route('admin.offers.index')],
        ['DELETE', route('admin.offers.destroy', $offer)],
    ];

    foreach ($routes as [$method, $url]) {
        $response = $this->call($method, $url);
        $response->assertRedirect('/login');
    }
}
```
**Test Results**: All **33 automated tests** (112 assertions) pass with 100% green status.

---

## 14. Artisan CLI & Windows Socket Environment Passthrough

### What is it?
Artisan is Laravel’s command-line interface. It powers migrations, database seeding, tinker REPL, and local development serving (`php artisan serve`).

### Why do we use it in this project?
On Windows operating systems, PHP's built-in web server subprocess can fail with `Failed to listen on 127.0.0.1:8001 (reason: ?)` if required Windows environment variables are not passed through to the child process.

### How do we do it?
In `app/Providers/AppServiceProvider.php`:
```php
public function boot(): void
{
    if (class_exists(\Illuminate\Foundation\Console\ServeCommand::class)) {
        \Illuminate\Foundation\Console\ServeCommand::$passthroughVariables = array_merge(
            \Illuminate\Foundation\Console\ServeCommand::$passthroughVariables,
            ['SystemRoot', 'WINDIR', 'SYSTEMDRIVE', 'TEMP', 'TMP', 'PATH', 'PATHEXT']
        );
    }
}
```
This fix ensures that `php artisan serve --port=8001` runs on Windows systems without socket initialization crashes.

---

## 15. Blade Templating Engine (Server-Side Dynamic Rendering)

### What is it?
Blade is Laravel's lightweight, powerful templating engine. Unlike other PHP templating engines, Blade does not restrict you from using plain PHP code in your templates. All Blade views are compiled into plain PHP code and cached until they are modified.

### Why do we use it in this project?
* **Zero Overhead**: Incurs essentially zero overhead to your application since it compiles to native PHP opcode.
* **Component-Based Layouts**: Allows shared layout structures (`layouts/admin.blade.php`, `layouts/candidate.blade.php`) with reusable sections (`@yield('content')`, `@push('scripts')`).
* **Format-Specific Views**: Uses dedicated Blade views for both web preview rendering (`admin/offers/preview.blade.php`) and DomPDF compilation (`pdf/offer-template.blade.php` and `pdf/offer-template-signed.blade.php`), keeping presentation logic clean and separated.

### How do we do it?
In `resources/views/pdf/offer-template-signed.blade.php`:
```blade
{{-- Conditional rendering of the Candidate's Digital Signature Seal on Page 5 --}}
@if($offer->signature)
    <div style="margin-top: 15pt; border: 1px solid #10b981; background: #ecfdf5; padding: 10pt; border-radius: 4pt;">
        <span style="font-weight: bold; color: #047857;">DIGITALLY ACCEPTED AND SIGNED</span>
        <div style="margin-top: 6pt;">
            <img src="{{ $offer->signature->signature_data }}" style="height: 40pt; max-width: 150pt;" />
        </div>
        <div style="font-size: 7.5pt; color: #065f46; margin-top: 4pt;">
            IP: {{ $offer->signature->ip_address }} | Date: {{ $offer->signature->signed_at->format('d M Y, h:i A') }}
        </div>
    </div>
@endif
```

---

## 16. Step-by-Step Operational Lifecycle (How It All Works Together)

```
 [1. Admin Portal] ──► [2. PDF Engine] ──► [3. Candidate Link] ──► [4. Observer & Canvas] ──► [5. Seal & Archive]
   Author offer         Generate preview       Token URL sent        Candidate reads 100%      Final PDF saved
   with salary rules     with letterhead       without login         & signs digitally         with audit trail
```

### Step 1: Offer Creation & Drafting
1. Admin logs into the portal at `http://127.0.0.1:8001/admin`.
2. Admin enters candidate information (Full Name, Passport Number, Nationality, Designation, Place of Posting, Basic Salary, Allowances, Joining Date, Expiry Date, and optional Custom Content / Additional Terms).
3. Admin clicks **"Save as Draft"** or **"Publish & Generate Link"**.
4. The controller automatically calculates total remuneration (`basic_salary + allowances`) and saves the record in MySQL.

### Step 2: Letterhead Alignment & Clearance
1. The application overlays the official FTS letterhead background (`fts_letterhead.jpg`).
2. CSS `padding-top: 195px` (for browser view) and DomPDF margin `142pt` ensure the document content starts exactly below the Abu Dhabi and Ras Al Khaimah contact info.
3. Content is organized across **5 standard pages**:
   - **Page 1**: Candidate Intro, Designation, Remuneration Table, Clauses 01 (Work Place), 02 (Contract Period), 03 (Probation).
   - **Page 2**: Clauses 04 (Leave), 05 (Air Ticket), 06 (Working Hours & Overtime), 07 (Medical/Insurance).
   - **Page 3**: Clause 08 (Responsibilities & Disciplinary Regulations: AED 5,000 / 2,500 training reimbursement, AED 10,000 fine for document discrepancies).
   - **Page 4**: Clause 09 (End of Service Gratuity & UAE Labour Law governance).
   - **Page 5**: Clause 08 (Joining Date), Clause 09 (Cancellation of Contract), Optional Clause 10 (Custom Content / Additional Terms & Special Conditions), Final Acceptance Statement, Admin Signature & Seal, and Candidate Signature Block.

### Step 3: Candidate Verification & Reading Lock
1. Candidate opens their personalized URL: `/offer/{64-character-token}`.
2. The page renders in a high-fidelity 5-sheet paper preview.
3. The acknowledgment checkbox and "Accept & Sign" button are **disabled** by default.
4. An `IntersectionObserver` monitors `#document-end` on Page 5. Only when the candidate scrolls through all 5 pages does the reading progress reach 100% and unlock the signature canvas.

### Step 4: Digital Signature Capture
1. Candidate uses their finger or mouse to draw their signature on the `SignaturePad.js` canvas.
2. Candidate checks the legally binding acknowledgment checkbox.
3. Candidate clicks **"Accept & Sign Offer Letter"**.
4. JavaScript captures the canvas as a base64-encoded PNG image and posts it via AJAX to `/offer/{token}/sign`.

### Step 5: PDF Recompilation & Archival
1. The server stores the signature record with IP address, user-agent, and timestamp.
2. `OfferPdfService` re-renders the document using `offer-template-signed.blade.php`, permanently stamping the candidate's vector signature and digital verification seal onto Page 5.
3. The compiled PDF is stored in `storage/app/offers/{id}/documents/offer_signed.pdf`.
4. Offer status transitions from `published` to `signed`.
5. An immutable audit record is logged in `offer_events`.

### Step 6: Safe Deletion & Cascade
1. If the HR Admin clicks **"Delete"** from the dashboard or offers index:
2. A JavaScript modal confirms the action.
3. `OfferLetterController::destroy()` deletes all files in `storage/app/offers/{id}`.
4. Calling `$offer->delete()` triggers MySQL `ON DELETE CASCADE`, automatically removing all linked rows in `signatures`, `documents`, and `offer_events`.

---

## 17. Project File Directory & Key Responsibilities

| Path / File | Type | Purpose & Responsibility |
|---|---|---|
| `app/Http/Controllers/Admin/OfferLetterController.php` | Controller | CRUD operations, offer publishing, revocation, and cascading deletion. |
| `app/Http/Controllers/Candidate/OfferViewController.php` | Controller | Candidate viewing, reading progress tracking, and signature ingestion. |
| `app/Services/OfferPdfService.php` | Service | DomPDF generation for original and signed 5-page employment contracts. |
| `app/Services/AuditService.php` | Service | Immutable logging of IP addresses, user agents, and lifecycle events. |
| `app/Models/OfferLetter.php` | Eloquent Model | Core entity model with relationships, scopes, and status badges. |
| `app/Models/Signature.php` | Eloquent Model | Digital signature record storing base64 PNG data, IP, and timestamp. |
| `app/Models/Document.php` | Eloquent Model | File storage metadata for compiled original and signed PDFs. |
| `app/Models/OfferEvent.php` | Eloquent Model | Audit trail history records. |
| `resources/views/pdf/offer-template.blade.php` | Blade View | 5-page bilingual PDF template matching the FTS draft format. |
| `resources/views/pdf/offer-template-signed.blade.php` | Blade View | 5-page signed PDF template containing the candidate's digital signature seal. |
| `resources/views/admin/offers/preview.blade.php` | Blade View | High-fidelity 5-sheet paper preview for administrative review. |
| `resources/views/candidate/offer.blade.php` | Blade View | Interactive candidate signing page with reading tracker and canvas. |
| `public/css/admin.css` | CSS | Modern Portal admin theme (FTS Maroon `#d81b60`, dark top nav, clean 4-box stats). |
| `public/css/candidate.css` | CSS | Realistic paper-sheet desk viewer (`#525659`) with 195px letterhead clearance. |
| `app/Providers/AppServiceProvider.php` | Provider | Fixes Windows PHP built-in web server socket startup crashes via environment variable passthrough. |
| `tests/Feature/SecurityWorkflowTest.php` | PHPUnit Test | 33 comprehensive automated tests covering security, workflow, and deletion cascades. |

---

## 18. End-to-End Architectural Integration Map

```
┌────────────────────────────────────────────────────────────────────────┐
│                          ADMINISTRATIVE USER                           │
│  - Modern Portal Theme: FTS Maroon (#d81b60), Poppins, Dark Top-Nav    │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                         [Authentication Guard]
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                        OfferLetterController.php                       │
│  - Author 5-Page Contract with dynamic salary calculation              │
│  - Draft Mode vs Instant Publish Mode                                  │
│  - Secure Deletion with ON DELETE CASCADE & storage cleanup            │
└─────────────┬────────────────────────────────────────────┬─────────────┘
              │                                            │
              ▼                                            ▼
┌───────────────────────────┐                ┌───────────────────────────┐
│     OfferPdfService       │                │       AuditService        │
│  - Compiles DomPDF views  │                │  - Logs IP, Agent & Time  │
│  - Embeds DejaVu Sans font│                │  - Immutable Event Stream │
│  - Renders A4/Letter pages│                └───────────────────────────┘
└─────────────┬─────────────┘
              │
              ▼
┌────────────────────────────────────────────────────────────────────────┐
│                            CANDIDATE PORTAL                            │
│  - Public token URL: /offer/{64-char-token}                           │
│  - 5-Sheet Document Desk with fts_letterhead.jpg Background            │
│  - IntersectionObserver tracks reading progress to Page 5              │
│  - SignaturePad.js captures vector signature from Canvas               │
│  - Recompiles final PDF with Digital Verification Seal                 │
└────────────────────────────────────────────────────────────────────────┘
```

