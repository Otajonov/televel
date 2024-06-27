<?php

namespace App\Http\Televels;

use Televel\Bot\Televel;


class EchoBotTelevel extends Televel
{
    
    public function __construct()
    {
        parent::__construct('default');
    }
    
    




    /**
     * Method to send a message to a chat.
     *
     * @param int $chatId Chat ID to send the message to.
     * @param string $text Message text to send.
     * @param string $parseMode Parse mode for the message (Markdown).
     * @return array Response from Telegram API.
     */
    public function sendMessage($chatId, $text, $parseMode = 'Markdown')
    {
        return $this->post('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ]);
    }
    
    
    
    

}
