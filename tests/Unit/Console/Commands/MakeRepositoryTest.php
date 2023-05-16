<?php

namespace Tests\Unit\Console\Commands;

use Tests\TestCase;

class MakeRepositoryTest extends TestCase
{
    public function test_create_repository_file(): void
    {
        $this->artisan('app:make-repository', ['repository' => 'TestRepository', '--model' => 'User', '--test' => true]);
        $this->assertTrue(file_exists(app_path('Repositories/Eloquents/TestRepository.php')));
        $this->assertTrue(file_exists(app_path('Repositories/Contracts/TestRepository.php')));
    }

    protected function tearDown(): void
    {
        unlink(app_path('Repositories/Eloquents/TestRepository.php'));
        unlink(app_path('Repositories/Contracts/TestRepository.php'));

        $path = app_path('Providers/RepositoryServiceProvider.php');
        $fileContent = file($path);
        $contentToCheck = '\App\Repositories\Contracts\\TestRepository::class => \App\Repositories\\Eloquents\\TestRepository::class,';

        foreach ($fileContent as $line => $content) {
            if (str_contains($content, $contentToCheck)) {
                $fileContent[$line] = '';
                break;
            }
        }

        file_put_contents($path, $fileContent);
    }
}
