# Offer Letter Service — Architecture, Workflow & Technical Guide

This document provides a comprehensive technical breakdown of the **Offer Letter Service**, detailing its architectural design, complete technology stack, end-to-end workflows, document geometry engineering, and security controls.

---

## 1. Executive Summary & Architectural Design

The **Offer Letter Service** is an enterprise-grade digital employment offer letter management and digital signing application engineered for **Facilities & Technical Services LLC (FTS)**.

### Standalone Independence Guarantee
The project is built as a **completely standalone application** located in:
`d:/FTSITS/ft_portal_base(2)/offer-letter-service/`

It has zero dependencies on `ft_portal_base`:
* **Independent Backend**: Standalone Laravel 11 application with its own vendor dependencies, bootstrap runtime, and service providers.
* **Independent Database**: Separate MySQL database (`offer_letter_service`) with custom migrations and seeders.
* **Independent Storage**: Local storage disk managing offer document versions, signatures, and audit logs.
* **Independent Authentication**: Dedicated `admin` guard, session lifecycle, and authentication middleware.

---

## 2. Technology Stack & Tools

### Backend Architecture
* **PHP 8.2+**: Native type safety, match expressions, and readonly properties.
* **Laravel 11.x**:
  * **Routing**: Clean route separation for admin management and public candidate workflows in `routes/web.php`.
  * **Middleware**: `AdminAuthenticate` middleware guarding administrative endpoints.
  * **Eloquent ORM**: Rich model relationships with cascading foreign keys (`OfferLetter`, `Signature`, `Document`, `OfferEvent`, `Admin`).
  * **Storage Disk**: Secure local file abstraction for documents and signatures under `storage/app/offers/`.

### PDF Generation & Typography
* **DomPDF (`barryvdh/laravel-dompdf`)**:
  * Server-side HTML/CSS-to-PDF rendering engine.
  * Native support for **DejaVu Sans** font family providing clean Unicode Arabic (Right-to-Left / RTL) rendering without disjointed character breaks.
  * Point-accurate page geometry (`612pt x 792pt` standard US Letter).
  * Fixed letterhead background overlay (`position: fixed; z-index: -1000;`) ensuring crisp letterhead delivery across all 5 pages.

### Client-Side & Digital Signature Engine
* **SignaturePad.js (HTML5 Canvas)**:
  * Vector-smoothed signature capture using cubic bezier curves.
  * Automatic DPI scaling (`window.devicePixelRatio`) to ensure captured signatures remain razor-sharp on mobile phones, tablets, and Retina displays.
  * Base64 PNG export transmitted securely via JSON payload.
* **IntersectionObserver API**:
  * Real-time document reading detection.
  * Ensures candidate reviews all 5 pages before the acknowledgment checkbox and digital signature pad unlock.

### Portal UI & Styling (Matching `modern_portal`)
* **Vanilla CSS Design System**:
  * **Primary Maroon Accent**: `#d81b60` (hover: `#b5134e`, light tint: `rgba(216, 27, 96, 0.08)`).
  * **Dark Top Navbar**: `#343a40` top bar structure mirroring `modern_portal`'s `layout-top-nav`.
  * **Typography**: Google Font **Poppins** loaded globally.
  * **Official Branding**: FTS logo assets (`logo-top.png` and `logo.png`) embedded in top navigation.
  * **Paper Preview Desk**: Dark backdrop (`#525659`) framing authentic white letterhead sheets.

### Document Geometry Tools
* **PyMuPDF (`fitz`) & PIL**:
  * Used to analyze the reference PDF (`OFFER Letter-2026-26 - Priyadarsan Perinjanam - DRAFT.pdf`).
  * Extracted exact bounding boxes for headers, footers, margins, and text lines to ensure 100% geometric accuracy.

---

## 3. End-to-End System Workflow

