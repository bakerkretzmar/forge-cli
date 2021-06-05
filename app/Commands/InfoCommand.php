<?php

namespace App\Commands;

use App\Support\Configuration;
use Laravel\Forge\Forge;
use Symfony\Component\Console\Helper\TableCell;

class InfoCommand extends ForgeCommand
{
    protected $name = 'info';

    protected $description = 'Show information about a linked site on Forge.';

    public function handle(Forge $forge): int
    {
        $server = $forge->server($this->config->get('server'));
        $site = $forge->site($server->id, $this->config->get('id'));

        $this->table([new TableCell('Server Details', ['colspan' => 2])], [
            ['Server', $server->name],
            ['IP', $server->ipAddress],
            ['Type', $server->type],
            ['Site', $site->name],
            ['Directory', $site->directory],
            // @todo more info! e.g. php versions, mysql versions, provider, etc. sections? list all sites?
        ]);

        return static::SUCCESS;
    }
}
