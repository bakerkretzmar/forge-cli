<?php

namespace App\Commands;

use App\Support\Configuration;
use Laravel\Forge\Forge;

class PushNginxCommand extends ForgeCommand
{
    protected $signature = 'nginx:push {environment=production}';

    protected $description = 'Push the Nginx config file to Forge';

    /**
     * @param Forge $forge
     * @param Configuration $configuration
     * @return int
     */
    public function handle(Forge $forge, Configuration $configuration)
    {
        if (! $this->ensureHasToken()) {
            return 1;
        }
        if (! $this->ensureHasForgeConfiguration()) {
            return 1;
        }

        $environment = $this->argument('environment');
        $filename = "nginx-forge-{$environment}.conf";

        if (! file_exists($filename)) {
            $this->error("The {$filename} file does not exist.");
            exit();
        }

        $siteId = $configuration->get($environment, 'id');

        try {
            $forge->updateSiteNginxFile(
                $configuration->get($environment, 'server'),
                $configuration->get($environment, 'id'),
                file_get_contents($filename)
            );

            $this->info("Successfully updated the Nginx configuration on Forge ({$environment}).");
        } catch (\Exception $e) {
            $this->error('Something went wrong: ');
            $this->error($e->getMessage());
        }
    }
}
