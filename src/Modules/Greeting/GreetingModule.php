<?php

declare(strict_types=1);

namespace Kinodash\Modules\Greeting;

use Kinodash\Dashboard\Spot;
use Kinodash\Modules\Module;
use Kinodash\Modules\ModuleTemplate;
use Kinodash\Modules\ModuleView;
use Psr\Http\Message\UriInterface;

class GreetingModule implements Module
{
    use ModuleTemplate;

    public const WHO_FALLBACK = 'Kinodash';

    private string $id = 'greeting';

    private string $who;

    public function boot(UriInterface $config): void
    {
        parse_str($config->getQuery(), $configQuery);

        $this->who = $configQuery['who'] ?? self::WHO_FALLBACK;

        $this->booted = true;
    }

    /**
     * @inheritDoc
     */
    public function view(Spot $spot): ?ModuleView
    {
        if ($spot->equals(Spot::MIDDLE_CENTER())) {
            return new ModuleView('center', ['who' => $this->who]);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function templateFolder(): string
    {
        return __DIR__ . '/templates';
    }
}
