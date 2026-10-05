<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\Document;
use App\Models\OfferEvent;
use App\Models\OfferLetter;
use App\Models\Signature;
use App\Services\AuditService;
use App\Services\OfferPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModelsAndServicesTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->admin = Admin::create([
            'name'     => 'Test Admin',
            'email'    => 'admin_test@fts.ae',
            'password' => Hash::make('password123'),
        ]);
    }

    protected function createOffer(array $attributes = []): OfferLetter
    {
        return OfferLetter::create(array_merge([
            'admin_id'               => $this->admin->id,
            'candidate_name'         => 'Ahmed Al-Mansoor',
            'passport_number'        => 'N9876543',
            'nationality'            => 'Emirati',
            'designation'            => 'Facilities Engineer',
            'place_of_posting'       => 'Abu Dhabi',
            'offer_date'             => now()->format('Y-m-d'),
            'validity_date'          => now()->addDays(14)->format('Y-m-d'),
            'joining_date'           => now()->addDays(30)->format('Y-m-d'),
            'probation_period'       => '6 months',
            'contract_duration'      => '2 years',
            'working_hours'          => '8 hours',
            'weekly_day_off'         => 'Sunday',
            'overtime_info'          => 'Standard',
            'basic_salary'           => 5000,
            'basic_salary_words'     => 'Five Thousand',
            'other_allowances'       => 2000,
            'other_allowances_words' => 'Two Thousand',
            'total_salary'           => 7000,
            'total_salary_words'     => 'Seven Thousand',
            'salary_currency'        => 'AED',
            'annual_leave'           => '30 days',
            'air_ticket_allowance'   => 'Annual',
            'medical_requirements'   => 'Comprehensive',
            'notice_period'          => '60 days',
            'additional_terms'       => 'None',
            'status'                 => 'draft',
        ], $attributes));
    }

    // --- OfferLetter Model Tests ---

    public function test_offer_letter_booted_generates_token_if_empty(): void
    {
        $offer = $this->createOffer(['token' => null]);
        $this->assertNotEmpty($offer->token);
        $this->assertEquals(64, strlen($offer->token));
    }

    public function test_offer_letter_preserves_custom_token(): void
    {
        $customToken = str_repeat('a', 64);
        $offer = $this->createOffer(['token' => $customToken]);
        $this->assertEquals($customToken, $offer->token);
    }

    public function test_offer_letter_admin_relationship(): void
    {
        $offer = $this->createOffer();
        $this->assertInstanceOf(Admin::class, $offer->admin);
        $this->assertEquals($this->admin->id, $offer->admin->id);
    }

    public function test_offer_letter_signature_relationship(): void
    {
        $offer = $this->createOffer();
        $signature = Signature::create([
            'offer_letter_id' => $offer->id,
            'signature_data'  => 'data:image/png;base64,sample',
            'signature_path'  => 'sample.png',
            'ip_address'      => '127.0.0.1',
            'user_agent'      => 'TestAgent',
            'signed_at'       => now(),
        ]);

        $this->assertInstanceOf(Signature::class, $offer->signature);
        $this->assertEquals($signature->id, $offer->signature->id);
        $this->assertInstanceOf(OfferLetter::class, $signature->offerLetter);
    }

    public function test_offer_letter_events_relationship(): void
    {
        $offer = $this->createOffer();
        OfferEvent::create([
            'offer_letter_id' => $offer->id,
            'event_type'      => 'offer_created',
            'ip_address'      => '127.0.0.1',
            'user_agent'      => 'Agent',
        ]);

        $this->assertCount(1, $offer->events);
        $this->assertEquals('offer_created', $offer->events->first()->event_type);
    }

    public function test_offer_letter_documents_relationships(): void
    {
        $offer = $this->createOffer();
        $original = Document::create([
            'offer_letter_id' => $offer->id,
            'type'            => 'original',
            'file_path'       => 'path/original.pdf',
            'file_name'       => 'original.pdf',
        ]);
        $signed = Document::create([
            'offer_letter_id' => $offer->id,
            'type'            => 'signed',
            'file_path'       => 'path/signed.pdf',
            'file_name'       => 'signed.pdf',
        ]);

        $this->assertCount(2, $offer->documents);
        $this->assertEquals($original->id, $offer->originalDocument->id);
        $this->assertEquals($signed->id, $offer->signedDocument->id);
        $this->assertEquals($offer->id, $original->offerLetter->id);
    }

    public function test_offer_letter_is_expired_logic(): void
    {
        // Past validity date and unsigned -> expired
        $expiredOffer = $this->createOffer([
            'validity_date' => now()->subDay(),
            'status'        => 'published',
        ]);
        $this->assertTrue($expiredOffer->isExpired());

        // Past validity date but signed -> not expired
        $signedOffer = $this->createOffer([
            'validity_date' => now()->subDay(),
            'status'        => 'signed',
        ]);
        $this->assertFalse($signedOffer->isExpired());

        // Future validity date -> not expired
        $activeOffer = $this->createOffer([
            'validity_date' => now()->addDay(),
            'status'        => 'published',
        ]);
        $this->assertFalse($activeOffer->isExpired());

        // Null validity date -> not expired (tested on unpersisted model since validity_date column is not null)
        $nullDateOffer = new OfferLetter([
            'validity_date' => null,
            'status'        => 'published',
        ]);
        $this->assertFalse($nullDateOffer->isExpired());
    }

    public function test_offer_letter_is_revoked_logic(): void
    {
        $revoked = $this->createOffer(['status' => 'revoked']);
        $this->assertTrue($revoked->isRevoked());

        $draft = $this->createOffer(['status' => 'draft']);
        $this->assertFalse($draft->isRevoked());
    }

    public function test_offer_letter_is_available_for_candidate_logic(): void
    {
        // Draft is never available
        $draft = $this->createOffer(['status' => 'draft']);
        $this->assertFalse($draft->isAvailableForCandidate());

        // Revoked is not available
        $revoked = $this->createOffer(['status' => 'revoked']);
        $this->assertFalse($revoked->isAvailableForCandidate());

        // Expired is not available
        $expired = $this->createOffer([
            'status'        => 'published',
            'validity_date' => now()->subDay(),
        ]);
        $this->assertFalse($expired->isAvailableForCandidate());

        // Signed is available (read-only view)
        $signed = $this->createOffer(['status' => 'signed']);
        $this->assertTrue($signed->isAvailableForCandidate());

        // Published with valid date is available
        $published = $this->createOffer([
            'status'        => 'published',
            'validity_date' => now()->addDays(5),
        ]);
        $this->assertTrue($published->isAvailableForCandidate());
    }

    public function test_offer_letter_candidate_link(): void
    {
        $offer = $this->createOffer();
        $this->assertEquals(url('/offer/' . $offer->token), $offer->candidateLink());
    }

    public function test_offer_letter_status_badges(): void
    {
        $statuses = [
            'draft'             => 'badge-draft',
            'published'         => 'badge-published',
            'viewed'            => 'badge-viewed',
            'pending_signature' => 'badge-pending',
            'accepted'          => 'badge-accepted',
            'signed'            => 'badge-signed',
            'expired'           => 'badge-expired',
            'revoked'           => 'badge-revoked',
        ];

        foreach ($statuses as $status => $expectedClass) {
            $offer = $this->createOffer(['status' => $status]);
            $this->assertEquals($expectedClass, $offer->status_badge['class']);
        }

        // Test fallback custom status on model instance without violating DB enum check
        $customOffer = new OfferLetter(['status' => 'custom_state']);
        $this->assertEquals('badge-default', $customOffer->status_badge['class']);
    }

    // --- OfferEvent Model Tests ---

    public function test_offer_event_labels(): void
    {
        $eventTypes = [
            'offer_created'           => 'Offer Created',
            'offer_updated'           => 'Offer Updated',
            'offer_published'         => 'Offer Published',
            'link_generated'          => 'Link Generated',
            'offer_viewed'            => 'Offer Viewed',
            'reading_started'         => 'Reading Started',
            'reading_complete'        => 'Reading Completed',
            'acknowledgment_accepted' => 'Acknowledgment Accepted',
            'signature_submitted'     => 'Signature Submitted',
            'offer_signed'            => 'Offer Signed',
            'offer_downloaded'        => 'Offer Downloaded',
            'offer_revoked'           => 'Offer Revoked',
            'offer_expired'           => 'Offer Expired',
            'custom_event_name'       => 'Custom Event Name',
        ];

        $offer = $this->createOffer();

        foreach ($eventTypes as $type => $expectedLabel) {
            $event = OfferEvent::create([
                'offer_letter_id' => $offer->id,
                'event_type'      => $type,
                'ip_address'      => '127.0.0.1',
            ]);
            $this->assertEquals($expectedLabel, $event->event_label);
        }
    }

    // --- Admin Model Tests ---

    public function test_admin_model_properties_and_relationship(): void
    {
        $this->assertTrue(Hash::check('password123', $this->admin->password));
        $this->assertArrayNotHasKey('password', $this->admin->toArray());

        $this->createOffer();
        $this->assertCount(1, $this->admin->offerLetters);
    }

    // --- AuditService Tests ---

    public function test_audit_service_logs_with_defaults_and_custom_metadata(): void
    {
        $audit = new AuditService();
        $offer = $this->createOffer();

        // Log without explicit IP / user agent
        $audit->log($offer, 'test_default_event');
        $this->assertDatabaseHas('offer_events', [
            'offer_letter_id' => $offer->id,
            'event_type'      => 'test_default_event',
        ]);

        // Log with explicit IP, user agent, and metadata
        $audit->log($offer, 'test_custom_event', '192.168.1.100', 'CustomBrowser/1.0', ['key' => 'value']);
        $this->assertDatabaseHas('offer_events', [
            'offer_letter_id' => $offer->id,
            'event_type'      => 'test_custom_event',
            'ip_address'      => '192.168.1.100',
            'user_agent'      => 'CustomBrowser/1.0',
        ]);
    }

    // --- OfferPdfService Tests ---

    public function test_pdf_service_generates_original_and_signed_documents(): void
    {
        $service = new OfferPdfService();
        $offer = $this->createOffer(['status' => 'published']);

        // 1. Generate Original PDF
        $originalDoc = $service->generateOriginal($offer);
        $this->assertInstanceOf(Document::class, $originalDoc);
        $this->assertEquals('original', $originalDoc->type);
        Storage::disk('local')->assertExists($originalDoc->file_path);

        // 2. Add signature and generate Signed PDF
        Signature::create([
            'offer_letter_id' => $offer->id,
            'signature_data'  => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
            'signature_path'  => 'sig.png',
            'ip_address'      => '127.0.0.1',
            'signed_at'       => now(),
        ]);
        $offer->refresh()->load('signature');

        $signedDoc = $service->generateSigned($offer);
        $this->assertInstanceOf(Document::class, $signedDoc);
        $this->assertEquals('signed', $signedDoc->type);
        Storage::disk('local')->assertExists($signedDoc->file_path);

        // 3. Document path resolver
        $localPath = $service->getDocumentPath($signedDoc);
        $this->assertNotEmpty($localPath);
    }
}
