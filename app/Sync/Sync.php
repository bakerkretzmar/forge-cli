<?php

namespace App\Sync;

use App\Concerns\EmphasizesOutput;
use App\Support\Configuration;
use Closure;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;

abstract class Sync
{
    use EmphasizesOutput;

    public function __construct(
        protected Forge $forge,
        protected Configuration $config,
    ) {
    }

    abstract public function sync(Server $server, Site $site, Closure $output, bool $force = false): void;
}
