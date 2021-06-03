<?php

namespace App\Commands;

use App\Support\Configuration;
use Laravel\Forge\Forge;
use Symfony\Component\Console\Input\InputOption;

class LogsCommand extends ForgeCommand
{
    protected $name = 'logs';

    protected $description = 'View server log files.';

    protected $help = <<<TXT
        Available file types:
          - nginx_access
          - nginx_error
          - database
          - php7x (where x is a valid version number, e.g. php71 or php80)
        TXT;

    public function handle(Forge $forge)
    {
        $serverId = $this->config->get('server');
        $file = $this->option('file');

        $log = $forge->get("servers/{$serverId}/logs?file={$file}");

        $this->info("Log file: {$log['path']}");

        $this->info($log['content']);

        return static::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            new InputOption('file', null, InputOption::VALUE_REQUIRED, 'Log file type to retrieve.', 'nginx_error')
        ];
    }
}
