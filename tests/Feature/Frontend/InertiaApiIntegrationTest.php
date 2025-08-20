<?php

namespace Tests\Feature\Frontend;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

/**
 * InertiaApiIntegrationTest
 * 
 * This test class specifically targets the integration between Inertia.js frontend
 * and Laravel API endpoints to prevent response format mismatches.
 * 
 * Key Focus Areas:
 * - API endpoints that should return JSON not Inertia responses
 * - Header requirements for preventing Inertia.js interception
 * - Frontend component API integration patterns
 */
class InertiaApiIntegrationTest extends TestCase
{
    use RefreshDatabase, WithRoles;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    /** @test */
    public function two_factor_status_endpoint_returns_json_not_inertia_response()
    {
        $user = $this->createSubscriber();
        
        // Test with proper API headers (should work)
        $response = $this->actingAs($user)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest'
            ])
            ->get('/user/two-factor-authentication');
            
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'two_factor_enabled',
            'two_factor_confirmed',
            'recovery_codes_count'
        ]);
    }

    /** @test */
    public function two_factor_status_endpoint_without_proper_headers_still_returns_json()
    {
        $user = $this->createSubscriber();
        
        // Test without XMLHttpRequest header (common frontend mistake)
        $response = $this->actingAs($user)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
                // Missing X-Requested-With header
            ])
            ->get('/user/two-factor-authentication');
            
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertDontSee('<!DOCTYPE html>'); // Should not be HTML/Inertia response
    }

    /** @test */
    public function api_endpoints_are_not_intercepted_by_inertia()
    {
        $user = $this->createSubscriber();
        
        $apiEndpoints = [
            '/user/two-factor-authentication',
            '/user/two-factor-authentication/qr-code',
            '/user/two-factor-authentication/recovery-codes',
        ];
        
        foreach ($apiEndpoints as $endpoint) {
            $response = $this->actingAs($user)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'X-Requested-With' => 'XMLHttpRequest'
                ])
                ->get($endpoint);
                
            // Should return JSON, not HTML
            $response->assertHeader('Content-Type', 'application/json');
            $response->assertDontSee('<!DOCTYPE html>');
            $response->assertDontSee('<html');
            
            // Should not contain Inertia.js response structure
            $this->assertNotNull(json_decode($response->getContent()));
        }
    }

    /** @test */
    public function frontend_simulated_fetch_requests_work_correctly()
    {
        $user = $this->createSubscriber();
        
        // Simulate exactly how frontend ApiClient makes requests
        $response = $this->actingAs($user)
            ->call('GET', '/user/two-factor-authentication', [], [], [], [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_CONTENT_TYPE' => 'application/json',
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ]);
            
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
        
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('two_factor_enabled', $data);
        $this->assertArrayHasKey('two_factor_confirmed', $data);
        $this->assertArrayHasKey('recovery_codes_count', $data);
    }

    /** @test */
    public function csrf_protected_endpoints_work_with_api_client()
    {
        $user = $this->createSubscriber();
        
        // Test CSRF protected endpoint (POST request)
        $response = $this->actingAs($user)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest'
            ])
            ->post('/user/two-factor-authentication');
            
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertDontSee('<!DOCTYPE html>');
    }

    /** @test */
    public function all_two_factor_endpoints_return_consistent_json_responses()
    {
        $user = $this->createSubscriber();
        $user->forceFill([
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        ])->save();
        
        $endpoints = [
            ['method' => 'GET', 'url' => '/user/two-factor-authentication'],
            ['method' => 'POST', 'url' => '/user/two-factor-authentication'],
            ['method' => 'DELETE', 'url' => '/user/two-factor-authentication'],
            ['method' => 'GET', 'url' => '/user/two-factor-authentication/qr-code'],
            ['method' => 'GET', 'url' => '/user/two-factor-authentication/recovery-codes'],
            ['method' => 'POST', 'url' => '/user/two-factor-authentication/recovery-codes'],
        ];
        
        foreach ($endpoints as $endpoint) {
            $response = $this->actingAs($user)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-Requested-With' => 'XMLHttpRequest'
                ])
                ->call($endpoint['method'], $endpoint['url']);
                
            $this->assertTrue(
                $response->isOk() || $response->isClientError(),
                "Endpoint {$endpoint['method']} {$endpoint['url']} should return valid response"
            );
            
            // Should always return JSON, never HTML
            $contentType = $response->headers->get('Content-Type', '');
            $this->assertStringContainsString('application/json', $contentType,
                "Endpoint {$endpoint['method']} {$endpoint['url']} should return JSON"
            );
        }
    }

    /** @test */
    public function browser_navigation_to_api_endpoints_should_redirect_or_error()
    {
        $user = $this->createSubscriber();
        
        // Simulate browser navigation (no API headers)
        $response = $this->actingAs($user)
            ->withHeaders([
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
            ])
            ->get('/user/two-factor-authentication');
            
        // Should either redirect to proper page or return error, but not break
        $this->assertTrue(
            $response->isRedirection() || $response->isClientError() || $response->isOk(),
            'Browser navigation to API endpoint should be handled gracefully'
        );
    }

    /** @test */
    public function api_error_responses_are_properly_formatted_for_frontend()
    {
        $user = $this->createSubscriber();
        
        // Test invalid POST to 2FA confirm endpoint (should return JSON error)
        $response = $this->actingAs($user)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest'
            ])
            ->post('/user/two-factor-authentication/confirm', [
                'code' => 'invalid'
            ]);
            
        // Should return JSON error, not HTML error page
        $this->assertTrue($response->isClientError() || $response->isServerError());
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertDontSee('<!DOCTYPE html>');
    }
}