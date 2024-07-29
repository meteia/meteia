<?php

declare(strict_types=1);

use Meteia\Symfony\Normalizers\HashNormalizer;
use Meteia\Symfony\Normalizers\UniqueIdNormalizer;
use Meteia\Symfony\Normalizers\UriNormalizer;
use Symfony\Component\PropertyInfo\Extractor\ConstructorExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

return [
    Serializer::class => static function (): Serializer {
        $phpDocExtractor = new PhpDocExtractor();
        $typeExtractor = new PropertyInfoExtractor(
            typeExtractors: [new ConstructorExtractor([$phpDocExtractor]), $phpDocExtractor],
        );
        $normalizers = [
            new HashNormalizer(),
            new UriNormalizer(),
            new UniqueIdNormalizer(),
            new BackedEnumNormalizer(),
            new ObjectNormalizer(propertyTypeExtractor: $typeExtractor),
            new ArrayDenormalizer(),
        ];

        $encoders = [new XmlEncoder(), new JsonEncoder()];

        return new Serializer($normalizers, $encoders);
    },
];
