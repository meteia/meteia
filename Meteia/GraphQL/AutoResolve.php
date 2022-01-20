<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use GraphQL\Executor\Executor;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\WrappingType;
use Invoker\InvokerInterface;
use Meteia\GraphQL\Contracts\Field;
use Meteia\GraphQL\Contracts\RequestContext;
use Meteia\GraphQL\Contracts\Resolver;
use Meteia\GraphQL\Contracts\ResolvesAttr;
use ProxyManager\Proxy\ValueHolderInterface;
use Psr\Container\ContainerInterface;

class AutoResolve
{
    /**
     * @param ContainerInterface|InvokerInterface $container
     */
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    public function resolve($source, array $args, RequestContext $requestContext, ResolveInfo $resolveInfo)
    {
        $returnType = $resolveInfo->returnType;
        $expectsList = $returnType instanceof ListOfType;
        while ($returnType instanceof WrappingType && $expectsList === false) {
            $returnType = $returnType->getWrappedType(false);
            $expectsList = $returnType instanceof ListOfType;
        }

        // Unwrap return types that use the @Injectable(lazy=true) annotation to allow for circular references
        if ($returnType instanceof ValueHolderInterface) {
            $returnType = $returnType->getWrappedValueHolderValue();
        }

        $resolver = $returnType;
        $returnTypeClass = trim(\get_class($returnType), '\\');
        $resolverClass = $returnTypeClass . 'Resolver';
        if (class_exists($resolverClass)) {
            /** @var Resolver $resolver */
            $resolver = $this->container->get($resolverClass);
        }

        if ($resolver instanceof Resolver && ($returnType instanceof StringType || $resolver instanceof ResolvesAttr)) {
            return $resolver->data($source->{$resolveInfo->fieldName}, $args, $requestContext);
        }
        if ($resolver instanceof Resolver) {
            if (isset($source->{$resolveInfo->fieldName}) && \is_array($source->{$resolveInfo->fieldName}) && $expectsList) {
                return array_map(function ($source) use ($resolver, $args, $requestContext) {
                    return $resolver->data($source, $args, $requestContext);
                }, $source->{$resolveInfo->fieldName});
            }

            return $resolver->data($source, $args, $requestContext);
        }

        if ($returnType instanceof Field) {
            return $source;
        }

        return Executor::defaultFieldResolver($source, $args, $requestContext, $resolveInfo);
    }
}
