<?php
namespace OCA\Followme\Service;

use OCA\Followme\Db\FollowmeparamMapper;
use OCP\Http\Client\IClientService;

class IntegrationService {
    public function __construct(private FollowmeparamMapper $settings, private IClientService $clients) {}

    public function notify(string $message): bool {
        $url = $this->settings->getParamByName('urlRC');
        if ($url === '' || $this->settings->getParamByName('roomRC') === '') { return false; }
        $client = $this->clients->newClient();
        $response = $client->post($url . '/api/v1/login', ['timeout' => 10, 'json' => [
            'user' => $this->settings->getParamByName('userIdRC'), 'password' => $this->settings->getParamByName('passwordRC'),
        ]]);
        $auth = json_decode($response->getBody(), true);
        if (empty($auth['data']['authToken']) || empty($auth['data']['userId'])) { throw new \RuntimeException('Rocket.Chat authentication failed'); }
        $client->post($url . '/api/v1/chat.postMessage', ['timeout' => 10, 'headers' => [
            'X-Auth-Token' => $auth['data']['authToken'], 'X-User-Id' => $auth['data']['userId'],
        ], 'json' => ['channel' => $this->settings->getParamByName('roomRC'), 'text' => $message]]);
        return true;
    }

    public function publish(string $title, string $content): array {
        $url = $this->settings->getParamByName('urlWP');
        if ($url === '') { throw new \InvalidArgumentException('WordPress non configuré.'); }
        $response = $this->clients->newClient()->post($url . '/wp-json/wp/v2/posts', ['timeout' => 15,
            'auth' => [$this->settings->getParamByName('userIdWP'), $this->settings->getParamByName('passwordWP')],
            'json' => ['title' => $title, 'content' => $content],
        ]);
        return json_decode($response->getBody(), true) ?? [];
    }
}
