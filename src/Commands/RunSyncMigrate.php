<?php

namespace Hanan467\SyncMigrate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RunSyncMigrate extends Command
{
    protected $signature = 'migrate:sync';
    protected $description = 'Automatically resolve and run Laravel migrations in correct dependency order';

    public function handle()
    {
        $this->info("Scanning migrations...");

        $migrationPath = base_path('database/migrations');
        $migrationFiles = File::files($migrationPath);

        $graph = [];
        $fileMap = [];

        foreach ($migrationFiles as $file) {
            $filename = $file->getFilename();
            $contents = File::get($file->getRealPath());

            preg_match_all('/foreignId\([\'"](\w+)_id[\'"]\)->constrained\([\'"]?(\w+)?[\'"]?\)?/', $contents, $matches, PREG_SET_ORDER);

            $dependencies = [];
            foreach ($matches as $match) {
                $dependencies[] = $match[2] ?? Str::plural($match[1]);
            }

            $tableName = $this->extractTableName($contents);
            if ($tableName) {
                $graph[$tableName] = $dependencies;
                $fileMap[$tableName] = $file->getRealPath();
            }
        }

        $sorted = $this->topologicalSort($graph);

        if (empty($sorted)) {
            $this->error("Could not resolve dependencies (possible circular reference).");
            return;
        }

        foreach ($sorted as $table) {
            $file = $fileMap[$table] ?? null;
            if ($file) {
                $this->line("Running: $table");
                include_once $file;
                $class = $this->extractMigrationClass($file);
                if (class_exists($class)) {
                    (new $class)->up();
                }
            }
        }

        $this->info("✅ Migrations executed in correct order.");
    }

    protected function extractTableName($content)
    {
        if (preg_match("/Schema::create\(['\"](\w+)['\"]/", $content, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function extractMigrationClass($file)
    {
        $contents = File::get($file);
        if (preg_match('/class\s+(\w+)\s+extends/', $contents, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function topologicalSort($graph)
    {
        $sorted = [];
        $visited = [];

        $visit = function ($node) use (&$visit, &$sorted, &$visited, $graph) {
            if (isset($visited[$node])) {
                return;
            }

            $visited[$node] = true;

            foreach ($graph[$node] ?? [] as $neighbor) {
                $visit($neighbor);
            }

            $sorted[] = $node;
        };

        foreach (array_keys($graph) as $node) {
            $visit($node);
        }

        return array_reverse($sorted);
    }
}
