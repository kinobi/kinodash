<?php

declare(strict_types=1);

namespace Kinodash\Modules\Auth0;

use Auth0\SDK\Auth0;
use Kinodash\Dashboard\Spot;
use Kinodash\Modules\Config;
use Kinodash\Modules\Module;
use Kinodash\Modules\ModuleTemplate;
use Kinodash\Modules\ModuleView;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Auth0Module implements Module
{
    use ModuleTemplate;

    private string $id = 'auth0';

    private Auth0 $auth0;

    private UriInterface $logoutUrl;

    public function __construct(Auth0 $auth0, UriInterface $logoutUrl)
    {
        $this->auth0 = $auth0;
        $this->logoutUrl = $logoutUrl;
    }

    /**
     * @inheritDoc
     */
    public function api(RequestInterface $request, ResponseInterface $response, array $params): ResponseInterface
    {
        switch ($params[0]) {
            case 'callback':
                $this->auth0->getUser();

                return $response
                    ->withStatus(302)
                    ->withHeader('Location', '/');
                break;

            case 'login':
                $this->auth0->login();
                break;

            case 'logout':
                $this->auth0->logout();

                return $response
                    ->withStatus(302)
                    ->withHeader('Location', (string)$this->logoutUrl);
                break;
        }

        return $response->withStatus(404);
    }

    /**
     * @inheritDoc
     */
    public function boot(Config $config): void
    {
        $this->booted = true;
    }

    /**
     * @inheritDoc
     */
    public function templateFolder(): string
    {
        return __DIR__ . '/templates';
    }

    /**
     * @inheritDoc
     */
    public function view(Spot $spot): ?ModuleView
    {
        if (!$spot->equals(Spot::BODY_HEAD())) {
            return null;
        }

        if (!$this->auth0->getUser()) {
            return new ModuleView('button', ['text' => 'Login', 'icon' => 'door-open','a' => '/auth0/login']);
        }

        return new ModuleView('button', ['text' => 'Logout', 'icon' => 'door-closed','a' => '/auth0/logout']);
    }
}
