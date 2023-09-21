<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Service\Calendar;

use App\Crm\Transport\Event\CalendarConfigurationEvent;
use App\Crm\Transport\Event\CalendarDragAndDropSourceEvent;
use App\Crm\Transport\Event\CalendarGoogleSourceEvent;
use App\Crm\Transport\Event\CalendarSourceEvent;
use App\Crm\Transport\Event\RecentActivityEvent;
use App\Crm\Application\Configuration\SystemConfiguration;
use App\Crm\Application\Utils\Color;
use App\User\Domain\Entity\User;
use App\Crm\Domain\Repository\TimesheetRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class CalendarService
{
    public function __construct(private SystemConfiguration $configuration, private TimesheetRepository $repository, private EventDispatcherInterface $dispatcher)
    {
    }

    /**
     * @param User $user
     * @return DragAndDropSource[]
     * @throws \Exception
     */
    public function getDragAndDropResources(User $user): array
    {
        $maxAmount = $this->configuration->getCalendarDragAndDropMaxEntries();
        $event = new CalendarDragAndDropSourceEvent($user, $maxAmount);

        if ($maxAmount < 1) {
            return [];
        }

        $data = $this->repository->getRecentActivities($user, null, $maxAmount);

        $recentActivity = new RecentActivityEvent($user, $data);
        $this->dispatcher->dispatch($recentActivity);

        $entries = [];
        $colorHelper = new Color();
        $copy = $this->configuration->isCalendarDragAndDropCopyData();
        foreach ($recentActivity->getRecentActivities() as $timesheet) {
            $entries[] = new TimesheetEntry($timesheet, $colorHelper->getTimesheetColor($timesheet), $copy);
        }

        $event->addSource(new RecentActivitiesSource($entries));

        $this->dispatcher->dispatch($event);

        return $event->getSources();
    }

    public function getGoogleSources(User $user): ?Google
    {
        $apiKey = $this->configuration->getCalendarGoogleApiKey();
        if ($apiKey === null) {
            return null;
        }

        $sources = [];

        foreach ($this->configuration->getCalendarGoogleSources() as $name => $config) {
            $sources[] = new GoogleSource($name, $config['id'], $config['color']);
        }

        $event = new CalendarGoogleSourceEvent($user);
        $this->dispatcher->dispatch($event);

        foreach ($event->getSources() as $source) {
            $sources[] = $source;
        }

        return new Google($apiKey, $sources);
    }

    /**
     * @return array<CalendarSource>
     */
    public function getSources(User $user): array
    {
        $sources = [];

        $event = new CalendarSourceEvent($user);
        $this->dispatcher->dispatch($event);

        foreach ($event->getSources() as $source) {
            $sources[] = $source;
        }

        return $sources;
    }

    public function getConfiguration(): array
    {
        $config = [
            'dayLimit' => $this->configuration->getCalendarDayLimit(),
            'showWeekNumbers' => $this->configuration->isCalendarShowWeekNumbers(),
            'showWeekends' => $this->configuration->isCalendarShowWeekends(),
            'businessTimeBegin' => $this->configuration->getCalendarBusinessTimeBegin(),
            'businessTimeEnd' => $this->configuration->getCalendarBusinessTimeEnd(),
            'slotDuration' => $this->configuration->getCalendarSlotDuration(),
            'timeframeBegin' => $this->configuration->getCalendarTimeframeBegin(),
            'timeframeEnd' => $this->configuration->getCalendarTimeframeEnd(),
            'dragDropAmount' => $this->configuration->getCalendarDragAndDropMaxEntries(),
            'entryTitlePattern' => $this->configuration->find('calendar.title_pattern'),
        ];

        $event = new CalendarConfigurationEvent($config);
        $this->dispatcher->dispatch($event);

        return $event->getConfiguration();
    }
}
