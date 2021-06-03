<?php

namespace App\Commands;

use App\Support\Configuration;
use Laravel\Forge\Forge;

class ConfigPullCommand extends ForgeCommand
{
    protected $name = 'config:pull';

    protected $description = 'Pull down the configuration from Forge and store it in your `forge.yml` file.';

    public function handle(Forge $forge): int
    {
        $server = $forge->server($this->config->get('server'));
        $site = $forge->site($server->id, $this->config->get('id'));

        $this->config->initialize($this->environment, $server, $site);

        $this->info('Updated the Forge configuration file.');

        return static::SUCCESS;
    }
}
