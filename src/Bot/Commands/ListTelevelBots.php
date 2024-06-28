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

        $this->line('');
        $this->line('<fg=green;options=bold>Configured Bots:</>');
        $this->line('');

        foreach ($config as $bot => $settings) {
            $this->line("<fg=green>{$bot}</>:");
            $this->line("  Token: <fg=cyan>{$settings['token']}</>");
            $this->line("  Webhook URL: <fg=cyan>{$settings['webhook_url']}</>");
            $this->line('');
        }

        $this->line('');
    }
}
