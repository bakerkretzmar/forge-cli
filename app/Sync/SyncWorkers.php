<?php

namespace App\Sync;

use Closure;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Arr;
use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;
use Laravel\Forge\Resources\Worker;

class SyncWorkers extends Sync
{
    public function sync(Server $server, Site $site, Closure $output, bool $force = false): void
    {
        $workers = collect($this->config->get('workers', []));
        $forgeWorkers = collect($this->forge->workers($server->id, $site->id))->keyBy('id');

        // Create workers that are defined locally but do not exist on Forge
        $workers->reject(function (array $worker) use (&$forgeWorkers, $server, $site) {
            if ($match = $forgeWorkers->first(fn (Worker $forge) => $this->equivalent($server, $forge, $worker))) {
                // Remove each found worker from the list of 'unmatched' workers on Forge
                $forgeWorkers->forget($match->id);

                return true;
            }
        })->map(function (array $worker) use ($server, $site, $output) {
            $data = $this->getWorkerPayload($server, $worker);

            $output("Creating {$data['queue']} queue worker on {$this->emphasize($data['connection'])} connection...");

            $this->forge->createWorker($server->id, $site->id, $data);
        });

        if ($force) {
            $forgeWorkers->map(function (Worker $worker) use ($server, $site, $output) {
                $output("Deleting {$worker->queue} queue worker present on Forge but not listed locally...", 'warn');

                $this->forge->deleteWorker($server->id, $site->id, $worker->id);
            });
        } else {
            $output("Found {$forgeWorkers->count()} queue workers present on Forge but not listed locally.", 'warn');
            // @todo just `->confirm()` here
            $output('Run the command again with the `--force` option to delete them.');
        }
    }

    protected function equivalent(Server $server, Worker $worker, array $config): bool
    {
        // @todo cache this internally in config
        $defaults = $this->config->defaultWorker($server);

        $data = [
            'queue' => $worker->queue,
            'connection' => $worker->connection,
            'timeout' => $worker->timeout,
            'delay' => $worker->delay,
            'sleep' => $worker->sleep,
            'tries' => $worker->tries,
            'environment' => $worker->environment,
            'daemon' => (bool) $worker->daemon,
            'force' => (bool) $worker->force,
            'php' => str_replace('.', '', head(explode(' ', $worker->command))),
            'processes' => $worker->processes,
        ];

        foreach (array_merge($defaults, $config) as $key => $value) {
            if ($data[$key] !== $value) {
                return false;
            }
        }

        return true;
    }

    protected function getWorkerPayload(Server $server, array $worker): array
    {
        return array_merge(
            $data = array_merge($this->config->defaultWorker($server), $worker),
            ['php_version' => $data['php']],
        );
    }
}
