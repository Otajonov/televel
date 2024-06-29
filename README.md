  Televel Package for Laravel

Televel Package for Laravel
===========================

Introduction
------------

Televel is a Laravel package designed to simplify the setup and management of Telegram bots within your Laravel application. It provides commands to set up, manage, and interact with Telegram bots via webhooks.

Installation
------------

To install Televel, follow these steps:

1. Install the package via Composer:

    composer require otajonov/televel

2. Publish the package configuration and Provider files:

    php artisan vendor:publish --provider="Televel\TelevelServiceProvider" --tag="config"

This will publish the configuration file to `config/televel.php`, where you can configure your Telegram bots.

3. Make sure your APP_URL in `.env` file is set correct, and is `https`.
   
4. Run `php artisan televel:setup` and provide your Bot Token
   
5. Congrats. Your Bot should be already running. /start it and happy coding!


Commands
--------

### Setup Command

The setup command (`televel:setup`) or (`televel:setup botname`) allows you to configure a new Telegram bot within your Laravel application. It prompts for the bot name and token and sets up the necessary webhook URL for Telegram updates.

Example usage:

    php artisan televel:setup

### List Command

The list command (`televel:list`) lists all configured Telegram bots along with their tokens and webhook URLs.

Example usage:

    php artisan televel:list

### Remove Command

The remove command (`televel:remove`) removes a configured Telegram bot from your Laravel application. It deletes the webhook, removes configuration entries, and deletes associated files.

Example usage:

    php artisan televel:remove

### Reset Command

The reset command (`televel:reset`) resets the webhook URL for a configured Telegram bot. It deletes the existing webhook, sets a new one with a new token, and updates the configuration.

Example usage:

    php artisan televel:reset

Usage
-----

### Controller

Once set up, incoming updates from Telegram are handled by the webhook method in your newly generated YourBotController.php file in `app/Http/Controllers` folder.

    

Extending Televel class
---------------------

You can further customize your Telegram bot by extending the Televel class and adding additional methods for handling specific bot functionalities.

    <?php
    
    namespace App\Http\Televels;
    
    use Televel\Bot\Televel; // Import Televel if needed
    
    class EchoBotTelevel extends Televel
    {
        /**
         * Example method to send a message using the bot.
         *
         * @param int $chatId The chat ID where the message should be sent.
         * @param string $message The message to send.
         * @return object Response from Telegram API.
         */
        public function sendMessage($chatId, $message)
        {
            // Implement your custom logic here to send messages via Telegram API
            // Example: return $this->post('sendMessage', ['chat_id' => $chatId, 'text' => $message]);
        }
    }
    

License
-------

This package is open-source software licensed under the MIT license.

_Feel free to contribute the project if you want_
