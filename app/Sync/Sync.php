<?php

namespace App\Sync;

use App\Support\Configuration;
use Illuminate\Console\OutputStyle;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;

abstract class Sync
{
    public function __construct(
        protected Forge $forge,
        protected Configuration $config,
    ) {
    }

    abstract public function sync(Server $server, Site $site, OutputStyle $output, bool $force = false): void;
}
