<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class IntroPageTest extends TestCase
{
    public function test_guests_see_the_branding_and_are_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('alt="EnerBridge logo"', false)
            ->assertSee('Smart energy. Stronger tomorrow.')
            ->assertSee('window.location.replace', false)
            ->assertSee(str_replace('/', '\\/', route('login')), false)
            ->assertSee('), 1000);', false)
            ->assertDontSee('intro-choices', false);
    }

    public function test_authenticated_users_are_sent_to_the_dashboard_from_the_intro_page(): void
    {
        $this->actingAs(User::factory()->make());

        $response = $this->get('/');

        $response->assertRedirect('/dashboard');
    }
}
