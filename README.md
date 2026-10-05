# Offer Letter Service

A completely standalone, modern web application and digital signing system built for **Facilities & Technical Services LLC (FTS)** to create, preview, publish, track, acknowledge, digitally sign, store, and download employee offer letters.

> 📖 **Comprehensive System Guide**: See [SYSTEM_DOCUMENTATION.md](file:///d:/FTSITS/ft_portal_base(2)/offer-letter-service/SYSTEM_DOCUMENTATION.md) for the complete architecture breakdown, end-to-end workflow diagrams, letterhead geometry engineering, database schema, and test suite details.

---

## 1. Project Overview

**Offer Letter Service** provides an end-to-end digital lifecycle for employment offer letters:
- **Admin Management**: Secure administrative portal to author bilingual 5-page employment offers, save drafts, preview formatted documents, publish secure tokens, track candidate interaction stages, and revoke offers.
- **Candidate Portal**: Lightweight, responsive, mobile-first web interface where candidates view their exact 5-page bilingual appointment letter, must complete a full-document reading workflow, accept legal acknowledgment checkboxes, and digitally sign via an interactive canvas pad.
- **PDF Engine**: High-fidelity PDF generation mirroring the official FTS 5-page employment contract template (both unsigned draft/published original and final counter-signed version with embedded signature, timestamp, and audit watermark).
- **Audit & Compliance**: Granular immutable event logs capturing IP addresses, timestamps, user-agent fingerprints, and status transitions for complete legal traceability.

---

## 2. Requirements

- **PHP**: 8.2 or higher (with `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `gd` or `imagick` extensions enabled)
- **Composer**: 2.x
- **Database**: MySQL 8.0+ or MariaDB 10.4+ (SQLite also supported for local testing)
- **Web Server**: Apache / Nginx / PHP built-in server (`php artisan serve`)
- **Node.js & NPM** (optional, for asset bundling if customizing CSS/JS)

---

## 3. Installation

Clone or locate the repository in a standalone folder:

```bash
cd offer-letter-service
```

Install backend PHP dependencies via Composer:

```bash
composer install
```

---

## 4. Environment Setup

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Generate the unique application encryption key:

```bash
php artisan key:generate
```

Review and adjust application settings in `.env`:

```env
APP_NAME="Offer Letter Service"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8001

# MySQL Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=offer_letter_service
DB_USERNAME=root
DB_PASSWORD=

# Offer Link Base URL (used in sharing candidate links)
OFFER_BASE_URL=http://localhost:8001
```

---

## 5. Database Setup

Ensure MySQL server is running, then create the database if not already created:

```sql
CREATE DATABASE offer_letter_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 6. Migration

Run all database migrations to set up the tables (`admins`, `offer_letters`, `signatures`, `offer_events`, `documents`, and sessions):

```bash
php artisan migrate
```

---

## 7. Seeder

Seed the initial development administrator account:

```bash
php artisan db:seed
```

> **IMPORTANT SECURITY NOTICE**: The default seeder creates:
> - **Email**: `admin@example.com`
> - **Password**: `ChangeThisPassword`
>
> You **MUST** change this password immediately in production environments.

---

## 8. Running Locally

Start the local development server:

```bash
php artisan serve --no-reload --port=8001
```

*(Or simply `composer serve`)*

> **Windows Note**: On Windows environments, the `--no-reload` flag is required because PHP's built-in web server needs Windows system environment variables (`SystemRoot`, `WINDIR`) which Laravel's process reloader isolates by default.

Access the application in your browser:
- **Admin Portal**: [http://localhost:8001/login](http://localhost:8001/login)
- **Root redirect**: [http://localhost:8001/](http://localhost:8001/) automatically redirects to the admin panel.

---

## 9. PDF Generation

The service utilizes `barryvdh/laravel-dompdf` (DomPDF) to compile pure Blade templates into pixel-perfect A4 documents.
- **Original Unsigned PDF**: Renders the 5-page employment agreement with placeholder signature boxes.
- **Signed PDF**: Counter-signs page 5 with the candidate's drawn canvas signature, acceptance date/time, candidate IP, and a tamper-evident audit badge.
- PDF templates reside in `resources/views/pdf/`:
  - `offer-template.blade.php`: Original unsigned document
  - `offer-template-signed.blade.php`: Counter-signed legal document

---

## 10. Storage Configuration

PDF files and signature images are stored locally under private storage:

```text
storage/app/private/
    offers/
        {offer-id}/
            original/
                offer-{token}-original.pdf
            signed/
                offer-{token}-signed.pdf
            signatures/
                signature-{timestamp}.png
```

- Physical storage paths are **never** exposed to the public.
- Downloads are strictly streamed through protected controller actions validating permissions and cryptographic tokens.

---

## 11. Admin Login

1. Navigate to `/login`.
2. Enter admin credentials:
   - **Email**: `admin@example.com`
   - **Password**: `ChangeThisPassword`
3. Upon authentication, you will be redirected to the **Dashboard** (`/admin/dashboard`).

---

## 12. Offer Creation Workflow

1. Click **Create Offer** from the dashboard or sidebar.
2. Complete the candidate details:
   - **Candidate Information**: Full Name, Passport Number, Nationality.
   - **Position & Posting**: Designation, Place of Posting, Joining Date.
   - **Dates**: Offer Date and Offer Validity Date (mandatory expiration safeguard).
   - **Compensation**: Basic Salary, Other Allowances, Total Package (both numerical and formal words, e.g. *Two Thousand Five Hundred Dirhams Only*).
   - **Terms & Benefits**: Probation period, contract duration, working hours, weekly day off, overtime rules, annual leave, air ticket eligibility, notice period, and special conditions.
3. Choose an action:
   - **Save as Draft**: Saves the offer with status `draft`. No candidate link is active yet.
   - **Publish Offer**: Generates the 64-character unguessable token, compiles the official original PDF, transitions status to `published`, and generates the shareable link.
4. From the offer details screen (`/admin/offers/{id}`), preview the offer, copy the unique candidate signing link, download the original PDF, or revoke the offer if necessary.

---

## 13. Candidate Workflow

1. **Access**: Candidate receives and opens their unique secure URL (`/offer/{token}`).
2. **First View**: The system records the candidate's IP address, timestamp, and marks the status as `viewed`.
3. **Reading Mechanism**:
   - The candidate views the full 5-page offer document.
   - A progress bar tracks the candidate's scrolling through all pages.
   - The acknowledgment and signature section remains locked until the candidate reaches the end of page 5.
4. **Acknowledgment**: Candidate checks the formal declaration confirming agreement to all terms and FTS Standard Operating Procedures.
5. **Digital Signature**:
   - Candidate draws their signature using a touch/mouse digital canvas pad.
   - Clear and redraw capabilities are provided.
6. **Submission**:
   - Candidate clicks **Accept & Submit Offer**.
   - The system validates signature completeness, saves the PNG signature file, binds IP and user-agent metadata, transitions status to `signed`, and compiles the official counter-signed PDF.
7. **Download**: Candidate can instantly download the counter-signed PDF copy.

---

## 14. Testing

A complete automated test suite covers the entire system with **32 feature and unit tests (106 assertions)**:

```bash
php artisan test
```

### Test Coverage Highlights:
- **Admin Auth**: Login, invalid credentials, logout, dashboard protection, route authorization.
- **Offer Management**: Draft creation, edit draft, instant publish, draft publishing, preview view, revocation, immutability of signed offers.
- **Candidate Workflow**: Link verification, draft protection (404), expired view, revoked view, reading completion event, signing workflow, signed document generation & download.
- **Security Constraints**: Guest access restriction on all admin endpoints, prevention of signing revoked/expired offers, prevention of downloading unsigned PDFs as signed, resistance to token tampering.

---

## 15. Production Deployment

1. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
2. Set strong application key: `php artisan key:generate`.
3. Configure your production database credentials.
4. Cache configuration and routes for peak performance:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. Ensure write permissions on `storage/` and `bootstrap/cache/`:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```
6. Serve `public/` directory as the document root in Nginx / Apache with HTTPS enabled.

---

## 16. Security Notes

- **Cryptographic Tokens**: Candidate URLs use 64-character cryptographically secure random alphanumeric tokens (`Str::random(64)`), eliminating enumeration or brute-force risks.
- **Immutability**: Once an offer is signed, it is permanently locked against modifications or revocation by admins.
- **Audit Trails**: Every interaction (creation, update, publishing, link generation, candidate viewing, reading completion, signing, download, revocation) is logged in the `offer_events` table with IP and client information.
- **Storage Protection**: All documents and signatures are stored outside web roots and served exclusively through authenticated or token-verified controller streams.
- **CSRF & Validation**: All state-changing requests require valid CSRF tokens and adhere to strict server-side validation rules.
