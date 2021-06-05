<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Daemon;
use Laravel\Forge\Resources\PHPVersion;
use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class Configuration
{
    protected static ?string $environment = null;

    protected Forge $forge;
    protected ?string $path = null;
    protected array $config = [];

    public function __construct(Forge $forge, string $environment = null)
    {
        $this->forge = $forge;
        static::$environment ??= $environment;

        try {
            $this->config = Yaml::parseFile($this->path());
        } catch (Throwable $e) {
            throw new RuntimeException("Failed to parse `forge.yml` configuration file: {$e->getMessage()}");
        }
    }

    public function initialize(Server $server, Site $site)
    {
        $this->config[static::$environment] = [
            'id' => $site->id,
            'name' => $site->name,
            'server' => $server->id,
            'quick-deploy' => $site->quickDeploy,
            'deployment' => $site->getDeploymentScript(),
            'webhooks' => $this->getWebhooks($server, $site),
            'daemons' => $this->getDaemons($server, $site),
            'workers' => $this->getWorkers($server, $site),
        ];

        $this->save();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->config, static::$environment . ".{$key}", $default);
    }

    public function set(string $key, mixed $value): static
    {
        Arr::set($this->config, static::$environment . ".{$key}", $value);

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
        return data_get($this->forge->webhooks($server->id, $site->id), '*.url');
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

    protected function getWorkers(Server $server, Site $site)
    {
        $defaults = $this->defaultWorker($server);

        return collect($this->forge->workers($server->id, $site->id))->map(function ($worker) use ($defaults) {
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

            $nonDefaults = collect($data)->filter(fn ($value, $key) => $value !== $defaults[$key])->keys()->toArray();

            return Arr::only($data, ['queue', 'connection', ...$nonDefaults]);
        })->toArray();
    }

    protected function path(): string
    {
        return $this->path ?: getcwd() . DIRECTORY_SEPARATOR . 'forge.yml';
    }

    public static function setEnvironment(string $environment): void
    {
        static::$environment = $environment;
    }

    public static function environment(): ?string
    {
        return static::$environment;
    }

    public function defaultWorker(Server $server): array
    {
        $cli = collect($this->forge->phpVersions($server->id))->firstWhere('usedOnCli', true)->version;

        return [
            'queue' => 'default',
            'connection' => 'redis',
            'php' => $cli,
            'timeout' => 60,
            'processes' => 1,
            'sleep' => 10,
            'daemon' => false,
            'delay' => 0,
            'tries' => null,
            'environment' => null,
            'force' => false,
        ];
    }
}
