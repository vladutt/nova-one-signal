<?php

namespace Yassi\OneSignal\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class OneSignalController extends BaseController
{

    /**
     * Api Key.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * App ID.
     *
     * @var string
     */
    protected $appId;

    /**
     * Players.
     *
     * @var array
     */
    protected $players;

    /**
     * Constructor method.
     */
    public function __construct()
    {
        $this->setApiKey(env('ONE_SIGNAL_API_KEY'))->setAppId(env('ONE_SIGNAL_APP_ID'));
    }

    /**
     * Get all authenticatables.
     *
     * @return  array
     */
    public function getAuthenticatables()
    {
        return $this->authenticatables()->get();
    }

    /**
     * Get authentictables builder.
     *
     * @return  Builder
     */
    private function authenticatables()
    {
        $authenticatable_class = config('onesignal.authenticatable_class') ?? \App\Authenticatable::class;
        return $authenticatable_class::whereIn('id', $this->getPlayers()->pluck('external_user_id'));
    }

    /**
     * Get included player ids by uahtnticatable id.
     *
     * @param  array  $players
     * @return  array
     */
    private function getIncludedPlayers(array $players)
    {
        return $this->authenticatables()->whereIn('id', collect($players)->pluck('id'))->get();
    }

    /**
     * Format the given text with the dynamic values.
     *
     * @param  Model  $player
     * @param  string  $text
     * @return  string
     */
    protected function formatText(Model $player, string $text)
    {
        $matches = [];

        preg_match_all('/::(\w+)::/', $text, $matches, PREG_SET_ORDER);

        foreach ($matches as [$full, $key]) {
            $text = str_replace($full, $player->$key, $text);
        }

        return $text;
    }

    /**
     * Send a notification.
     *
     * @return  void
     */
    public function send(Request $request)
    {
        $players = $this->getIncludedPlayers($request->players);

        foreach ($players as $player) {
            $params = [
                'include_player_ids' => $this->getPlayers()->where('external_user_id', $player->id)->pluck('id'),
                'contents' => [
                    'en' => $this->formatText($player, $request->message),
                ],
            ];

            if (isset($request->title)) {
                $params['headings'] = [
                    'en' => $this->formatText($player, $request->title),
                ];
            }

            if (isset($request->subtitle)) {
                $params['subtitle'] = [
                    'en' => $this->formatText($player, $request->subtitle),
                ];
            }

            $this->request('notifications', 'POST', $params);
        }
    }

    /**
     * Get API key.
     *
     * @return  string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set API key.
     *
     * @param  string  $apiKey
     * @return  self
     */
    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get app ID.
     *
     * @return  string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * Set app ID.
     *
     * @param  string  $appId
     * @return  self
     */
    public function setAppId(string $appId)
    {
        $this->appId = $appId;

        return $this;
    }

    /**
     * Get players.
     *
     * @return  string
     */
    public function getPlayers()
    {
        return $this->players ?? $this->setPlayers();
    }

    /**
     * Set the list of players
     *
     * @return  array
     */
    public function setPlayers()
    {
        $this->players = collect($this->request('players')->players);

        return $this->players;
    }

    /**
     * Send request.
     *
     * @param  string  $type
     * @param  string  $method
     * @return  array|object
     */
    private function request(string $type, string $method = 'GET', array $params = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/' . $type . '?app_id=' . $this->appId);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Basic ' . $this->apiKey]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            $params['app_id'] = $this->appId;
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }

        $response = json_decode(curl_exec($ch));

        curl_close($ch);

        if (isset($response->error) || isset($response->errors)) {
            throw new \Exception($response->errors[0] ?? $response->error);
        }

        return $response;
    }
}
