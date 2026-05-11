<?php

declare(strict_types=1);

namespace Meteia\Symfony\Normalizers;

use Meteia\ValueObjects\Identity\UniqueId;
use Override;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UniqueIdNormalizer implements NormalizerInterface, DenormalizerInterface
{
    #[Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        \assert($object instanceof UniqueId);

        return $object->token();
    }

    #[Override]
    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UniqueId;
    }

    #[Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            UniqueId::class => true,
        ];
    }

    #[Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): UniqueId
    {
        \assert(is_subclass_of($type, UniqueId::class));
        \assert(\is_string($data));
        $result = $type::fromToken($data);
        \assert($result instanceof UniqueId);

        return $result;
    }

    #[Override]
    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): bool {
        return is_subclass_of($type, UniqueId::class);
    }
}
