<?php

declare(strict_types=1);

namespace Meteia\Symfony\Normalizers;

use Meteia\Cryptography\Hash;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HashNormalizer implements NormalizerInterface, DenormalizerInterface
{
    #[\Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        \assert($object instanceof Hash);

        // @var Hash $object
        return $object->hex();
    }

    #[\Override]
    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Hash;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            Hash::class => true,
        ];
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Hash
    {
        return new Hash($data);
    }

    #[\Override]
    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): bool {
        return $type === Hash::class;
    }
}
