<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-service {service} {--m|model= : Model Class Name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create service files with available content easily';

    /**
     * @var string
     */
    protected $rootNamespace = 'App\Services';

    /**
     * @var string
     */
    protected $rootNamespaceModel = 'App\Models';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $model = $this->option('model');
        $modelClass = $this->rootNamespaceModel . '\\' . $model;
        $service = $this->argument('service');
        $filePath = app_path("Services/{$service}.php");

        if (file_exists($filePath)) {
            $this->error("{$service}.php file is exists");

            return Command::FAILURE;
        }

        $file = fopen($filePath, 'w+');
        $stubContent = file_get_contents($this->getStubPath($modelClass));
        $stubContent = str_replace([
            '{{ namespace }}',
            '{{ model }}',
            '{{ class }}',
            '{{ model_basename }}',
        ], [
            $this->rootNamespace,
            $modelClass ?? '',
            $service,
            $model ?? '',
        ], $stubContent);

        fwrite($file, $stubContent);
        fclose($file);

        $this->info('Service created successfully');

        return Command::SUCCESS;
    }

    /**
     * Get stub path.
     */
    private function getStubPath(string $modelClass): string
    {
        $stubPath = base_path('stubs/service.stub');

        if (class_exists($modelClass)) {
            $stubPath = base_path('stubs/service.model.stub');
        }

        return $stubPath;
    }
}
