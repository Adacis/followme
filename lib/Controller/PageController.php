<?php
namespace OCA\Followme\Controller;

use OCA\Followme\Db\FollowmeMapper;
use OCA\Followme\Service\IntegrationService;
use OCA\Followme\Service\CategoryCatalog;
use OCP\App\IAppManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;

class PageController extends Controller {
    public function __construct(string $AppName, IRequest $request, private FollowmeMapper $articles,
        private IUserSession $users, private IGroupManager $groups, private IAppManager $apps,
        private IURLGenerator $urls, private IntegrationService $integrations) {
        parent::__construct($AppName, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(): TemplateResponse {
        $user = $this->users->getUser();
        return new TemplateResponse('followme', 'index', [
            'categories' => CategoryCatalog::DEFAULTS,
            'key' => $user ? $user->getDisplayName() : '', 'version' => $this->apps->getAppVersion('followme'),
            'isAdmin' => $user && $this->groups->isAdmin($user->getUID()),
            'adminUrl' => $this->urls->linkToRoute('settings.AdminSettings.index', ['section' => 'followme']),
        ]);
    }

    #[NoAdminRequired]
    public function showActu(string $intervaldebut, string $intervalfin): DataResponse {
        return $this->interval($intervaldebut, $intervalfin, false);
    }

    #[NoAdminRequired]
    public function postActuInterval(string $intervaldebut, string $intervalfin): DataResponse {
        return $this->interval($intervaldebut, $intervalfin, true);
    }

    private function interval(string $start, string $end, bool $grouped): DataResponse {
        if (!ctype_digit($start) || !ctype_digit($end) || (int)$start > (int)$end) {
            return new DataResponse(['message' => 'Période invalide.'], Http::STATUS_BAD_REQUEST);
        }
        return new DataResponse($grouped ? $this->articles->findAllInterval($start, $end) : $this->articles->findAll([$start, $end]));
    }

    #[NoAdminRequired]
    public function findActu(int $id): DataResponse {
        try { return new DataResponse(['actu' => $this->articles->findArticleById($id)]); }
        catch (DoesNotExistException $e) { return new DataResponse(['message' => 'Actualité introuvable.'], Http::STATUS_NOT_FOUND); }
    }

    #[NoAdminRequired]
    public function insertActu(string $date, string $lien, string $description, string $categorie, string $title): DataResponse {
        if (!$this->validArticle($date, $lien, $description, $categorie, $title)) {
            return new DataResponse(['message' => 'Vérifiez la date, le lien HTTP(S) et les champs obligatoires.'], Http::STATUS_BAD_REQUEST);
        }
        $author = $this->users->getUser()->getDisplayName();
        $id = $this->articles->insertActu($date, $author, trim($lien), trim($description), trim($categorie), trim($title));
        $warning = null;
        try { $this->integrations->notify($categorie . ' - ' . $author . ' → ' . $lien); }
        catch (\Throwable $e) { $warning = 'Actualité enregistrée, mais la notification Rocket.Chat a échoué.'; }
        return new DataResponse(['id' => $id, 'warning' => $warning], Http::STATUS_CREATED);
    }

    #[NoAdminRequired]
    public function updateActu(string $date, string $lien, string $description, string $categorie, string $idArticle, string $title): DataResponse {
        if (!$this->validArticle($date, $lien, $description, $categorie, $title)) {
            return new DataResponse(['message' => 'Actualité invalide.'], Http::STATUS_BAD_REQUEST);
        }
        try { return new DataResponse($this->articles->updateActu($date, '', trim($lien), trim($description), trim($categorie), trim($title), $idArticle)); }
        catch (DoesNotExistException $e) { return new DataResponse(['message' => 'Actualité introuvable.'], Http::STATUS_NOT_FOUND); }
    }

    private function validArticle(string $date, string $link, string $description, string $category, string $title): bool {
        return ctype_digit($date) && (int)$date > 0 && filter_var(trim($link), FILTER_VALIDATE_URL)
            && in_array(strtolower(parse_url(trim($link), PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)
            && trim($description) !== '' && mb_strlen($description) <= 500
            && trim($category) !== '' && mb_strlen($category) <= 64
            && trim($title) !== '' && mb_strlen($title) <= 255;
    }

    #[NoAdminRequired]
    public function delActu(string $id): DataResponse {
        try { return new DataResponse($this->articles->delActu($id)); }
        catch (DoesNotExistException $e) { return new DataResponse(['message' => 'Actualité introuvable.'], Http::STATUS_NOT_FOUND); }
    }

    #[NoAdminRequired]
    public function findCategorie(): DataResponse { return new DataResponse($this->articles->findCategorie()); }

    #[NoAdminRequired]
    public function getNbArticleByUser(string $year): DataResponse {
        if (!ctype_digit($year) || (int)$year < 1970 || (int)$year > 2100) {
            return new DataResponse(['message' => 'Année invalide.'], Http::STATUS_BAD_REQUEST);
        }
        return new DataResponse($this->articles->getNbArticleByUser($year));
    }

    // Publishing to an external site remains restricted to administrators.
    public function envoieWP(string $titre, string $content): DataResponse {
        try { return new DataResponse($this->integrations->publish($titre, $content)); }
        catch (\InvalidArgumentException $e) { return new DataResponse(['message' => $e->getMessage()], Http::STATUS_BAD_REQUEST); }
        catch (\Throwable $e) { return new DataResponse(['message' => 'Échec de la publication WordPress.'], Http::STATUS_BAD_GATEWAY); }
    }
}
