<?php

namespace App\Commands;

class RebootPostgresCommand extends RebootCommand
{
    protected $name = 'reboot:postgres';
    protected $description = 'Reboot Postgres.';
    protected $subject = 'Postgres';

    public function reboot(string $serverId): void
    {
        $this->forge->rebootPostgres($serverId);
    }
}
