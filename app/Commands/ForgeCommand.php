<?php

namespace App\Commands;

use App\Support\Configuration;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ForgeCommand extends Command
{
    protected string $environment;

    protected Configuration $config;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (blank(config('forge.token'))) {
            $this->error('No Forge API token found. Run `forge login` first.');

            return static::FAILURE;
        }

        if (! file_exists(getcwd() . '/forge.yml')) {
            $this->error('No `forge.yml` configuration file found. Run `forge init` first.');

            return static::FAILURE;
        }

        $this->environment = $this->argument('environment');
        $this->config = app(Configuration::class)->setEnvironment($this->environment);

        return parent::execute($input, $output);
    }

    protected function getArguments()
    {
        return [
            new InputArgument('environment', InputArgument::OPTIONAL, 'The environment to run the command in.', 'production'),
        ];
    }

    protected function emphasize(string $text): string
    {
        return "<options=bold;fg=blue>{$text}</options=bold;fg=blue>";
    }
}
