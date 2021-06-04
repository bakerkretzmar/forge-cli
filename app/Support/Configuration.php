<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Daemon;
use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;
use Laravel\Forge\Resources\Webhook;
use Symfony\Component\Yaml\Yaml;

class Configuration
{
    protected Forge $forge;

    protected ?string $environment = null;

    protected ?string $path = null;

    protected array $config = [];

    public function __construct(Forge $forge, string $environment = null)
    {
        $this->forge = $forge;
        $this->environment = $environment;

        try {
            $this->config = Yaml::parseFile($this->path());
        } catch (\Exception $e) {
        }
    }

    public function initialize(Server $server, Site $site)
    {
        // $workers = $this->forge->workers($server->id, $site->id);

        $this->config[$this->environment] = [
            'id' => $site->id,
            'name' => $site->name,
            'server' => $server->id,
            'quick-deploy' => $site->quickDeploy,
            'deployment' => $site->getDeploymentScript(),
            'webhooks' => $this->getWebhooks($server, $site),
            'daemons' => $this->getDaemons($server, $site),
            // 'workers' => $this->getWorkers($server, $site),
        ];

        $this->save();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->config, "{$this->environment}.{$key}", $default);
    }

    public function set(string $key, mixed $value): static
    {
        Arr::set($this->config, "{$this->environment}.{$key}", $value);

        return $this;
    }

    public function save(string $path = null): static
    {
        $flags = Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK;

        file_put_contents($path ?? $this->path(), Yaml::dump($this->config, 4, 2, $flags));

        return $this;
    }

    protected function getWebhooks(Server $server, Site $site): array
    {
        return collect($this->forge->webhooks($server->id, $site->id))->pluck('url')->values()->toArray();
    }

    protected function getDaemons(Server $server, Site $site)
    {
        return collect($this->forge->daemons($server->id))
            ->filter(function (Daemon $daemon) use ($site) {
                return Str::endsWith($daemon->command, " #{$site->id}");
            })
            ->map(function (Daemon $daemon) use ($site) {
                return [
                    'command' => Str::beforeLast($daemon->command, " #{$site->id}"),
                    'user' => $daemon->user,
                    'directory' => $daemon->directory,
                    'processes' => $daemon->processes,
                    'startsecs' => $daemon->startsecs,
                ];
            })->values()->toArray();
    }

    protected function path(): string
    {
        return $this->path ?: getcwd() . DIRECTORY_SEPARATOR . 'forge.yml';
    }

    public function setEnvironment(string $environment): static
    {
        $this->environment = $environment;

        return $this;
    }
}
