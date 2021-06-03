<?php

namespace App\Commands;

use App\Support\Configuration;
use Laravel\Forge\Forge;

class CreateSSHCommand extends ForgeCommand
{
    protected $name = 'create:ssh';

    protected $description = 'Upload an SSH key to Forge.';

    public function handle(Forge $forge): int
    {
        $name = $this->ask('What should the key be named in Forge?', $_SERVER['USER'] . '@' . gethostname());

        $path = $this->ask('Where is the local public key file?', ($_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? __DIR__) . '/.ssh/id_rsa.pub');

        $username = $this->ask('What Forge user is the key for?', 'forge');

        if (! file_exists($path)) {
            $this->error("The `{$path}` file does not exist.");

            return static::FAILURE;
        }

        $forge->createSSHKey($this->config->get('server'), [
            'name' => $name,
            'key' => file_get_contents($path),
            'username' => $username,
        ]);

        $this->info('Key created.');

        return static::SUCCESS;
    }
}
