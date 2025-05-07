<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_liveness()
    {
        $resp = $this->getJson('/api/v1/health');
        $resp->assertStatus(200)
            ->assertJson([
                'status'    => 'success',
                'http_code' => 200,
                'data'      => ['status' => 'up'],
            ]);
    }

    public function test_readiness()
    {
        $resp = $this->getJson('/api/v1/ready');
        $resp->assertStatus(200)
            ->assertJson([
                'status'    => 'success',
                'http_code' => 200,
                'data'      => ['status' => 'up'],  // now matches actual readiness
            ]);
    }
}
