<?php

namespace Tests\Feature;

use App\Support\StationSetupState;
use Tests\TestCase;

class StationSetupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['station.setup_complete' => false]);
    }

    public function test_incomplete_install_redirects_login_to_setup(): void
    {
        $this->assertFalse(StationSetupState::isComplete());
        $this->get('/login')->assertRedirect(route('setup.show'));
    }

    public function test_incomplete_install_redirects_app_pages_to_setup(): void
    {
        $this->assertFalse(StationSetupState::isComplete());
        $this->get('/dashboard')->assertRedirect(route('setup.show'));
    }

    public function test_setup_screen_can_be_rendered(): void
    {
        $this->get('/setup')
            ->assertOk()
            ->assertSee('First-run station setup')
            ->assertSee('Local MySQL')
            ->assertSee('Cloud backup');
    }

    public function test_setup_requires_mysql_password(): void
    {
        $this->from(route('setup.show'))
            ->post('/setup', [
                'db_host' => '127.0.0.1',
                'db_port' => 3306,
                'db_database' => 'smart_weighbridge',
                'db_username' => 'root',
                'com_port' => 'COM1',
            ])
            ->assertRedirect(route('setup.show'))
            ->assertSessionHasErrors('db_password');
    }

    public function test_cloud_fields_are_required_when_sync_is_enabled(): void
    {
        $this->from(route('setup.show'))
            ->post('/setup', [
                'db_host' => '127.0.0.1',
                'db_port' => 3306,
                'db_database' => 'smart_weighbridge',
                'db_username' => 'root',
                'db_password' => 'secret',
                'com_port' => 'COM1',
                'cloud_sync_enabled' => '1',
            ])
            ->assertRedirect(route('setup.show'))
            ->assertSessionHasErrors(['db_cloud_host', 'db_cloud_username', 'db_cloud_password']);
    }

    public function test_completed_setup_skips_the_wizard(): void
    {
        config(['station.setup_complete' => true]);

        $this->get('/setup')->assertRedirect(route('login'));
        $this->get('/login')->assertOk();
    }
}
