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
            // THIS might behave weirdly if there are multiple identical workers on forge?
            if ($match = $forgeWorkers->first(fn (Worker $forge) => $this->equivalent($server, $forge, $worker))) {
                $forgeWorkers->forget($match->id);

                return true;
            }
        })->map(function (array $worker) use ($server) {
            dump('creating local worker not found on Forge:');
            dump(array_merge($this->config->defaultWorker($server), $worker));
            // create new workers
        });

        $forgeWorkers->map(function (Worker $worker) {
            dump('deleting Forge worker not found locally:');
            dump($worker);
        });

        // Create webhooks not on Forge
        // $webhooks->diff(collect($webhooksOnForge)->map(function (Webhook $webhook) {
        //     return $webhook->url;
        // }))->map(function ($url) use ($server, $site, $output) {
        //     $output->writeln("Creating webhook: {$url}");
        //     $this->forge->createWebhook($server->id, $site->id, [
        //         'url' => $url,
        //     ]);
        // });

        // // Delete webhooks on Forge but removed locally
        // $deleteWebhooks = collect($webhooksOnForge)
        //     ->reject(function (Webhook $webhook) use ($webhooks) {
        //         return $webhooks->contains($webhook->url);
        //     });

        // if (! $force && $deleteWebhooks->isNotEmpty()) {
        //     $output->warning("Skipping the deletion of {$deleteWebhooks->count()} Webhooks. \nUse --force to delete them.");

        //     return;
        // }

        // $deleteWebhooks->map(function (Webhook $webhook) use ($server, $site, $output) {
        //     $output->writeln("Deleting webhook: {$webhook->url}");
        //     $this->forge->deleteWebhook($server->id, $site->id, $webhook->id);
        // });
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

    // protected function defaultWorkerPayload(): array
    // {
    //     return [
    //         'connection' => 'redis',
    //         'queue' => 'default',
    //         'timeout' => 60,
    //         'sleep' => 10,
    //         'delay' => 0,
    //         'tries' => null,
    //         'environment' => null,
    //         'daemon' => false,
    //         'force' => false,
    //         'php_version' => 'php', // Will use server's default PHP CLI version
    //         'processes' => 1,
    //     ];
    // }
}
