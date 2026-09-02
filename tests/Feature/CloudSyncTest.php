<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Services\CloudSync\CloudSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CloudSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('cloud_sync.enabled', true);
        Config::set('database.connections.mysql_cloud', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $this->artisan('migrate', ['--database' => 'mysql_cloud']);
    }

    public function test_customer_upserts_to_cloud_database(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Cloud Sync Customer',
            'customer_code' => 'CSC001',
        ]);

        app(CloudSyncService::class)->sync(Customer::class, $customer->id);

        $cloudRow = DB::connection('mysql_cloud')
            ->table('customers')
            ->where('id', $customer->id)
            ->first();

        $this->assertNotNull($cloudRow);
        $this->assertSame('Cloud Sync Customer', $cloudRow->name);
        $this->assertSame('CSC001', $cloudRow->customer_code);
    }

    public function test_cloud_sync_status_command_runs(): void
    {
        $this->artisan('cloud:sync-status')
            ->assertSuccessful();
    }
}
