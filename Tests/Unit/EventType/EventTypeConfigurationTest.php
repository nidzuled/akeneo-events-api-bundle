<?php

declare(strict_types=1);

namespace Trilix\EventsApiBundle\Tests\Unit\EventType;

use PHPUnit\Framework\TestCase;
use Trilix\EventsApiBundle\EventType\EventIsNotSupportedException;
use Trilix\EventsApiBundle\EventType\EventTypeConfiguration;
use Trilix\EventsApiBundle\Model\GenericEventInterface;

class EventTypeConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function throwsEventIsNotSupportedExceptionIfGivenEventIsNotSupported(): void
    {
        $configuration = new EventTypeConfiguration(
            'foo',
            function (GenericEventInterface $event): bool { return $event->getSubject() instanceof isSupportedEntity; },
            function (GenericEventInterface $event): array { return ['foo' => get_class($event->getSubject())]; }
        );

        $event = new class implements GenericEventInterface
        {
            public function getSubject()
            {
                return new isNotSupportedEntity();
            }
        };

        $this->expectException(EventIsNotSupportedException::class);

        $configuration->resolve($event);
    }

    /**
     * @test
     */
    public function resolvesEventType(): void
    {
        $eventTypeConfiguration = new EventTypeConfiguration(
            'foo',
            function (GenericEventInterface $event): bool { return $event->getSubject() instanceof isSupportedEntity; },
            function (GenericEventInterface $event): array { return ['foo' => get_class($event->getSubject())]; }
        );

        $event = new class implements GenericEventInterface
        {
            public function getSubject()
            {
                return new isSupportedEntity();
            }
        };

        $eventType = $eventTypeConfiguration->resolve($event);

        $this->assertNotNull($eventType);
        $this->assertEquals($eventType->getName(), 'foo');
        $this->assertNotEmpty($eventType->getPayload());
        $this->assertArrayHasKey('foo', $eventType->getPayload());
        $this->assertEquals(get_class($event->getSubject()), $eventType->getPayload()['foo']);
    }
}

class isNotSupportedEntity {
}

class isSupportedEntity {
}
