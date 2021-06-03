<?php

namespace App\Commands;

use App\Support\Configuration;
use Laravel\Forge\Forge;
use Symfony\Component\Console\Input\InputOption;

abstract class RebootCommand extends ForgeCommand
{
    protected Forge $forge;

    protected $subject;

    abstract public function reboot(string $serverId): void;

    public function handle(Forge $forge): int
    {
        $this->forge = $forge;

        $this->info("Rebooting {$this->subject} in {$this->emphasize($this->environment)} environment.");
        $this->info('This could take a few minutes and cause temporary downtime.');

        if (! $this->option('force') && $this->confirm('Are you sure?')) {
            $this->info("Rebooting {$this->subject}.");

            $this->reboot($this->config->get('server'));
        }

        return static::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            new InputOption('force', null, InputOption::VALUE_NONE, 'Reboot immediately without confirming.'),
        ];
    }
}
