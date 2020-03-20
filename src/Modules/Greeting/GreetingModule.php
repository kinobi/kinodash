<?php

declare(strict_types=1);

namespace Kinodash\Modules\Greeting;

use Kinodash\Modules\Module;
use Kinodash\Modules\ModuleTemplate;
use Kinodash\Modules\ModuleView;
use League\Plates\Engine as View;
use Psr\Http\Message\UriInterface;

class GreetingModule implements Module
{
    use ModuleTemplate;

    public const WHO_FALLBACK = 'Kinodash';

    private string $id = 'greeting';

    private string $who;

    public function boot(UriInterface $config, View $view): void
    {
        parse_str($config->getQuery(), $configQuery);

        $this->who = $configQuery['who'] ?? self::WHO_FALLBACK;

        $view->addFolder($this->id, __DIR__ . '/templates');
        $this->booted = true;
    }

    public function center(): ?ModuleView
    {
        return new ModuleView('center', ['who' => $this->who]);
    }
}
