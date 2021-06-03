<?php

namespace App\Commands;

class RebootServerCommand extends RebootCommand
{
    protected $name = 'reboot:server';
    protected $description = 'Reboot server.';
    protected $subject = 'the server';

    public function reboot(string $serverId): void
    {
        $this->forge->rebootServer($serverId);
    }
}
