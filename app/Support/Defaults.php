<?php

namespace App\Support;

use Laravel\Forge\Resources\Server;

class Defaults
{
    public static function worker(string $php): array
    {
        return [
            'queue' => 'default', // Careful - defaults to blank if omitted
            'connection' => 'redis', // Required by Forge API
            'php' => $php, // Required by Forge API (but called 'php_version')
            'daemon' => false, // Required by Forge API
            'processes' => 1,
            'timeout' => 60, // Careful - defaults to 0 if omitted
            'sleep' => 10, // Required by Forge API
            'delay' => 0,
            'tries' => null,
            'environment' => null,
            'force' => false,
        ];
    }

    public static function phpVersionUsedOnCli(Server $server): array
    {
        $cli = collect($this->forge->phpVersions($server->id))->firstWhere('usedOnCli', true)->version;
    }
}
