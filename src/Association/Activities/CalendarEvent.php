<?php

declare(strict_types=1);

namespace Francken\Association\Activities;

use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Sabre\VObject\Component\VEvent;

final class CalendarEvent
{
    public string $id;

    public Collection $notes;
    private string $uid;
    private string $recurrence;
    private string $summary;

    private string $description;

    private string $location;

    private DateTimeImmutable $start;

    private DateTimeImmutable $end;

    private string $status;

    public function __toString() : string
    {
        return $this->id;
    }

    public static function fromEvent(VEvent $event) : self
    {
        $calendarEvent = new self();
        $calendarEvent->id = (string)Arr::first($event->select('UID'));
        $calendarEvent->uid = $calendarEvent->id;
        $calendarEvent->recurrence = ((string)Arr::first($event->select('RECURRENCE-ID')));
        $calendarEvent->summary = (string)Arr::first($event->select('SUMMARY'));
        $calendarEvent->description = (string)Arr::first($event->select('DESCRIPTION'));
        $calendarEvent->location = (string)Arr::first($event->select('LOCATION'));
        $calendarEvent->status = (string)Arr::first($event->select('STATUS'));
        $calendarEvent->notes = collect();
        $calendarEvent->parseSchedule($event);
        return $calendarEvent;
    }
    public function uid() : string
    {
        if ($this->recurrence !== '') {
            return $this->uid . '_' . $this->recurrence;
        }
        return $this->uid;
    }

    public function startDate() : DateTimeImmutable
    {
        return $this->start->setTimeZone(new DateTimeZone('Europe/Amsterdam'));
    }

    public function endDate() : DateTimeImmutable
    {
        return $this->end->setTimeZone(new DateTimeZone('Europe/Amsterdam'));
    }

    public function status() : string
    {
        return $this->status;
    }

    public function title() : string
    {
        return $this->summary;
    }

    public function name() : string
    {
        return $this->summary;
    }

    public function description() : string
    {
        return $this->description;
    }

    public function location() : string
    {
        if ($this->location == "Technisch Fysische Vereniging 'Professor Francken', Nijenborgh 4, 9747 AG Groningen, Netherlands") {
            return 'Franckenroom';
        }

        return $this->location;
    }

    public function googleMapsEmbedUri() : string
    {
        return 'https://www.google.com/maps/embed/v1/place?' . http_build_query([
            'q' => $this->location,
            'zoom' => 13,
            'key' => 'AIzaSyBmxy9LR0IeIDPmfVY_2ZQOLSbgNz_jDpw'
        ]);
    }

    public function shortDescription() : string
    {
        return Str::limit($this->description, 150);
    }

    public function url() : string
    {
        return '';
    }

    /*
     * [day] [mnth] to [day] [mnth]
     * Shows first month only when it is different
     */
    public function schedule() : string
    {
        $string = '';
        $from = Carbon::createFromFormat('Y-m-d', $this->startDate()->format('Y-m-d'));
        $to = Carbon::createFromFormat('Y-m-d', $this->endDate()->format('Y-m-d'));

        $start = $this->startDate()->setTimeZone(new DateTimeZone('Europe/Amsterdam'));
        $end = $this->endDate()->setTimeZone(new DateTimeZone('Europe/Amsterdam'));

        $string .= $start->format('d');

        // Display month and year only twice if necessary
        if ($from->month !== $to->month) {
            $string .= $start->format(' F');
        }

        // Check if the end date is different
        if ($from->format('Y-m-d') !== $to->format('Y-m-d')) {
            $string .= ' - ' . $end->format('d F');
        } else {
            $string .= $end->format(' F');
        }

        // Show time if the activity isn't on the whole day
        if ($this->startDate()->format('H:i') !== '00:00') {
            $string .= ' at ' . $start->format('H:i');
        }

        return $string;
    }

    private function parseSchedule(VEvent $event) : void
    {
        $this->start = Arr::first($event->select('DTSTART'))->getDateTime();
        $this->end = Arr::first($event->select('DTEND'))->getDateTime();
    }
}
