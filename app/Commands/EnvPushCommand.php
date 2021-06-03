<?php

namespace App\Commands;

use App\Support\Configuration;
use Laravel\Forge\Forge;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

class EnvPushCommand extends ForgeCommand
{
    protected $name = 'env:push';

    protected $description = 'Push up a local `.env` file to Forge.';

    public function handle(Forge $forge): int
    {
        $env = ".env.forge.{$this->environment}";

        if (! file_exists($env)) {
            $this->error("The `{$env}` file does not exist.");

            return static::FAILURE;
        }

        // reboot php?
        // enable/disable opcache?
        // secure site?
        // throw nice exception if passed an environment argument that doesn't exist
        try {
            $forge->updateSiteEnvironmentFile(
                $this->config->get('server'),
                $this->config->get('id'),
                file_get_contents($env)
            );

            $this->info("Updated `.env` file in {$this->emphasize($this->environment)} environment.");

            if ($this->option('delete')) {
                unlink($env);
            }
        } catch (Throwable $e) {
            $this->error('Something went wrong: ');

            $this->error($e->getMessage());

            return static::FAILURE;
        }

        return static::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            new InputOption('delete', null, InputOption::VALUE_NONE, 'Delete local file after pushing to Forge.'),
        ];
    }
}
