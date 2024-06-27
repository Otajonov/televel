<?php

namespace Televel\Bot\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RemoveTelevelBot extends Command
{
    protected $signature = 'televel:remove {bot?}';
    protected $description = 'Remove a configured bot';

    public function handle()
    {
        $bot = $this->argument('bot');
        $config = config('televel');
        
        // Ask for bot name if not provided
        if (!$bot) {
            $bot = $this->ask('Enter the bot name to remove, or press Enter for', 'default');
        }

        if (!isset($config['bots'][$bot])) {
            $this->error("Bot '{$bot}' is not configured.");
            return;
        }

        try {
            // Unset webhook from Telegram
            $token = $config['bots'][$bot]['token'];
            $response = $this->unsetTelegramWebhook($token);

            if (!$response['ok']) {
                Log::error("Failed to unset webhook for bot '{$bot}'. Response: " . json_encode($response));
                $this->warn("Failed to unset webhook for bot '{$bot}'. Telegram response: " . json_encode($response));
            }

            // Remove from config
            unset($config['bots'][$bot]);
            file_put_contents(config_path('televel.php'), '<?php return ' . var_export($config, true) . ';');
            $this->info("Removed config record for the bot {$bot} from config/televel.php");
        } catch (\Exception $e) {
            $this->warn("Skipped removing configuration for bot '{$bot}' due to error: " . $e->getMessage());
        }

        try {
            // Remove the Televel file
            $televelFilePath = app_path("Http/Televels/" . ucfirst($bot) . "Televel.php");
            if (File::exists($televelFilePath)) {
                File::delete($televelFilePath);
                $this->info("Deleted Televel file: {$televelFilePath}");
            } else {
                $this->warn("Televel file not found: {$televelFilePath}");
            }
        } catch (\Exception $e) {
            $this->warn("Skipped removing Televel file for bot '{$bot}' due to error: " . $e->getMessage());
        }

        try {
            // Remove the controller file
            $controllerFilePath = app_path("Http/Controllers/" . ucfirst($bot) . "TelevelController.php");
            if (File::exists($controllerFilePath)) {
                File::delete($controllerFilePath);
                $this->info("Deleted controller file: {$controllerFilePath}");
            } else {
                $this->warn("Controller file not found: {$controllerFilePath}");
            }
        } catch (\Exception $e) {
            $this->warn("Skipped removing Controller file for bot '{$bot}' due to error: " . $e->getMessage());
        }

        $this->info("Bot '{$bot}' has been removed successfully.");
    }

    private function unsetTelegramWebhook($token)
    {
        $url = "https://api.telegram.org/bot{$token}/deleteWebhook";
        $response = Http::get($url);

        return $response->json();
    }
}
