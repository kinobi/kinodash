<?php

declare(strict_types=1);

namespace Kinodash\Modules\Greeting;

use Carbon\CarbonImmutable;
use DateTimeZone;
use Kinodash\Dashboard\Module\Config;
use Kinodash\Dashboard\Module\Module;
use Kinodash\Dashboard\Module\ModuleTemplate;
use Kinodash\Dashboard\Module\ModuleView;
use Kinodash\Dashboard\Spot;

class GreetingModule implements Module
{
    use ModuleTemplate;

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
            '%s',
            $greetings[$this->getPeriod()]
        );

        $this->booted = true;
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

    /**
     * @inheritDoc
     */
    public function view(Spot $spot): ?ModuleView
    {
        if ($spot->equals(Spot::BODY())) {
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
    public function templateFolder(): ?string
    {
        return __DIR__ . '/templates';
    }
}
