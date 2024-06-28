<?php

namespace Televel\Bot\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RemoveTelevelBot extends Command
{
    protected $signature = 'televel:remove';
    protected $description = 'Remove a configured bot';

    public function handle()
    {
        $config = config('televel.bots');
        $botNames = array_keys($config);

        if (empty($botNames)) {
            $this->error("No bots configured.");
            return;
        }

        $botName = $this->choice('Select the bot to remove. Press Enter for', $botNames, 0);

        if (!$this->confirm("Are you sure you want to remove bot '{$botName}'? It will remove all files related to bot from project!")) {
            $this->info("Operation cancelled.");
            return;
        }

        try {
            // Unset webhook from Telegram
            $token = $config[$botName]['token'];
            $response = $this->unsetTelegramWebhook($token);

            if (!$response['ok']) {
                Log::error("Failed to unset webhook for bot '{$botName}'. Response: " . json_encode($response));
                $this->warn("Failed to unset webhook for bot '{$botName}'. Telegram response: " . json_encode($response));
            } else {
                $this->info("Webhook is unset for the bot {$botName}");
            }

            // Remove from config
            unset($config[$botName]);
            file_put_contents(config_path('televel.php'), '<?php return ' . var_export(['bots' => $config], true) . ';');
            $this->info("Removed config record for the bot {$botName} from config/televel.php");
        } catch (\Exception $e) {
            $this->warn("Skipped removing configuration for bot '{$botName}' due to error: " . $e->getMessage());
        }

        try {
            // Remove the Televel file
            $televelFilePath = app_path("Http/Televels/" . ucfirst($botName) . "Televel.php");
            if (File::exists($televelFilePath)) {
                File::delete($televelFilePath);
                $this->info("Deleted Televel file: {$televelFilePath}");
            } else {
                $this->warn("Televel file not found: {$televelFilePath}");
            }
        } catch (\Exception $e) {
            $this->warn("Skipped removing Televel file for bot '{$botName}' due to error: " . $e->getMessage());
        }

        try {
            // Remove the controller file
            $controllerFilePath = app_path("Http/Controllers/" . ucfirst($botName) . "TelevelController.php");
            if (File::exists($controllerFilePath)) {
                File::delete($controllerFilePath);
                $this->info("Deleted Controller file: {$controllerFilePath}");
            } else {
                $this->warn("Controller file not found: {$controllerFilePath}");
            }
        } catch (\Exception $e) {
            $this->warn("Skipped removing Controller file for bot '{$botName}' due to error: " . $e->getMessage());
        }

        $this->info("Bot '{$botName}' has been removed successfully.");
    }

    private function unsetTelegramWebhook($token)
    {
        $url = "https://api.telegram.org/bot{$token}/deleteWebhook";
        $response = Http::get($url);

        return $response->json();
    }
}
