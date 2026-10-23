<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_homepage_sends_standard_security_headers(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_content_security_policy_only_allows_this_sites_own_content(): void
    {
        $policy = $this->get('/')->headers->get('Content-Security-Policy');

        foreach ([
            "default-src 'self'",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ] as $rule) {
            $this->assertStringContainsString($rule, $policy);
        }

        $this->assertStringNotContainsString("script-src 'unsafe-inline'", $policy);
        $this->assertStringNotContainsString('*', $policy);
    }

    public function test_every_script_on_the_page_carries_this_requests_nonce(): void
    {
        $response = $this->get('/');
        $this->assertSame(1, preg_match("/script-src 'nonce-([A-Za-z0-9]+)'/", $response->headers->get('Content-Security-Policy'), $match));

        $html = $response->getContent();
        $this->assertSame(2, substr_count($html, '<script'));
        $this->assertSame(2, substr_count($html, '<script nonce="'.$match[1].'">'));

        // Inline event handlers would be blocked by the policy.
        $this->assertSame(0, preg_match('/\son[a-z]+="/i', $html));
    }

    public function test_nonce_changes_on_every_request(): void
    {
        $first = $this->get('/')->headers->get('Content-Security-Policy');
        $second = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertNotSame($first, $second);
    }

    public function test_demo_endpoints_also_send_the_headers(): void
    {
        foreach (['/guided-demo/guided-maya-headache', '/safety-demo/demo-ordinary-worry', '/ai-demo/guided-maya-headache'] as $url) {
            $this->postJson($url)
                ->assertOk()
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('X-Frame-Options', 'DENY');
        }
    }

    public function test_https_only_header_is_sent_in_production_only(): void
    {
        $this->get('/')->assertHeaderMissing('Strict-Transport-Security');

        $this->app->detectEnvironment(fn () => 'production');

        $this->get('/')->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }
}
