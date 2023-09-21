<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Event;

use App\Crm\Application\Service\Calendar\GoogleSource;
use App\User\Domain\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

final class CalendarGoogleSourceEvent extends Event
{
    /**
     * @var GoogleSource[]
     */
    private array $sources = [];

    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function addSource(GoogleSource $source): CalendarGoogleSourceEvent
    {
        $this->sources[] = $source;

        return $this;
    }

    /**
     * @return GoogleSource[]
     */
    public function getSources(): array
    {
        return $this->sources;
    }
}
