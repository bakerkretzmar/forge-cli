<?php

namespace App\Commands;

use App\Support\Configuration;

class CreateWebhookCommand extends ForgeCommand
{
    protected $name = 'create:webhook';

    protected $description = 'Create a new Webhook on Forge.';

    public function handle(): int
    {
        $url = $this->ask('What is the URL of the webhook?');

        $webhooks = $this->config->get('webhooks', []);

        // @todo ->append()? or make it 'settable'?
        $webhooks[] = $url;

        // @todo ->append()? or make it 'settable'?
        $this->config->set('webhooks', $webhooks);
        $this->config->save();

        $this->info('Stored the webhook in your forge.yml config file. You can push the configuration using `forge config:push`.');
    }
}
