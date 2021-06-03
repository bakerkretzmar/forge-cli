<?php

namespace App\Commands;

class RebootMySQLCommand extends RebootCommand
{
    protected $name = 'reboot:mysql';
    protected $description = 'Reboot MySQL.';
    protected $subject = 'MySQL';

    public function reboot(string $serverId): void
    {
        $this->forge->rebootMysql($serverId);
    }
}
