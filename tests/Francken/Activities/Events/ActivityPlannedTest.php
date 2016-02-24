<?php

namespace Tests\Francken\Activities\Events;

use Tests\SetupReconstitution;

use Broadway\UuidGenerator\Rfc4122\Version4Generator;

use Francken\Activities\ActivityId;
use Francken\Activities\Activity;
use Francken\Activities\Location;
use Francken\Activities\Events\ActivityPlanned;

use DateTimeImmutable;

class ActivityPlannedTest extends \PHPUnit_Framework_TestCase
{
    use SetupReconstitution;

    private $generator;

    public function setUp()
    {
        parent::setUp();

        $this->generator = new Version4Generator();
    }

    /**
     * @test
     */
    public function it_happend_to_an_activity()
    {
        $id = new ActivityId($this->generator->generate());
        $event = new ActivityPlanned(
            $id,
            "Crash & Compile",
            "Programming competition",
            new DateTimeImmutable("2015-12-04"),
            Location::fromNameAndAddress("Francken kamer"),
            Activity::SOCIAL
        );

        $this->assertEquals($id, $event->activityId());
        $this->assertEquals("Crash & Compile", $event->name());
        $this->assertEquals("Programming competition", $event->description());
        $this->assertEquals(new DateTimeImmutable("2015-12-04"), $event->time());
        $this->assertEquals(Location::fromNameAndAddress("Francken kamer"), $event->location());
        $this->assertEquals(Activity::SOCIAL, $event->category());
    }

    /**
     * @test
     */
    public function it_is_serializable()
    {
        $id = new ActivityId($this->generator->generate());
        $event = new ActivityPlanned(
            $id,
            'Crash & Compile',
            'Programming competition',
            new DateTimeImmutable('2015-12-04'),
            Location::fromNameAndAddress('Francken kamer'),
            Activity::SOCIAL
        );

        $this->assertEquals(
            $event,
            ActivityPlanned::deserialize($event->serialize())
        );
    }

    /** @test */
    public function the_deserialized_activity_has_the_same_properties()
    {
        $id = new ActivityId($this->generator->generate());
        $event = new ActivityPlanned(
            $id,
            'Crash & Compile',
            'Programming competition',
            new DateTimeImmutable('2015-12-04'),
            Location::fromNameAndAddress('Francken kamer'),
            Activity::SOCIAL
        );

        $event = ActivityPlanned::deserialize($event->serialize());

        $this->assertEquals($id, $event->activityId());
        $this->assertEquals("Crash & Compile", $event->name());
        $this->assertEquals("Programming competition", $event->description());
        $this->assertEquals(new DateTimeImmutable("2015-12-04"), $event->time());
        $this->assertEquals(Location::fromNameAndAddress("Francken kamer"), $event->location());
        $this->assertEquals(Activity::SOCIAL, $event->category());
    }
}