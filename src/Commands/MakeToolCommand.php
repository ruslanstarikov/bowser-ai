<?php

namespace Ruslanstarikov\BowserAi\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class MakeToolCommand extends Command
{
    protected $signature = 'make:tool {name : The tool class name in StudlyCase}';
    protected $description = 'Generate a new AI Tool class in app/Tools';

    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $path = app_path("Tools/{$name}.php");

        $this->filesystem->ensureDirectoryExists(app_path('Tools'));

        if ($this->filesystem->exists($path)) {
            if (!$this->confirm("The tool {$name} already exists. Overwrite?")) {
                return;
            }
        }

        $content = View::make('bowser-ai::code.ToolTemplate', [
            'class_name' => $name
        ])->render();

        $this->filesystem->put($path, $content);

        $this->info("Tool {$name} created successfully in app/Tools.");
    }
}