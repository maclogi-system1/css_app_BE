<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-repository
        {repository?}
        {--m|model= : Model Class Name}
        {--r|repo=Eloquents : Repo Name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make repository file';

    /**
     * @var string
     */
    protected $rootNamespace = 'App\Repositories';

    /**
     * @var string
     */
    protected $rootNamespaceModel = 'App\Models';

    /**
     * @var string
     */
    protected $repositoryName;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->makeFolderRepositories();

        $model = $this->option('model') ?? '';
        $modelClass = $this->rootNamespaceModel . '\\' . $model;
        $repositoryPath = app_path("Repositories/{$this->getRepo()}/{$this->getRepositoryName()}.php");

        if (file_exists($repositoryPath)) {
            $this->error("{$this->getRepositoryName()}.php file is exists");

            return Command::FAILURE;
        }

        [$contractStubPath, $repositoryStubPath] = $this->getStubPath($modelClass);

        $repositoryDir = app_path("Repositories/{$this->getRepo()}");
        if (! is_dir($repositoryDir)) {
            mkdir($repositoryDir, 0777);
        }

        $this->createRepository($repositoryPath, $repositoryStubPath, $model, $modelClass);

        $contractDir = app_path("Repositories/Contracts");
        if (! is_dir($contractDir)) {
            mkdir($contractDir, 0777);
        }

        $this->createContract($contractStubPath);

        $this->updateServiceProvider();

        $this->info(sprintf(
            'Repository [%s] created successfully',
            "app/Repositories/{$this->getRepo()}/{$this->getRepositoryName()}.php"
        ));

        return Command::SUCCESS;
    }

    private function makeFolderRepositories(): void
    {
        $dir = app_path('Repositories');

        if (! is_dir($dir)) {
            mkdir($dir, 0777);
        }
    }

    /**
     * Create a new file Repository.
     */
    private function createRepository(
        string $path,
        string $stubPath,
        string $model,
        string $modelClass
    ): void {
        $file = fopen($path, 'w+');
        $repositoryContent = file_get_contents($stubPath);
        $repositoryContent = str_replace([
            '{{ namespace }}',
            '{{ model }}',
            '{{ class }}',
            '{{ model_basename }}',
        ], [
            $this->rootNamespace."\\".$this->getRepo(),
            $modelClass ?? '',
            $this->getRepositoryName(),
            $model ?? '',
        ], $repositoryContent);

        fwrite($file, $repositoryContent);
        fclose($file);
    }

    /**
     * Create a new file Contract.
     */
    private function createContract(string $stubPath): void
    {
        $file = fopen(app_path("Repositories/Contracts/{$this->getRepositoryName()}.php"), 'w+');
        $contractContent = file_get_contents($stubPath);
        $contractContent = str_replace([
            '{{ class }}',
        ], [
            $this->getRepositoryName(),
        ], $contractContent);

        fwrite($file, $contractContent);
        fclose($file);
    }

    /**
     * Get stub path.
     */
    private function getStubPath(string $modelClass): array
    {
        $repositoryPath = base_path('stubs/repository.stub');
        $contractPath = base_path('stubs/repository.contract.stub');

        if (class_exists($modelClass)) {
            $repositoryPath = base_path('stubs/repository.model.stub');
        }

        return [$contractPath, $repositoryPath];
    }

    /**
     * Get directory name of Repository.
     */
    private function getRepo(): string
    {
        return $this->option('repo') ?? 'Eloquents';
    }

    /**
     * Get repository name.
     */
    private function getRepositoryName(): string
    {
        if (! ($repository = $this->argument('repository'))) {
            if (! $this->repositoryName) {
                $this->repositoryName = $this->ask('What should the repository be named?');
            }

            return $this->repositoryName;
        }

        return $repository;
    }

    private function updateServiceProvider()
    {
        $path = app_path('Providers/RepositoryServiceProvider.php');
        $fileContent = file($path);
        $contentStart = 0;
        $repo = $this->getRepo();
        $name = $this->getRepositoryName();

        foreach ($fileContent as $line => $content) {
            if (str_contains($content, 'protected $repositories = [')) {
                $contentStart = $line;
            } elseif ($contentStart != 0 && str_contains($content, '];')) {
                $fileContent[$line] = <<<EOT
                        \App\Repositories\Contracts\\$name::class => \App\Repositories\\$repo\\$name::class,
                    ];

                EOT;
                break;
            }
        }

        file_put_contents($path, $fileContent);
    }
}
