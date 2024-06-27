<?php

namespace Televel\Bot;

use Illuminate\Support\Facades\Http;

class Televel
{
    protected $bot;

    public function __construct($bot = 'default')
    {
        $this->bot = $bot;
    }

    public static function bot($bot = 'default')
    {
        return new static($bot);
    }

    protected function getConfig()
    {
        return config("televel.bots.{$this->bot}");
    }

    public function post($method, $params = [])
    {
        $config = $this->getConfig();

        $response = Http::post("https://api.telegram.org/bot{$config['token']}/{$method}", $params);
        return $response->json();
    }

    public function get($method, $params = [])
    {
        $config = $this->getConfig();

        $response = Http::get("https://api.telegram.org/bot{$config['token']}/{$method}", $params);
        return $response->json();
    }
}
