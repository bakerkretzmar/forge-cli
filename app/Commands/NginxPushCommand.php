<?php

namespace App\Commands;

use App\Support\Configuration;
use Laravel\Forge\Forge;
use Throwable;

class NginxPushCommand extends ForgeCommand
{
    protected $name = 'nginx:push';

    protected $description = 'Push the Nginx config file to Forge.';

    public function handle(Forge $forge): int
    {
        $nginx = "nginx-forge-{$this->environment}.conf";

        if (! file_exists($nginx)) {
            $this->error("The `{$nginx}` file does not exist.");

            return static::FAILURE;
        }

        try {
            $forge->updateSiteNginxFile(
                $this->config->get('server'),
                $this->config->get('id'),
                file_get_contents($nginx)
            );

            $this->info("Updated Nginx configuration file in {$this->emphasize($this->environment)} environment.");
        } catch (Throwable $e) {
            $this->error('Something went wrong: ');

            $this->error($e->getMessage());

            return static::FAILURE;
        }

        return static::SUCCESS;
    }
}
