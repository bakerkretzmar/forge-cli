<?php

namespace App\Commands;

use Laravel\Forge\Forge;

class ConfigPullCommand extends ForgeCommand
{
    protected $name = 'config:pull';

    protected $description = 'Pull down the configuration from Forge and store it in your `forge.yml` file.';

    public function handle(Forge $forge): int
    {
        $this->config->initialize(
            $server = $forge->server($this->config->get('server')),
            $forge->site($server->id, $this->config->get('id'))
        );

        $this->info('Updated the Forge configuration file.');

        return static::SUCCESS;
    }
}
