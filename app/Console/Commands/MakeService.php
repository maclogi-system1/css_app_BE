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
    protected $signature = 'app:make-service {service?} {--m|model= : Model Class Name}';

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
     * @var string
     */
    protected $serviceName;

    /**
     * @var string
     */
    protected $filePath = 'Services/%s.php';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $model = $this->option('model');
        $modelClass = $this->rootNamespaceModel . '\\' . $model;
        $service = $this->getServiceName();
        $filePath = app_path($this->filePath);

        if (file_exists($filePath)) {
            $this->error("{$service}.php file is exists");

            return Command::FAILURE;
        } elseif (! is_dir($dir = str($filePath)->beforeLast('/')->toString())) {
            mkdir(directory: $dir, recursive: true);
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

        if ($this->rootNamespace == 'App\Services') {
            $stubContent = str_replace("\nuse App\\Services\\Service;", '', $stubContent);
        }

        fwrite($file, $stubContent);
        fclose($file);

        $this->info('Service created successfully');

        return Command::SUCCESS;
    }

    /**
     * Get service name.
     */
    private function getServiceName()
    {
        $service = $this->argument('service') ?? '';

        if (! $service) {
            if (! $this->serviceName) {
                $this->serviceName = $this->ask('What should the service be named?');
            }

            $service = $this->serviceName;
        }

        $this->filePath = sprintf($this->filePath, $service);

        if (str($service)->contains('/')) {
            $this->serviceName = str($service)->afterLast('/')->toString();
            $this->rootNamespace = str($service)->beforeLast('/')
                ->replace('/', '\\')
                ->prepend($this->rootNamespace.'\\')
                ->toString();

            return $this->serviceName;
        }

        return $service;
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
