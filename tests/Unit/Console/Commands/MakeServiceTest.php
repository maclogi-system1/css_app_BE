<?php

namespace Tests\Unit\Console\Commands;

use Tests\TestCase;

class MakeServiceTest extends TestCase
{
    public function test_create_service_file(): void
    {
        $this->artisan('app:make-service', ['service' => 'TestService', '--model' => 'User']);
        $this->assertTrue(file_exists(app_path('Services/TestService.php')));
    }

    protected function tearDown(): void
    {
        unlink(app_path('Services/TestService.php'));
    }
}
