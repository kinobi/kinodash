<?php

declare(strict_types=1);

namespace Kinodash\Modules\Greeting;

use Carbon\CarbonImmutable;
use DateTimeZone;
use Kinodash\Dashboard\Spot;
use Kinodash\Modules\Config;
use Kinodash\Modules\Module;
use Kinodash\Modules\ModuleTemplate;
use Kinodash\Modules\ModuleView;

class GreetingModule implements Module
{
    use ModuleTemplate;

    public const WHO_FALLBACK = 'Kinodash';

    private string $id = 'greeting';

    private string $greetings;

    private CarbonImmutable $now;

    public function boot(Config $config): void
    {
        $options = $config->getOptions();

        $greetings = array_merge(
            ['am' => 'Bonjour', 'pm' => 'Bon après-midi', 'evening' => 'Bonsoir', 'night' => 'Bonne nuit'],
            $options['greetings'] ?? []
        );

        $this->now = CarbonImmutable::now(new DateTimeZone($options['timezone'] ?? 'Europe/Paris'));

        $this->greetings = sprintf(
            '%s %s',
            $greetings[$this->getPeriod()],
            $options['who'] ?? self::WHO_FALLBACK
        );

        $this->booted = true;
    }

    /**
     * @inheritDoc
     */
    public function view(Spot $spot): ?ModuleView
    {
        if ($spot->equals(Spot::MIDDLE_CENTER())) {
            return new ModuleView(
                'center',
                [
                    'greetings' => $this->greetings,
                    'hours' => $this->now->format('H'),
                    'minutes' => $this->now->format('i')
                ]
            );
        }

        if ($spot->equals(Spot::SCRIPT())) {
            return new ModuleView('script', ['tz' => $this->now->timezone->getName()]);
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

    private function getPeriod(): string
    {
        $hour = $this->now->hour;

        if ($hour >= 22) {
            return 'night';
        }

        if ($hour >= 18) {
            return 'evening';
        }

        if ($hour >= 12) {
            return 'pm';
        }

        if ($hour >= 6) {
            return 'am';
        }

        return 'night';
    }
}
