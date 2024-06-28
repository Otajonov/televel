<?php


namespace Televel\Bot\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ResetWebhook extends Command
{
    protected $signature = 'televel:reset';
    protected $description = 'Reset the webhook URL for a configured bot without deleting config and other files.';

    public function handle()
    {
        $config = config('televel.bots');
        $botNames = array_keys($config);

        if (empty($botNames)) {
            $this->error("No bots configured.");
            return;
        }

        $botName = $this->choice('Select the bot to reset webhook. Press enter for', $botNames, 0);

        $newToken = $this->ask("Enter the new token for bot '{$botName}'");

        if (!$newToken) {
            $this->error("Bot token is required.");
            return;
        }

        $oldToken = $config[$botName]['token'];

        // Delete the existing webhook
        $deleteResponse = $this->unsetTelegramWebhook($oldToken);
        if (!$deleteResponse['ok']) {
            $this->warn("Failed to delete existing webhook for bot '{$botName}'. Response: " . json_encode($deleteResponse));
        } else {
            $this->info("Old webhook for bot '{$botName}' has been unset successfully.");
        }
        

        $baseUrl = config('app.url') ?? env('APP_URL');
        $webhookUrl = rtrim($baseUrl, '/') . "/televel/{$botName}/{$newToken}";

        // Set the new webhook
        $setResponse = $this->setTelegramWebhook($newToken, $webhookUrl);
        if (!$setResponse['ok']) {
            $this->error("Failed to set new webhook for bot '{$botName}'. Response: " . json_encode($setResponse));
            return;
        }

        // Update the config
        $config[$botName]['token'] = $newToken;
        $config[$botName]['webhook_url'] = $webhookUrl;
        file_put_contents(config_path('televel.php'), '<?php return ' . var_export(['bots' => $config], true) . ';');

        $this->info("New webhook for bot '{$botName}' has been set successfully.");
        $this->info("New Webhook URL: {$webhookUrl}");
    }

    private function setTelegramWebhook($token, $webhookUrl)
    {
        $url = "https://api.telegram.org/bot{$token}/setWebhook";
        $response = Http::post($url, [
            'url' => $webhookUrl
        ]);

        return $response->json();
    }

    private function unsetTelegramWebhook($token)
    {
        $url = "https://api.telegram.org/bot{$token}/deleteWebhook";
        $response = Http::get($url);

        return $response->json();
    }
}
