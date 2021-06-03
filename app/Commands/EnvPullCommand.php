<?php

namespace App\Commands;

use App\Support\Configuration;
use Laravel\Forge\Forge;

class EnvPullCommand extends ForgeCommand
{
    protected $name = 'env:pull';

    protected $description = 'Pull down a `.env` file from Forge.';

    public function handle(Forge $forge): int
    {
        $env = $forge->siteEnvironmentFile(
            $this->config->get('server'),
            $this->config->get('id')
        );

        file_put_contents(".env.forge.{$this->environment}", $env);

        $this->info("Wrote environment file to `.env.forge.{$this->environment}`.");

        return static::SUCCESS;
    }
}