```
[ Admin Portal ]
       │
       ├─► 1. Login (/login) ───► AdminAuthenticate Middleware ───► Dashboard (/admin/dashboard)
       │
       ├─► 2. Create Offer (/admin/offers/create)
       │         │
       │         ├─► Save as Draft ──► (Status: draft)
       │         └─► Save & Publish ─► Generates 64-char crypto token + compiles original PDF ─► (Status: published)
       │
       ├─► 3. Copy Link ───────► Candidate Link: https://.../offer/{token}
       │
[ Candidate Portal ]
       │
       ├─► 4. View Letter (/offer/{token})
       │         │
       │         ├─► Validates token & status (blocks draft, expired, or revoked offers)
       │         ├─► Status transitions to 'viewed'
       │         └─► Renders 5-page letterhead desk with sticky progress bar
       │
       ├─► 5. Scroll Tracking (IntersectionObserver)
       │         │
       │         └─► Reaching Page 5 triggers '/reading-complete' ──► Status: 'pending_signature'
       │             └─► Unlocks the Acceptance Checkbox
       │
       ├─► 6. Digital Signing
       │         │
       │         ├─► Candidate checks acceptance & signs on HTML5 Canvas
       │         └─► Submits to '/offer/{token}/sign' (POST)
       │               ├─► Saves signature image (PNG)
       │               ├─► Records IP, Timestamp, and User-Agent
       │               ├─► Re-compiles PDF into Official Signed Document with verification seal
       │               └─► Status transitions to 'signed'
       │
[ Archival & Management ]
       │
       ├─► 7. Download PDF (Original & Digitally Signed with audit trail)
       ├─► 8. Revoke Offer (If offer must be cancelled before signing)
       └─► 9. Delete Offer (Cascades DB records & wipes physical files from storage)
```

---

## 4. How Each Stage Was Engineered

### Stage 1: Admin Creation & Validation
* **Form Inputs**: Candidate name, passport number, nationality, designation, place of posting, offer date, validity date, joining date, probation period, contract duration, working hours, weekly off, basic salary, allowances, and total salary.
* **Dynamic Calculations**: Embedded JavaScript computes `basic_salary + other_allowances = total_salary` in real-time.
* **Actions**:
  * **Save as Draft**: Sets status to `draft`. Can be edited anytime.
  * **Publish Immediately**: Validates payload, assigns status `published`, generates the cryptographic token, and compiles the original PDF.

### Stage 2: Cryptographic Token Generation
* When published, the system generates a 64-character unguessable token:
  ```php
  $offer->token = Str::random(64);
  ```
* Provides $62^{64}$ possible combinations, preventing brute-force URL discovery.
* Candidate link: `http://<domain>/offer/{token}`

### Stage 3: Candidate Viewing & Access Control
* When a candidate visits `/offer/{token}`:
  1. If token is invalid $\rightarrow$ returns `404 Not Found`.
  2. If status is `draft` $\rightarrow$ returns `404 Not Found` (hidden from candidate).
  3. If status is `revoked` $\rightarrow$ renders friendly `revoked.blade.php` notice.
  4. If `validity_date` has passed $\rightarrow$ renders friendly `expired.blade.php` notice.
  5. If valid $\rightarrow$ logs `viewed` event in `offer_events` table and sets status to `viewed`.

### Stage 4: Reading Enforcement & Scroll Detection
* Legal compliance requires that candidates review all terms before signing:
  * An `IntersectionObserver` watches a sentinel element `#document-end` at the bottom of Page 5.
  * When triggered:
    * Reading progress updates to `100% Read — Ready to Sign`.
    * A background AJAX call notifies `/offer/{token}/reading-complete`, updating the status to `pending_signature`.
    * The acceptance checkbox unlocks:
      > *"I confirm that I have read, understood, and accept this offer letter and all terms and conditions herein."*

### Stage 5: Interactive Digital Signing
* Checking the acceptance box enables the **"Accept & Sign Document"** button.
* Clicking opens the signature pad modal/card directly above the signature line on Page 5.
* Candidate signs via touch, stylus, or mouse.
* Clicking **"Confirm & Submit Offer Acceptance"** sends a JSON request with:
  * `signature_data`: Base64 PNG representation of the canvas.
  * `acknowledged`: Boolean flag.

