<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Document;
use App\Models\OfferLetter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminOfferManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->admin = Admin::create([
            'name'     => 'HR Admin',
            'email'    => 'hr@fts.ae',
            'password' => Hash::make('password123'),
        ]);
    }

    protected function validOfferPayload(array $overrides = []): array
    {
        return array_merge([
            'candidate_name'         => 'Priyadarsan Perinjanam',
            'passport_number'        => 'Z1234567',
            'nationality'            => 'Indian',
            'designation'            => 'BMS Operator',
            'place_of_posting'       => 'Abu Dhabi, UAE',
            'offer_date'             => now()->format('Y-m-d'),
            'validity_date'          => now()->addDays(14)->format('Y-m-d'),
            'joining_date'           => now()->addDays(30)->format('Y-m-d'),
            'probation_period'       => '3 months',
            'contract_duration'      => '2 years',
            'working_hours'          => '9 hours per day',
            'weekly_day_off'         => 'Friday',
            'overtime_info'          => 'As per UAE Labour Law',
            'basic_salary'           => 2500,
            'basic_salary_words'     => 'Two Thousand Five Hundred Dirhams Only',
            'other_allowances'       => 1000,
            'other_allowances_words' => 'One Thousand Dirhams Only',
            'total_salary'           => 3500,
            'total_salary_words'     => 'Three Thousand Five Hundred Dirhams Only',
            'salary_currency'        => 'AED',
            'annual_leave'           => '30 days per year',
            'air_ticket_allowance'   => 'Economy class return once every 2 years',
            'medical_requirements'   => 'Standard UAE medical clearance',
            'notice_period'          => '30 days',
            'additional_terms'       => 'None',
        ], $overrides);
    }

    public function test_admin_can_view_offers_index(): void
    {
        OfferLetter::create(array_merge($this->validOfferPayload(), [
            'admin_id' => $this->admin->id,
            'status'   => 'draft',
        ]));

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.offers.index'));

        $response->assertStatus(200);
        $response->assertSee('Priyadarsan Perinjanam');
    }

    public function test_admin_can_create_offer_as_draft(): void
    {
        $payload = $this->validOfferPayload(['action' => 'draft']);

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.offers.store'), $payload);

        $this->assertDatabaseHas('offer_letters', [
            'candidate_name'  => 'Priyadarsan Perinjanam',
            'passport_number' => 'Z1234567',
            'status'          => 'draft',
        ]);

        $offer = OfferLetter::first();
        $this->assertNotNull($offer->token);
        $this->assertEquals(64, strlen($offer->token));

        $response->assertRedirect(route('admin.offers.show', $offer));
    }

    public function test_admin_can_create_and_publish_offer_immediately(): void
    {
        $payload = $this->validOfferPayload(['action' => 'publish']);

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.offers.store'), $payload);

        $offer = OfferLetter::first();
        $this->assertEquals('published', $offer->status);
        $this->assertNotNull($offer->published_at);

        $this->assertDatabaseHas('documents', [
            'offer_letter_id' => $offer->id,
            'type'            => 'original',
        ]);

        $response->assertRedirect(route('admin.offers.show', $offer));
    }

    public function test_admin_can_view_offer_preview(): void
    {
        $offer = OfferLetter::create(array_merge($this->validOfferPayload(), [
            'admin_id' => $this->admin->id,
            'status'   => 'draft',
        ]));

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.offers.preview', $offer));

        $response->assertStatus(200);
        $response->assertSee('Offer Letter Preview');
        $response->assertSee($offer->candidate_name);
    }

    public function test_admin_can_edit_draft_offer(): void
    {
        $offer = OfferLetter::create(array_merge($this->validOfferPayload(), [
            'admin_id' => $this->admin->id,
            'status'   => 'draft',
        ]));

        $response = $this->actingAs($this->admin, 'admin')->put(
            route('admin.offers.update', $offer),
            array_merge($this->validOfferPayload(['candidate_name' => 'Updated Name']), ['action' => 'draft'])
        );

        $response->assertRedirect(route('admin.offers.show', $offer));
        $this->assertDatabaseHas('offer_letters', [
            'id'             => $offer->id,
            'candidate_name' => 'Updated Name',
        ]);
    }

    public function test_admin_cannot_edit_signed_offer(): void
    {
        $offer = OfferLetter::create(array_merge($this->validOfferPayload(), [
            'admin_id'  => $this->admin->id,
            'status'    => 'signed',
            'signed_at' => now(),
        ]));

        $response = $this->actingAs($this->admin, 'admin')->put(
            route('admin.offers.update', $offer),
            array_merge($this->validOfferPayload(['candidate_name' => 'Malicious Update']))
        );

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('offer_letters', [
            'id'             => $offer->id,
            'candidate_name' => 'Priyadarsan Perinjanam',
        ]);
    }

    public function test_admin_can_publish_existing_draft_offer(): void
    {
        $offer = OfferLetter::create(array_merge($this->validOfferPayload(), [
            'admin_id' => $this->admin->id,
            'status'   => 'draft',
        ]));

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.offers.publish', $offer));

        $response->assertRedirect(route('admin.offers.show', $offer));
        $offer->refresh();
        $this->assertEquals('published', $offer->status);
        $this->assertNotNull($offer->published_at);
        $this->assertNotNull($offer->originalDocument);
    }

    public function test_admin_can_revoke_published_offer(): void
    {
        $offer = OfferLetter::create(array_merge($this->validOfferPayload(), [
            'admin_id'     => $this->admin->id,
            'status'       => 'published',
            'published_at' => now(),
        ]));

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.offers.revoke', $offer));

        $offer->refresh();
        $this->assertEquals('revoked', $offer->status);
        $this->assertNotNull($offer->revoked_at);
    }

    public function test_admin_cannot_revoke_signed_offer(): void
    {
        $offer = OfferLetter::create(array_merge($this->validOfferPayload(), [
            'admin_id'  => $this->admin->id,
            'status'    => 'signed',
            'signed_at' => now(),
        ]));

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.offers.revoke', $offer));

        $response->assertSessionHas('error');
        $offer->refresh();
        $this->assertEquals('signed', $offer->status);
    }

    public function test_admin_can_download_original_pdf(): void
    {
        $offer = OfferLetter::create(array_merge($this->validOfferPayload(), [
            'admin_id'     => $this->admin->id,
            'status'       => 'published',
            'published_at' => now(),
        ]));

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.offers.download.original', $offer));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_admin_cannot_download_signed_pdf_if_not_signed(): void
    {
        $offer = OfferLetter::create(array_merge($this->validOfferPayload(), [
            'admin_id'     => $this->admin->id,
            'status'       => 'published',
            'published_at' => now(),
        ]));

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.offers.download.signed', $offer));

        $response->assertSessionHas('error');
    }

    public function test_admin_can_download_signed_pdf_when_available(): void
    {
        $offer = OfferLetter::create(array_merge($this->validOfferPayload(), [
            'admin_id'  => $this->admin->id,
            'status'    => 'signed',
            'signed_at' => now(),
        ]));

        \App\Models\Signature::create([
            'offer_letter_id' => $offer->id,
            'signature_data'  => 'data:image/png;base64,sample',
            'signature_path'  => 'sig.png',
            'ip_address'      => '127.0.0.1',
            'signed_at'       => now(),
        ]);

        // Generate signed PDF
        app(\App\Services\OfferPdfService::class)->generateSigned($offer);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.offers.download.signed', $offer));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_admin_can_filter_offers_by_search_status_and_dates(): void
    {
        $offer1 = OfferLetter::create(array_merge($this->validOfferPayload([
            'candidate_name'  => 'Unique Candidate Alpha',
            'passport_number' => 'PASS999',
            'offer_date'      => '2026-05-01',
        ]), [
            'admin_id' => $this->admin->id,
            'status'   => 'draft',
        ]));

        $offer2 = OfferLetter::create(array_merge($this->validOfferPayload([
            'candidate_name'  => 'Different Candidate Beta',
            'passport_number' => 'PASS888',
            'offer_date'      => '2026-06-01',
        ]), [
            'admin_id' => $this->admin->id,
            'status'   => 'published',
        ]));

        // Search by name
        $resName = $this->actingAs($this->admin, 'admin')->get(route('admin.offers.index', ['search' => 'Alpha']));
        $resName->assertSee('Unique Candidate Alpha');
        $resName->assertDontSee('Different Candidate Beta');

        // Search by passport
        $resPass = $this->actingAs($this->admin, 'admin')->get(route('admin.offers.index', ['search' => 'PASS888']));
        $resPass->assertSee('Different Candidate Beta');
        $resPass->assertDontSee('Unique Candidate Alpha');

        // Filter by status
        $resStatus = $this->actingAs($this->admin, 'admin')->get(route('admin.offers.index', ['status' => 'draft']));
        $resStatus->assertSee('Unique Candidate Alpha');
        $resStatus->assertDontSee('Different Candidate Beta');

        // Filter by date range
        $resDate = $this->actingAs($this->admin, 'admin')->get(route('admin.offers.index', [
            'date_from' => '2026-05-20',
            'date_to'   => '2026-06-10',
        ]));
        $resDate->assertSee('Different Candidate Beta');
        $resDate->assertDontSee('Unique Candidate Alpha');
    }

    public function test_offer_creation_fails_with_invalid_validation_data(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.offers.store'), [
            'candidate_name' => '', // Required
            'basic_salary'   => -500, // Must be >= 0
        ]);

        $response->assertSessionHasErrors(['candidate_name', 'basic_salary', 'passport_number', 'total_salary']);
    }

    public function test_admin_can_delete_offer(): void
    {
        $offer = OfferLetter::create(array_merge($this->validOfferPayload(), [
            'admin_id' => $this->admin->id,
            'status'   => 'draft',
        ]));

        $response = $this->actingAs($this->admin, 'admin')->delete(route('admin.offers.destroy', $offer));

        $response->assertRedirect(route('admin.offers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('offer_letters', ['id' => $offer->id]);
    }

    public function test_admin_can_add_custom_content_and_it_shows_in_preview_pdf_and_candidate_view(): void
    {
        $customHeading = "10. Special Site Allowance & Accommodation Policy";
        $customContent = "Special Project Bonus of AED 1,000 upon successful completion of the Dubai Mall retrofit.";

        $payload = $this->validOfferPayload([
            'additional_terms_title' => $customHeading,
            'additional_terms'       => $customContent,
            'action'                 => 'publish',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.offers.store'), $payload);

        $offer = OfferLetter::where('candidate_name', 'Priyadarsan Perinjanam')->first();
        $this->assertNotNull($offer);
        $this->assertEquals($customHeading, $offer->additional_terms_title);
        $this->assertEquals($customContent, $offer->additional_terms);

        // 1. Verify in Admin Preview
        $previewRes = $this->actingAs($this->admin, 'admin')->get(route('admin.offers.preview', $offer));
        $previewRes->assertStatus(200);
        $previewRes->assertSee($customHeading);
        $previewRes->assertSee($customContent);

        // 2. Verify in Candidate View
        $candidateRes = $this->get(route('candidate.offer.show', $offer->token));
        $candidateRes->assertStatus(200);
        $candidateRes->assertSee($customHeading);
        $candidateRes->assertSee($customContent);

        // 3. Verify in Admin Details View
        $showRes = $this->actingAs($this->admin, 'admin')->get(route('admin.offers.show', $offer));
        $showRes->assertStatus(200);
        $showRes->assertSee($customHeading);
        $showRes->assertSee($customContent);
    }
}

