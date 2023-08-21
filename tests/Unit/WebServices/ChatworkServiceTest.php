<?php

namespace Tests\Unit\WebServices;

use App\WebServices\ChatworkService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ChatworkServiceTest extends TestCase
{
    public function test_can_get_personal_info(): void
    {
        $chatworkService = $this->app->make(ChatworkService::class);
        $members = $chatworkService->me();

        $this->assertInstanceOf(Collection::class, $members);
        $this->assertTrue($members->isNotEmpty());
    }

    public function test_can_get_list_rooms(): void
    {
        $chatworkService = $this->app->make(ChatworkService::class);
        $members = $chatworkService->getRooms();

        $this->assertInstanceOf(Collection::class, $members);
        $this->assertTrue($members->isNotEmpty());
    }
}