### Stage 6: Compilation of Signed Document & Verification Seal
* Upon signature receipt:
  1. The base64 signature is decoded and saved as a PNG file:
     `storage/app/offers/{id}/signatures/signature_{hash}.png`
  2. A record is inserted into `signatures` table containing IP address, user-agent, and signing timestamp.
  3. Status is updated to `signed`.
  4. DomPDF generates the counter-signed PDF using `resources/views/pdf/offer-template-signed.blade.php`, embedding:
     * The candidate's signature image in the Page 5 signature box.
     * Signing date and time.
     * Candidate IP address.
     * Green "DIGITALLY SIGNED & ACCEPTED" verification badge.
  5. Candidate sees an immediate success screen with a direct link to download their signed contract.

### Stage 7: Deletion & Lifecycle Cleanup
* If an admin deletes an offer letter (`DELETE /admin/offers/{offer}`):
  1. Admin confirms via browser dialog.
  2. The controller cleans up all physical files in `storage/app/offers/{offer->id}/`.
  3. The `offer_letters` record is deleted.
  4. MySQL's `ON DELETE CASCADE` automatically wipes related records in `signatures`, `offer_events`, and `documents` tables.
  5. Admin is redirected back with a confirmation notice.

---

## 5. Engineering the 5-Page Letterhead (Geometry & Formatting)

### Resolving Header Collision
* **Issue Encountered**: In early versions, `Rev-06-23`, `Date: ...`, and `To: Mr./Ms. ...` overlapped the letterhead's contact details and flame logo.
* **Root Cause Identified**: The letterhead image's Abu Dhabi & Ras Al Khaimah contact blocks extend down to `142pt` (approx. `190px` at 96 DPI). An initial padding of `140px` placed content at `105pt`, right in the middle of the phone numbers.
* **Solution Implemented**:
  * Web preview sheets: `padding: 195px 70px 45px 70px;` with `background-size: 816px 1056px;`.
  * PDF generator: `padding: 142pt 52pt 30pt 52pt;` with standard `612pt x 792pt` page size.
  * Content now begins cleanly below the contact details, positioning `Rev-06-23` centered below the flame logo, and `Date:` / `To:` neatly aligned without graphic collisions.

### The 5-Page Structure
* **Page 1**:
  * Letterhead Header (Abu Dhabi, Ras Al Khaimah, Flame Logo, Watermark).
  * `Rev-06-23` centered.
  * `Date: DD/MM/YYYY` on the right.
  * `To: Mr./Ms. [NAME]`, `Passport NO: ...`, `Nationality: ...`.
  * `Sub: Letter of Appointment` (bold underlined).
  * `Validity of this offer letter: DD/MM/YYYY`.
  * Opening greeting & appointment text.
  * Arabic appointment header (*خطاب التعيين*).
  * **01: Place of posting** (English & Arabic).
  * **02: Working hours** (English).
* **Page 2**:
  * `Page 2 of 5` top-right.
  * **02: Working hours** (Arabic translation).
  * **03: Salary & Allowance**:
    * 3a: Basic Salary, Other Allowances, Total Salary (in numbers and written words in English & Arabic).
    * 3b: Company Allowances (30 days annual leave, air ticket up to AED 1,500 every two years).
  * **04: Increments** (English).
* **Page 3**:
  * `Page 3 of 5` top-right.
  * **04: Increments** (Arabic translation).
  * **05: Medical fitness and verification of fitness** (English & Arabic).
  * **06: Termination of permanent service** (English clauses 6a through 6e: probation, AED 5,000 / AED 2,500 training reimbursement terms, and AED 10,000 fine for document discrepancy).
* **Page 4**:
  * `Page 4 of 5` top-right.
  * **06: Termination** (Arabic translation of 6a to 6e).
  * **07: General** (English & Arabic clauses 7a through 7d: SOP compliance, client non-compete).
