<?php

namespace App\Commands;

use App\Support\Configuration;
use Laravel\Forge\Forge;

class NginxPullCommand extends ForgeCommand
{
    protected $name = 'nginx:pull';

    protected $description = 'Pull the Nginx config file from Forge.';

    public function handle(Forge $forge): int
    {
        $nginx = $forge->siteNginxFile(
            $this->config->get('server'),
            $this->config->get('id')
        );

        file_put_contents("nginx-forge-{$this->environment}.conf", $nginx);

        $this->info("Wrote Nginx config file to `nginx-forge-{$nginx}.conf`.");

        return static::SUCCESS;
    }
}
