<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\OfferLetter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CandidateWorkflowTest extends TestCase
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

    protected function createOffer(array $attributes = []): OfferLetter
    {
        return OfferLetter::create(array_merge([
            'candidate_name'         => 'John Doe',
            'passport_number'        => 'A1234567',
            'nationality'            => 'Filipino',
            'designation'            => 'HVAC Technician',
            'place_of_posting'       => 'Dubai, UAE',
            'offer_date'             => now()->subDays(2)->format('Y-m-d'),
            'validity_date'          => now()->addDays(10)->format('Y-m-d'),
            'joining_date'           => now()->addDays(20)->format('Y-m-d'),
            'probation_period'       => '3 months',
            'contract_duration'      => '2 years',
            'working_hours'          => '9 hours per day',
            'weekly_day_off'         => 'Friday',
            'basic_salary'           => 2000,
            'basic_salary_words'     => 'Two Thousand Dirhams Only',
            'other_allowances'       => 500,
            'other_allowances_words' => 'Five Hundred Dirhams Only',
            'total_salary'           => 2500,
            'total_salary_words'     => 'Two Thousand Five Hundred Dirhams Only',
            'salary_currency'        => 'AED',
            'admin_id'               => $this->admin->id,
            'status'                 => 'published',
            'published_at'           => now()->subDays(2),
        ], $attributes));
    }

    public function test_candidate_can_view_published_offer_without_auth(): void
    {
        $offer = $this->createOffer();

        $response = $this->get(route('candidate.offer.show', $offer->token));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('HVAC Technician');
        $response->assertSee('Page 1 of 5');

        $offer->refresh();
        $this->assertEquals('viewed', $offer->status);
        $this->assertNotNull($offer->viewed_at);
    }

    public function test_candidate_cannot_view_draft_offer(): void
    {
        $offer = $this->createOffer(['status' => 'draft', 'published_at' => null]);

        $response = $this->get(route('candidate.offer.show', $offer->token));

        $response->assertStatus(404);
    }

    public function test_candidate_viewing_revoked_offer_sees_revoked_page(): void
    {
        $offer = $this->createOffer([
            'status'     => 'revoked',
            'revoked_at' => now(),
        ]);

        $response = $this->get(route('candidate.offer.show', $offer->token));

        $response->assertStatus(200);
        $response->assertSee('Offer Letter Revoked');
    }

    public function test_candidate_viewing_expired_offer_sees_expired_page(): void
    {
        $offer = $this->createOffer([
            'offer_date'    => now()->subDays(20)->format('Y-m-d'),
            'validity_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $response = $this->get(route('candidate.offer.show', $offer->token));

        $response->assertStatus(200);
        $response->assertSee('Offer Letter Expired');
    }

    public function test_invalid_token_returns_404(): void
    {
        $response = $this->get(route('candidate.offer.show', 'non-existent-token-12345'));

        $response->assertStatus(404);
    }

    public function test_candidate_marks_reading_complete(): void
    {
        $offer = $this->createOffer(['status' => 'viewed', 'viewed_at' => now()]);

        $response = $this->postJson(route('candidate.offer.reading-complete', $offer->token));

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        $offer->refresh();
        $this->assertEquals('pending_signature', $offer->status);
    }

    public function test_candidate_can_sign_offer(): void
    {
        $offer = $this->createOffer(['status' => 'pending_signature']);

        $sampleSig = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->postJson(route('candidate.offer.sign', $offer->token), [
            'signature_data' => $sampleSig,
            'acknowledged'   => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        $offer->refresh();
        $this->assertEquals('signed', $offer->status);
        $this->assertNotNull($offer->signed_at);
        $this->assertNotNull($offer->acknowledged_at);

        $this->assertDatabaseHas('signatures', [
            'offer_letter_id' => $offer->id,
        ]);

        $this->assertDatabaseHas('documents', [
            'offer_letter_id' => $offer->id,
            'type'            => 'signed',
        ]);
    }

    public function test_candidate_cannot_sign_already_signed_offer(): void
    {
        $offer = $this->createOffer([
            'status'          => 'signed',
            'signed_at'       => now(),
            'acknowledged_at' => now(),
        ]);

        $sampleSig = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->postJson(route('candidate.offer.sign', $offer->token), [
            'signature_data' => $sampleSig,
            'acknowledged'   => true,
        ]);

        $response->assertStatus(409);
    }

    public function test_candidate_can_download_signed_offer(): void
    {
        $offer = $this->createOffer([
            'status'          => 'signed',
            'signed_at'       => now(),
            'acknowledged_at' => now(),
            'candidate_ip'    => '127.0.0.1',
        ]);

        $sampleSig = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        \App\Models\Signature::create([
            'offer_letter_id' => $offer->id,
            'signature_data'  => $sampleSig,
            'signature_path'  => 'offers/' . $offer->id . '/signatures/test.png',
            'ip_address'      => '127.0.0.1',
            'signed_at'       => now(),
        ]);

        $response = $this->get(route('candidate.offer.download', $offer->token));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }
}
