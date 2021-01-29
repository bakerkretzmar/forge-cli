<?php

namespace App\Commands;

use App\Commands\Concerns\EnsureHasForgeConfiguration;
use App\Commands\Concerns\NeedsForgeToken;
use LaravelZero\Framework\Commands\Command;

abstract class ForgeCommand extends Command
{
    use NeedsForgeToken;
    use EnsureHasForgeConfiguration;
}
