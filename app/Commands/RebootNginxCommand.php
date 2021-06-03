<?php

namespace App\Commands;

class RebootNginxCommand extends RebootCommand
{
    protected $name = 'reboot:nginx';
    protected $description = 'Reboot Nginx.';
    protected $subject = 'Nginx';

    public function reboot(string $serverId): void
    {
        $this->forge->rebootNginx($serverId);
    }
}
