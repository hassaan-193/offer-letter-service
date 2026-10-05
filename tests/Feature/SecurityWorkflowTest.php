<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\OfferLetter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->admin = Admin::create([
            'name'     => 'Security Admin',
            'email'    => 'sec@fts.ae',
            'password' => Hash::make('password123'),
        ]);
    }

    protected function createSampleOffer(array $overrides = []): OfferLetter
    {
        return OfferLetter::create(array_merge([
            'candidate_name'         => 'Test Candidate',
            'passport_number'        => 'T9988776',
            'nationality'            => 'Pakistani',
            'designation'            => 'Electrician',
            'place_of_posting'       => 'Dubai, UAE',
            'offer_date'             => now()->subDays(1)->format('Y-m-d'),
            'validity_date'          => now()->addDays(14)->format('Y-m-d'),
            'joining_date'           => now()->addDays(20)->format('Y-m-d'),
            'basic_salary'           => 1800,
            'basic_salary_words'     => 'One Thousand Eight Hundred Dirhams Only',
            'other_allowances'       => 400,
            'other_allowances_words' => 'Four Hundred Dirhams Only',
            'total_salary'           => 2200,
            'total_salary_words'     => 'Two Thousand Two Hundred Dirhams Only',
            'admin_id'               => $this->admin->id,
            'status'                 => 'published',
            'published_at'           => now()->subDays(1),
        ], $overrides));
    }

    public function test_guest_cannot_access_any_admin_endpoint(): void
    {
        $offer = $this->createSampleOffer();

        $routes = [
            ['GET',  route('admin.dashboard')],
            ['GET',  route('admin.offers.index')],
            ['GET',  route('admin.offers.create')],
            ['POST', route('admin.offers.store')],
            ['GET',  route('admin.offers.show', $offer)],
            ['GET',  route('admin.offers.edit', $offer)],
            ['PUT',  route('admin.offers.update', $offer)],
            ['GET',  route('admin.offers.preview', $offer)],
            ['POST', route('admin.offers.publish', $offer)],
            ['POST', route('admin.offers.revoke', $offer)],
            ['GET',  route('admin.offers.download.original', $offer)],
            ['GET',  route('admin.offers.download.signed', $offer)],
            ['DELETE', route('admin.offers.destroy', $offer)],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->call($method, $url);
            $response->assertRedirect('/login');
        }
    }

    public function test_candidate_cannot_sign_revoked_offer(): void
    {
        $offer = $this->createSampleOffer([
            'status'     => 'revoked',
            'revoked_at' => now(),
        ]);

        $sampleSig = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->postJson(route('candidate.offer.sign', $offer->token), [
            'signature_data' => $sampleSig,
            'acknowledged'   => true,
        ]);

        $response->assertStatus(403);
        $this->assertEquals('revoked', $offer->fresh()->status);
    }

    public function test_candidate_cannot_sign_expired_offer(): void
    {
        $offer = $this->createSampleOffer([
            'offer_date'    => now()->subDays(30)->format('Y-m-d'),
            'validity_date' => now()->subDays(2)->format('Y-m-d'),
        ]);

        $sampleSig = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->postJson(route('candidate.offer.sign', $offer->token), [
            'signature_data' => $sampleSig,
            'acknowledged'   => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_candidate_cannot_download_unsigned_offer_from_signed_endpoint(): void
    {
        $offer = $this->createSampleOffer(['status' => 'published']);

        $response = $this->get(route('candidate.offer.download', $offer->token));

        $response->assertStatus(403);
    }

    public function test_tampered_token_cannot_access_offer(): void
    {
        $offer = $this->createSampleOffer();
        $tamperedToken = substr($offer->token, 0, -5) . 'XXXXX';

        $response = $this->get(route('candidate.offer.show', $tamperedToken));
        $response->assertStatus(404);
    }
}
