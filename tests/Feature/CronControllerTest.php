<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CronControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $validSecret = 'testsecret123';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.cron_secret' => $this->validSecret]);
    }

    // ─── processQueue ─────────────────────────────────────────────────────────

    public function test_process_queue_returns_403_for_wrong_secret(): void
    {
        $this->getJson('/cron/wrongsecret/queue')
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
    }

    public function test_process_queue_returns_success_for_correct_secret(): void
    {
        Artisan::shouldReceive('call')
            ->once()
            ->with('queue:work', [
                '--stop-when-empty' => true,
                '--tries' => 3,
                '--max-time' => 50,
            ]);

        Artisan::shouldReceive('output')->once()->andReturn('');

        $this->getJson("/cron/{$this->validSecret}/queue")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Queue processed',
            ]);
    }

    public function test_process_queue_returns_403_when_secret_is_empty_string(): void
    {
        // Empty string doesn't match config value
        config(['app.cron_secret' => 'somevalue']);

        $this->getJson('/cron/a/queue') // minimal valid route param
            ->assertStatus(403);
    }

    // ─── runCommand ───────────────────────────────────────────────────────────

    public function test_run_command_returns_403_for_wrong_secret(): void
    {
        $this->getJson('/cron/badsecret/run/optimize')
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
    }

    public function test_run_command_returns_400_for_disallowed_command(): void
    {
        $this->getJson("/cron/{$this->validSecret}/run/unknown-cmd")
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Command not allowed',
            ]);
    }

    public function test_run_command_allows_migrate(): void
    {
        Artisan::shouldReceive('call')->once()->with('migrate --force');
        Artisan::shouldReceive('output')->once()->andReturn('Nothing to migrate.');

        $this->getJson("/cron/{$this->validSecret}/run/migrate")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Command executed: migrate',
            ])
            ->assertJsonPath('data.output', 'Nothing to migrate.');
    }

    public function test_run_command_allows_optimize(): void
    {
        Artisan::shouldReceive('call')->once()->with('optimize');
        Artisan::shouldReceive('output')->once()->andReturn('');

        $this->getJson("/cron/{$this->validSecret}/run/optimize")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Command executed: optimize',
            ]);
    }

    public function test_run_command_allows_cache_clear(): void
    {
        Artisan::shouldReceive('call')->once()->with('cache:clear');
        Artisan::shouldReceive('output')->once()->andReturn('');

        $this->getJson("/cron/{$this->validSecret}/run/cache-clear")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Command executed: cache-clear',
            ]);
    }

    public function test_run_command_allows_route_clear(): void
    {
        Artisan::shouldReceive('call')->once()->with('route:clear');
        Artisan::shouldReceive('output')->once()->andReturn('');

        $this->getJson("/cron/{$this->validSecret}/run/route-clear")
            ->assertOk();
    }

    public function test_run_command_allows_config_clear(): void
    {
        Artisan::shouldReceive('call')->once()->with('config:clear');
        Artisan::shouldReceive('output')->once()->andReturn('');

        $this->getJson("/cron/{$this->validSecret}/run/config-clear")
            ->assertOk();
    }

    public function test_run_command_allows_storage_link(): void
    {
        Artisan::shouldReceive('call')->once()->with('storage:link');
        Artisan::shouldReceive('output')->once()->andReturn('');

        $this->getJson("/cron/{$this->validSecret}/run/storage-link")
            ->assertOk();
    }

    public function test_run_command_returns_500_with_error_message_on_exception(): void
    {
        Artisan::shouldReceive('call')
            ->once()
            ->with('optimize')
            ->andThrow(new \Exception('Optimization failed'));

        $this->getJson("/cron/{$this->validSecret}/run/optimize")
            ->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Command failed: Optimization failed',
            ]);
    }

    public function test_run_command_does_not_allow_arbitrary_artisan_commands(): void
    {
        // 'db:seed' is not in the allowed list
        $this->getJson("/cron/{$this->validSecret}/run/db-seed")
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Command not allowed',
            ]);
    }
}