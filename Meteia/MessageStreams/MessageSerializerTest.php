<?php

declare(strict_types=1);

use Carbon\Carbon;
use Meteia\MessageStreams\MessageSerializer;

return;
it('serialize/unserialize DateTime', function (): void {
    /** @var \PhpBench\Tests\TestCase $this */
    $ms = new MessageSerializer();

    // Arrange
    $before = new DateTime();

    // Act
    $after = $ms->unserialize($ms->serialize($before));

    // Assert
    $this->assertEquals(
        $before->format(DateTimeInterface::RFC3339_EXTENDED),
        $after->format(DateTimeInterface::RFC3339_EXTENDED),
    );
    $this->assertEquals($before::class, $after::class);
});

it('serialize/unserialize DateTimeImmutable', function (): void {
    /** @var \PhpBench\Tests\TestCase $this */
    $ms = new MessageSerializer();

    // Arrange
    $before = new DateTimeImmutable();

    // Act
    $after = $ms->unserialize($ms->serialize($before));

    // Assert
    $this->assertEquals(
        $before->format(DateTimeInterface::RFC3339_EXTENDED),
        $after->format(DateTimeInterface::RFC3339_EXTENDED),
    );
    $this->assertEquals($before::class, $after::class);
});

it('serialize/unserialize Carbon', function (): void {
    /** @var \PhpBench\Tests\TestCase $this */
    $ms = new MessageSerializer();

    // Arrange
    $before = new Carbon();

    // Act
    $after = $ms->unserialize($ms->serialize($before));

    // Assert
    $this->assertEquals(
        $before->format(DateTimeInterface::RFC3339_EXTENDED),
        $after->format(DateTimeInterface::RFC3339_EXTENDED),
    );
    $this->assertEquals($before::class, $after::class);
});

it('serialize/unserialize UserId', function (): void {
    /** @var \PhpBench\Tests\TestCase $this */
    $ms = new MessageSerializer();

    // Arrange
    $before = UserId::random();

    // Act
    $mid = $ms->serialize($before);
    dump($mid);
    $after = $ms->unserialize($mid);

    // Assert
    $this->assertEquals($before->token(), $after->token());
    $this->assertEquals($before::class, $after::class);
});
