<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use GraphQL\Executor\Executor;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\WrappingType;
use Meteia\DependencyInjection\Container;
use Meteia\GraphQL\Contracts\Field;
use Meteia\GraphQL\Contracts\RequestContext;
use Meteia\GraphQL\Contracts\Resolver;
use Meteia\GraphQL\Contracts\ResolvesAttr;

class AutoResolve
{
    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * @param array<string, mixed> $args
     */
    public function resolve(mixed $source, array $args, RequestContext $requestContext, ResolveInfo $resolveInfo): mixed
    {
        $returnType = $resolveInfo->returnType;
        $expectsList = $returnType instanceof ListOfType;
        while ($returnType instanceof WrappingType && !$expectsList) {
            $returnType = $returnType->getWrappedType();
            $expectsList = $returnType instanceof ListOfType;
        }

        // Unwrap return types that use the @Injectable(lazy=true) annotation to allow for circular references
        // if ($returnType instanceof ValueHolderInterface) {
        //    $returnType = $returnType->getWrappedValueHolderValue();
        // }

        $resolver = $returnType;
        $returnTypeClass = trim($returnType::class, '\\');
        $resolverClass = $returnTypeClass . 'Resolver';
        if (class_exists($resolverClass)) {
            /** @var Resolver $resolver */
            $resolver = $this->container->get($resolverClass);
        }

        if ($resolver instanceof Resolver && ($returnType instanceof StringType || $resolver instanceof ResolvesAttr)) {
            $value = \is_object($source) ? $source->{$resolveInfo->fieldName} ?? null : null;

            return $resolver->data($value, $args, $requestContext);
        }
        if ($resolver instanceof Resolver) {
            if (
                \is_object($source)
                && isset($source->{$resolveInfo->fieldName})
                && \is_array($source->{$resolveInfo->fieldName})
                && $expectsList
            ) {
                return array_map(static fn($source) => $resolver->data(
                    $source,
                    $args,
                    $requestContext,
                ), $source->{$resolveInfo->fieldName});
            }

            return $resolver->data($source, $args, $requestContext);
        }

        if ($returnType instanceof Field) {
            return $source;
        }

        /** @var array<string, mixed> $args */
        return Executor::defaultFieldResolver($source, $args, $requestContext, $resolveInfo);
    }
}
