<?php

namespace Yassi\NovaOneSignal\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Nova;

class NovaOneSignalController extends BaseController
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
     * Recipients.
     *
     * @var array
     */
    protected $recipients;

    /**
     * Constructor method.
     */
    public function __construct()
    {
        $this->setApiKey(config('one_signal.api_key'))->setAppId(config('one_signal.app_id'));
    }

    /**
     * Get all authenticatables.
     *
     * @return  array
     */
    public function getAuthenticatables()
    {
        return [
            'list' => $this->authenticatables()->get(),
            'name' => config('one_signal.name'),
            'avatar' => config('one_signal.avatar'),
        ];
    }

    /**
     * Get all recipients for the index page.
     *
     * @return  array
     */
    public function getRecipientsForIndex()
    {
        $recipients = $this->getRecipients();
        $fields = config('one_signal.recipients_fields') ?? '*';
        $allKeys = collect($recipients[0])->keys()->all();

        $keys = $fields === '*' ? $allKeys : collect($fields)->filter(function ($field) use ($allKeys) {
            return in_array($field, $allKeys);
        });

        $resourceClass = Nova::resourceForModel(config('one_signal.model'));

        return $recipients->map(function ($recipient) use ($keys, $resourceClass) {
            return [
                'resourceName' => $resourceClass::uriKey(),
                'fields' => $keys->map(function ($key) use ($recipient, $resourceClass) {
                    $properName = Str::title(str_replace('_', ' ', $key));

                    switch ($key) {
                        case 'external_user_id':
                            return (new BelongsTo(__('Recipient'), $resourceClass::uriKey(), $resourceClass))->withMeta([
                                'belongsToId' => $recipient->$key,
                                'value' => $this->authenticatables()->find($recipient->$key)->{config('one_signal.name')} ?? null,
                            ]);
                        case 'invalid_identifier':
                            return (new Boolean($properName))->withMeta(['value' => $recipient->$key]);
                        case 'created_at':
                        case 'updated_at':
                        case 'last_active':
                            return (new Date($properName))->withMeta(['value' => (new Carbon($recipient->$key))->format('Y-m-d H:i:s')]);
                        default:
                            return (new Text($properName))->withMeta(['value' => $recipient->$key]);
                    }
                }),
                'id' => [
                    'value' => $recipient->id,
                ],
            ];
        });
    }

    /**
     * Get authentictables builder.
     *
     * @return  Builder
     */
    private function authenticatables()
    {
        $model = config('one_signal.model');
        return $model::whereIn('id', $this->getRecipients()->pluck('external_user_id'));
    }

    /**
     * Get included player ids by uahtnticatable id.
     *
     * @param  array  $recipients
     * @return  array
     */
    private function getIncludedRecipients(array $recipients)
    {
        return $this->authenticatables()->whereIn('id', collect($recipients)->pluck('id'))->get();
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
        $recipients = $this->getIncludedRecipients($request->recipients);

        foreach ($recipients as $player) {
            $params = [
                'include_player_ids' => $this->getRecipients()->where('external_user_id', $player->id)->pluck('id'),
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
     * Get recipients.
     *
     * @return  string
     */
    public function getRecipients()
    {
        return $this->recipients ?? $this->setRecipients();
    }

    /**
     * Set the list of recipients
     *
     * @return  array
     */
    public function setRecipients()
    {
        $this->recipients = collect($this->request('players')->players);

        return $this->recipients;
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
