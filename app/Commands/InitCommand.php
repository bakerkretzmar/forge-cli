<?php

namespace App\Commands;

use App\Commands\Concerns\NeedsForgeToken;
use App\Support\Configuration;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;
use LaravelZero\Framework\Commands\Command;

class InitCommand extends Command
{
    use NeedsForgeToken;

    const PROJECT_TYPES = [
        'php' => 'General PHP/Laravel Application.',
        'html' => 'Static HTML site.',
        'symfony' => 'Symfony Application.',
        'symfony_dev' => 'Symfony (Dev) Application.',
        'symfony_four' => 'Symfony >4.0 Application.',
    ];

    /** @var Forge */
    protected $forge;

    protected $signature = 'init {environment=production}';

    protected $description = 'Initialize a new app ready to get deployed on Laravel Forge';

    /**
     * @param Forge $forge
     * @param Configuration $configuration
     */
    public function handle(Forge $forge, Configuration $configuration)
    {
        $this->ensureHasToken();

        $this->forge = $forge;

        $servers = $forge->servers();

        $selectedServer = $this->menu('Which server do you want to use?', collect($servers)->map(function (Server $server) {
            return "{$server->name} - [{$server->id}]";
        })->toArray())->open();

        if (is_null($selectedServer)) {
            return static::FAILURE;
        }

        $server = $servers[$selectedServer];

        $linkSite = $this->confirm('Do you want to link this directory to an existing site?');

        if ($linkSite) {
            $sites = $forge->sites($server->id);

            $selectedSite = $this->menu('Which site do you want to link this project to?', collect($sites)->map(function (Site $site) {
                return "{$site->name} - [{$site->id}]";
            })->toArray())->open();

            if (is_null($selectedSite)) {
                return static::FAILURE;
            }

            $site = $sites[$selectedSite];
        } else {
            $site = $this->createSite($server);
        }

        $configuration->initialize($this->argument('environment'), $server, $site);

        $this->info('The project was successfully initialized.');
    }

    protected function createSite(Server $server)
    {
        $domain = $this->ask('What is the domain of your project?', basename(getcwd()));

        $selectedProjectType = $this->menu('What is your project type?', static::PROJECT_TYPES)->open();

        $directory = $this->ask('What is the public directory of your project?', '/public');

        if (is_null($selectedProjectType)) {
            return static::FAILURE;
        }

        $this->info('Creating site on Forge');

        return $this->forge->createSite($server->id, [
            'domain' => $domain,
            'project_type' => $selectedProjectType,
            'directory' => $directory,
        ]);
    }
}
