<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ModuleMakeModel extends Command
{

    protected $signature = 'module:model {name} {--module=} {args?*}';

    protected $description = 'Create custom classes in module';

    protected $className;
    protected $moduleName;
    protected $modelNamespace;
    protected $factoryNamespace;
    protected $seederNamespace;
    protected $saveDirectory;
    protected $namespaceDir;
    protected $modelUse;

    public function handle()
    {
        $this->getParameters();
        $this->setNamespaces();
        $this->createModel();
        $this->createOptionClasses();

    }

    protected function getParameters(): void
    {
        $this->moduleName = $this->option('module');
    }

    protected function setNamespaces(): void
    {
        $dirs = explode('/', $this->argument('name'));

        $this->className = Str::studly(array_pop($dirs));
        $dirs = implode('/', $dirs);

        $this->saveDirectory = empty($dirs)
            ? null
            : $dirs . '/';

        $this->namespaceDir = empty($dirs)
            ? $dirs = null
            : '\\' . $dirs;

        $this->modelNamespace = "{$this->moduleName}\Models" . $this->namespaceDir ?? '';
    }

    protected function createModel(): void
    {
        $stub = file_get_contents(base_path('stubs/model.stub'));

        $stub = $this->replaceNamespace($stub, $this->modelNamespace);
        $stub = $this->replaceClassName($stub, $this->className);

        $saveDir = "app/Modules/{$this->moduleName}/src/Models/{$this->saveDirectory}";
        $filename = "{$this->className}.php";
        $filepath = $saveDir . $filename;

        $this->fileExists($filepath);
        $this->dirExists($saveDir);

        file_put_contents($filepath, $stub);
        echo '---------------------------' . "\n";
        echo 'Model created - ' . "[$filepath]" . "\n";
    }

    protected function replaceNamespace($stub, $namespace): string
    {
        return str_replace(['{{ namespace }}', '{{namespace}}'], $namespace, $stub);
    }

    protected function replaceClassName($stub, $class): string
    {
        return str_replace(['{{ class }}', '{{class}}'], $class, $stub);
    }

    protected function replaceModelUse($stub, $modelUse): string
    {
        return str_replace(['{{ modelNamespace }}', '{{modelNamespace}}'], $modelUse, $stub);
    }

    protected function createOptionClasses(): void
    {
        array_map(function ($elem) {
            match ($elem) {
                'm' => $this->createMigration(),
                'f' => $this->createFactory(),
                's' => $this->createSeeder(),
                default => $this->relax(),
            };
        }, $this->arguments()['args']);

    }

    public function relax(): void
    {
        ;
    }

    protected function createMigration(): void
    {
        $stub = file_get_contents(base_path('stubs/model.migration.stub'));

        $table = Str::snake($this->className) . 's';

        $stub = str_replace(['{{table}}', '{{ table }}'], $table, $stub);

        $date = Carbon::now()->format('Y_m_d');

        $saveDir = "app/Modules/{$this->moduleName}/database/migrations/";
        $filename = $date . '_' . fake()->regexify('[0-9]{6}') . '_' . "create_{$table}_table.php";
        $filepath = $saveDir . $filename;

        $this->fileExists($filepath);
        $this->dirExists($saveDir);

        file_put_contents($filepath, $stub);
        echo "\n";
        echo 'Migration created - ' . "[$filepath]" . "\n";
        echo "\n";
    }

    protected function createFactory(): void
    {
        $stub = file_get_contents(base_path('stubs/model.factory.stub'));

        $this->factoryNamespace = "{$this->moduleName}\\Database\\Factories{$this->namespaceDir}";

        $this->modelUse = $this->modelNamespace . '\\' . $this->className;

        $stub = $this->replaceNamespace($stub, $this->factoryNamespace);
        $stub = $this->replaceClassName($stub, $this->className);
        $stub = $this->replaceModelUse($stub, $this->modelUse);

        $saveDir = "app/Modules/{$this->moduleName}/database/factories/{$this->saveDirectory}";
        $filename = "{$this->className}Factory.php";
        $filepath = $saveDir . $filename;

        $this->fileExists($filepath);
        $this->dirExists($saveDir);

        file_put_contents($filepath, $stub);
        echo "\n";
        echo 'Factory created - ' . "[$filepath]" . "\n";
        echo "\n";
    }

    protected function createSeeder(): void
    {
        $stub = file_get_contents(base_path('stubs/model.seeder.stub'));

        $this->seederNamespace = "{$this->moduleName}\\Database\\Seeders{$this->namespaceDir}";

        $this->modelUse = $this->modelNamespace . '\\' . $this->className;

        $stub = $this->replaceNamespace($stub, $this->seederNamespace);
        $stub = $this->replaceClassName($stub, $this->className);
        $stub = $this->replaceModelUse($stub, $this->modelUse);

        $saveDir = "app/Modules/{$this->moduleName}/database/seeders/{$this->saveDirectory}";
        $filename = "{$this->className}Seeder.php";
        $filepath = $saveDir . $filename;

        $this->fileExists($filepath);
        $this->dirExists($saveDir);

        file_put_contents($filepath, $stub);
        echo "\n";
        echo 'Seeder created - ' . "[$filepath]" . "\n";
        echo "\n";
    }

    protected function fileExists(string $filepath): void
    {
        if (file_exists($filepath)) {
            echo "\n";
            echo 'Файл уже существует !!!! - ' . $filepath . "\n";
            echo "\n";
            return;
        }
    }

    protected function dirExists(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

}
