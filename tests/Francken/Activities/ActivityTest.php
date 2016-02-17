<?php

namespace Tests\Francken\Activities;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;

use Francken\Activities\Activity;
use Francken\Activities\ActivityId;
use Francken\Activities\Location;

use Francken\Activities\Events\ActivityPlanned;
use Francken\Activities\Events\ActivityPublished;

use DateTime;

class ActivitiesTest extends AggregateRootScenarioTestCase
{
    private $generator;

    public function setUp()
    {
        parent::setUp();

        // We will use this generator to generate valid UUIDs
        $this->generator = new Version4Generator();
    }

    protected function getAggregateRootClass()
    {
        return Activity::class;
    }

    /** @test */
    public function an_activity_can_be_planned()
    {
        $id = new ActivityId($this->generator->generate());

        $this->scenario
            ->when(function () use ($id) {
                return Activity::plan(
                    $id,
                    'Crash & Compile',
                    'Programming competition',
                    new DateTime('2015-12-04'),
                    Location::fromNameAndAddress('Francken kamer'),
                    Activity::SOCIAL
                );
            })
            ->then([new ActivityPlanned(
                $id,
                'Crash & Compile',
                'Programming competition',
                new DateTime('2015-12-04'),
                Location::fromNameAndAddress('Francken kamer'),
                Activity::SOCIAL
            )]);
    }

    /** @test */
    public function once_an_activity_has_been_planned_it_can_be_published()
    {

        $id = new ActivityId($this->generator->generate());

        $this->scenario
            ->withAggregateId($id)
            ->given([
                new ActivityPlanned(
                    $id,
                    'Crash & Compile',
                    'Programming competition',
                    new DateTime('2015-12-04'),
                    Location::fromNameAndAddress('Francken kamer'),
                    Activity::SOCIAL
                )
            ])
            ->when(function ($activity) use ($id) {
                return $activity->publish();
            })
            ->then([new ActivityPublished($id)]);
    }


}