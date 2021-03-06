<?php

namespace App\Commands;

use App\Support\Configuration;
use Laravel\Forge\Forge;

class CreateDaemonCommand extends ForgeCommand
{
    protected $signature = 'daemon {environment=production}';

    protected $description = 'Create a new daemon on Laravel Forge';

    public function handle(Forge $forge, Configuration $configuration)
    {
        if (! $this->ensureHasToken()) {
            return 1;
        }
        if (! $this->ensureHasForgeConfiguration()) {
            return 1;
        }

        $environment = $this->argument('environment');

        $serverId = $configuration->get($environment, 'server');
        $siteId = $configuration->get($environment, 'id');

        $site = $forge->site($serverId, $siteId);

        $command = $this->ask('Which command do you want to run on your server');
        $user = $this->ask('Which user should run the command', 'forge');
        $directory = $this->ask('Which directory should the command run in', "/home/forge/{$site->name}");
        $processes = $this->ask('How many processes do you want to run', 1);
        $startsecs = $this->ask('Start seconds (The total number of seconds the program needs to stay running to consider the start successful.
)', 1);

        $daemons = $configuration->get($environment, 'daemons', []);
        $daemons[] = [
            'command' => $command,
            'user' => $user,
            'directory' => $directory,
            'processes' => $processes,
            'startsecs' => $startsecs,
        ];

        $configuration->set('daemons', $daemons);
        $configuration->save();

        $this->info('Successfully stored daemon in your forge.yml config file. You can push the configuration using "forge config:push".');
    }
}
