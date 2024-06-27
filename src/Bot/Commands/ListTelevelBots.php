<?php

namespace Televel\Bot\Commands;

use Illuminate\Console\Command;

class ListTelevelBots extends Command
{
    protected $signature = 'televel:list';
    protected $description = 'List all configured bots';

    public function handle()
    {
        $config = config('televel.bots');

        if (empty($config)) {
            $this->info('No bots configured.');
            return;
        }

        foreach ($config as $bot => $settings) {
            $this->info("Bot: {$bot}");
            $this->info("Token: {$settings['token']}");
            $this->info("Webhook URL: {$settings['webhook_url']}");
            $this->info('---');
        }
    }
}
