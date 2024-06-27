<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

//use Televel\Bot\Televel; // Import Televel if needed
use App\Http\Televels\EchoBotTelevel; // Import EchoBotTelevel

class EchoBotController extends Controller
{
    public function webhook(Request $request, $token)
    {
        
        
        
        // $token is the Token that has received current update
        
        // Retrieve the bot configuration using the provided token
        $config = collect(config('televel.bots'))->firstWhere('token', $token);



        // Process the update from Telegram
        $update = $request->all();
        
        // $televel is now the method that you can use for further actions on behalf of the bot that received current update
       // $televel = Televel::bot('default');
        
        // You can use your custom extended Televel class as well even without passing bot name into it
        $defaultTelevel = new EchoBotTelevel();
        
        

        if (isset($update['message'])) {
            
            

            try {
                
                $chatId = $update['message']['chat']['id'];
                $message = "Post Update from Telegram:\n\n```\n" . json_encode($update['message'], JSON_PRETTY_PRINT) . "\n```";
                
                $res = $defaultTelevel->sendMessage($chatId, $message);

                // $res is a php object that contains the response from Telegram ex. message id that bot sent just now
                Log::info('Message sent on behalf of ' . json_encode($config) . ' Telegram response: ' . json_encode($res));
               
                
            } catch (\Exception $e) {
                // use try catch to reduce development stress :)
                Log::error('Failed to send message: ' . $e->getMessage());
            }
            
            
        }



        return response()->json(['status' => 'ok']); // return ok to Telegram, so that it knew you got the update.
        
    }
}
