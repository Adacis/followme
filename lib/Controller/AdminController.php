<?php
namespace OCA\Followme\Controller;

use OCA\Followme\Db\FollowmeparamMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;
use OCP\IURLGenerator;

class AdminController extends Controller {
    public function __construct(string $AppName, IRequest $request, private FollowmeparamMapper $settings, private IURLGenerator $urls) {
        parent::__construct($AppName, $request);
    }

    #[NoCSRFRequired]
    public function index(): RedirectResponse {
        return new RedirectResponse($this->urls->linkToRoute('settings.AdminSettings.index', ['section' => 'followme']));
    }

    // No NoAdminRequired / NoCSRFRequired: Nextcloud enforces both protections.
    public function save(string $userIdWP = '', string $passwordWP = '', string $urlWP = '', string $userIdRC = '', string $passwordRC = '', string $urlRC = '', string $roomRC = ''): DataResponse {
        foreach ([$urlWP, $urlRC] as $url) {
            if ($url !== '' && (!filter_var($url, FILTER_VALIDATE_URL) || strtolower(parse_url($url, PHP_URL_SCHEME) ?? '') !== 'https')) {
                return new DataResponse(['message' => 'Les intégrations doivent utiliser une URL HTTPS valide.'], Http::STATUS_BAD_REQUEST);
            }
        }
        $this->settings->insertParam(trim($userIdWP), $passwordWP, rtrim(trim($urlWP), '/'), trim($userIdRC), $passwordRC, rtrim(trim($urlRC), '/'), trim($roomRC));
        return new DataResponse(['message' => 'Paramètres enregistrés.']);
    }
}
