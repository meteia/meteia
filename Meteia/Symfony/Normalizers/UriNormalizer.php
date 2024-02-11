<?php

declare(strict_types=1);

namespace Meteia\Symfony\Normalizers;

use Meteia\ValueObjects\Identity\UniqueId;
use Meteia\ValueObjects\Identity\Uri;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UriNormalizer implements NormalizerInterface, DenormalizerInterface
{
    #[\Override]
    public function normalize(mixed $object, string $format = null, array $context = []): string
    {
        \assert($object instanceof UriInterface);

        return (string) $object;
    }

    #[\Override]
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof UriInterface;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            UriInterface::class => true,
        ];
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): UriInterface
    {
        // @var UniqueId $type
        return new Uri($data);
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return $type === UriInterface::class || is_subclass_of($type, UriInterface::class);
    }
}