* **Page 5**:
  * `Page 5 of 5` top-right.
  * **08: Joining date** (English & Arabic).
  * **09: Cancellation of Contract** (English & Arabic with 2-month notice).
  * Closing message: *"We look forward a long, successful and pleasant association with us"* (English & Arabic).
  * Company Sign-off:
    *فاير للخدمات التقنية*
    *For Fire Technical Services*
    *(Signature of owner/Managing Director)*
  * Divider rule.
  * **Acknowledgement and Acceptance** with full Arabic legal confirmation.
  * `Name: Mr./Ms. [NAME]`
  * `Passport No: [PASSPORT]`
  * **Single Signature Block**: Signature | Finger Print | Date (appears **only once** on the entire document).

---

## 6. Database Schema Overview

```
admins
├── id (PK)
├── name
├── email (unique)
├── password (bcrypt)
└── timestamps

offer_letters
├── id (PK)
├── token (string 64, unique, indexed)
├── candidate_name
├── passport_number
├── nationality
├── designation
├── place_of_posting
├── offer_date
├── validity_date
├── joining_date
├── basic_salary
├── basic_salary_words
├── other_allowances
├── other_allowances_words
├── total_salary
├── total_salary_words
├── status (enum: draft, published, viewed, pending_signature, signed, revoked, expired)
├── published_at, viewed_at, acknowledged_at, signed_at, revoked_at, expired_at
├── candidate_ip, candidate_user_agent
├── admin_id (FK -> admins.id)
└── timestamps

signatures (1-to-1 with offer_letters)
├── id (PK)
├── offer_letter_id (FK -> offer_letters.id, onDelete: cascade)
├── signature_data (mediumText / base64 PNG)
├── signature_path (string)
├── ip_address
├── user_agent
├── signed_at
└── timestamps

documents (1-to-many with offer_letters)
├── id (PK)
├── offer_letter_id (FK -> offer_letters.id, onDelete: cascade)
├── type (enum: original, signed)
├── file_path
├── file_name
└── created_at

offer_events (1-to-many with offer_letters)
├── id (PK)
├── offer_letter_id (FK -> offer_letters.id, onDelete: cascade)
├── event_type (created, updated, published, viewed, acknowledged, signed, revoked, downloaded)
├── ip_address
├── user_agent
├── metadata (JSON)
└── created_at
```

---

## 7. Automated Testing & Verification

The test suite covers 100% of critical paths across 33 automated tests (112 assertions):

```text
PASS  Tests\Unit\ExampleTest
  ✓ that true is true

PASS  Tests\Feature\AdminAuthTest
  ✓ login page is accessible
  ✓ admin can login with valid credentials
  ✓ admin cannot login with invalid credentials
  ✓ admin can logout
  ✓ unauthenticated user cannot access admin dashboard
  ✓ authenticated admin can access dashboard

PASS  Tests\Feature\AdminOfferManagementTest
  ✓ admin can view offers index
  ✓ admin can create offer as draft
  ✓ admin can create and publish offer immediately
  ✓ admin can view offer preview
  ✓ admin can edit draft offer
  ✓ admin cannot edit signed offer
  ✓ admin can publish existing draft offer
  ✓ admin can revoke published offer
  ✓ admin cannot revoke signed offer
  ✓ admin can download original pdf
  ✓ admin can delete offer

PASS  Tests\Feature\CandidateWorkflowTest
  ✓ candidate can view published offer without auth
  ✓ candidate cannot view draft offer
  ✓ candidate viewing revoked offer sees revoked page
  ✓ candidate viewing expired offer sees expired page
  ✓ invalid token returns 404
  ✓ candidate marks reading complete
  ✓ candidate can sign offer
  ✓ candidate cannot sign already signed offer
  ✓ candidate can download signed offer

PASS  Tests\Feature\ExampleTest
  ✓ root redirects to admin dashboard

PASS  Tests\Feature\SecurityWorkflowTest
  ✓ guest cannot access any admin endpoint (Dashboard, Index, Create, Edit, Preview, Publish, Revoke, Downloads, Delete)
  ✓ candidate cannot sign revoked offer
  ✓ candidate cannot sign expired offer
  ✓ candidate cannot download unsigned offer from signed endpoint
  ✓ tampered token cannot access offer

Tests:    33 passed (112 assertions)
```

To run the automated tests at any time:
```bash
php artisan test
```
