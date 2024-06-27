<?php

namespace Televel\Bot\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class SetupTelevelBot extends Command
{
    protected $signature = 'televel:setup {bot?}';
    protected $description = 'Setup Televel Bot Configuration';

    public function handle()
    {
        $bot = $this->argument('bot');

        // Ask for bot name if not provided
        if (!$bot) {
            $bot = $this->ask('Enter the bot name (lowercase letters only), or press Enter for', 'default');
        }

        // Validate bot name if entered
        if ($bot !== 'default' && !$this->validateBotName($bot)) {
            $this->error("Invalid bot name. Bot name should contain only lowercase letters with no spaces, special characters, or digits.");
            return;
        }

        // Fetch bot token, always ask if not specified in .env
        $token = $this->ask('Enter the bot token for bot \'' . $bot . '\'');

        if (!$token) {
            $this->error("Bot token is required.");
            return;
        }

        $baseUrl = config('app.url') ?? env('APP_URL');
        $webhookUrl = rtrim($baseUrl, '/') . "/televel/{$bot}/{$token}";

        // Set the webhook for Telegram
        $response = $this->setTelegramWebhook($token, $webhookUrl);

        if (!$response['ok']) {
            $this->error("Failed to set webhook for bot '{$bot}'. Response: " . json_encode($response));
            return;
        }

        // Update the config
        $config = config('televel');
        $config['bots'][$bot] = [
            'token' => $token,
            'webhook_url' => $webhookUrl,
        ];
        file_put_contents(config_path('televel.php'), '<?php return ' . var_export($config, true) . ';');
        
        // Create the Televels directory in the app/Http folder if it doesn't exist
        $televelDir = app_path('Http/Televels');
        if (!File::exists($televelDir)) {
            File::makeDirectory($televelDir, 0755, true);
        }

        // Create a new controller for the bot
        $controllerTemplate = file_get_contents(__DIR__ . '/../Controllers/EchoBotController.php');
        $controllerClassName = ucfirst($bot) . 'TelevelController';
        $controllerTemplate = str_replace('EchoBotController', $controllerClassName, $controllerTemplate);
        $controllerTemplate = str_replace('EchoBot', ucfirst($bot), $controllerTemplate);
        $controllerTemplate = str_replace('default', $bot, $controllerTemplate);
        
        // Create a custom Televel class
        $televelTemplate = file_get_contents(__DIR__ . '/../Televels/EchoBotTelevel.php');
        $televelClassName = ucfirst($bot) . 'Televel';
        $televelTemplate = str_replace('EchoBotTelevel', $televelClassName, $televelTemplate);
        $controllerTemplate = str_replace('EchoBot', ucfirst($bot), $controllerTemplate);
        $televelTemplate = str_replace('default', $bot, $televelTemplate);

        // Ensure unique namespace path
        $controllerPath = app_path("Http/Controllers/{$controllerClassName}.php");
        if (File::exists($controllerPath)) {
            $this->error("Controller for bot '{$bot}' already exists.");
            return;
        }
        
        // Ensure unique namespace path
        $televelPath = app_path("Http/Televels/{$televelClassName}.php");
        if (File::exists($televelPath)) {
            $this->error("Televel for bot '{$bot}' already exists.");
            return;
        }

        File::put($controllerPath, $controllerTemplate);
        File::put($televelPath, $televelTemplate);

        $this->info("Configuration for bot '{$bot}' has been set up successfully.");
        $this->info("Webhook URL: {$webhookUrl}");
    }

    private function setTelegramWebhook($token, $webhookUrl)
    {
        $url = "https://api.telegram.org/bot{$token}/setWebhook";
        $response = Http::post($url, [
            'url' => $webhookUrl
        ]);

        return $response->json();
    }

    private function validateBotName($bot)
    {
        return preg_match('/^[a-z]+$/', $bot);
    }
}
