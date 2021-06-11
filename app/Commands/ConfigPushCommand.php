<?php

namespace App\Commands;

use App\Sync\DaemonSync;
use App\Sync\DeploymentScriptSync;
use App\Sync\SyncWorkers;
use App\Sync\WebhookSync;
use Laravel\Forge\Forge;
use Symfony\Component\Console\Input\InputOption;

class ConfigPushCommand extends ForgeCommand
{
    const SYNC_CLASSES = [
        WebhookSync::class,
        DeploymentScriptSync::class,
        DaemonSync::class,
    ];

    protected $name = 'config:push';

    protected $description = 'Push up the configuration from your `forge.yml` file to Forge.';

    public function handle(Forge $forge): int
    {
        $server = $forge->server($this->config->get('server'));
        $site = $forge->site($server->id, $this->config->get('id'));

        $output = fn (string $contents, string $level = 'info') => $this->{$level}($contents);

        app(SyncWorkers::class)->sync($server, $site, $output, $this->option('force'));
        // foreach (static::SYNC_CLASSES as $syncClass) {
        //     $this->info('Synchronizing ' . $syncClass);

        //     app($syncClass)->sync($this->environment, $server, $site, $this->getOutput(), $this->option('force'));
        // }

        $this->info('Done!');

        return static::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            new InputOption('force', null, InputOption::VALUE_NONE, '@todo.'),
        ];
    }
}
