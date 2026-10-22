<?php

declare(strict_types=1);

namespace Meteia\Html;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ClassVarianceAuthorityTest extends TestCase
{
    public function testTrueSelectsTheTrueVariant(): void
    {
        $classes = new ClassVarianceAuthority(variants: [
            'disabled' => [true => 'cursor-not-allowed', false => 'cursor-pointer'],
        ]);

        static::assertSame('cursor-not-allowed', (string) $classes->use(['disabled' => true]));
    }

    public function testFalseSelectsTheFalseVariant(): void
    {
        $classes = new ClassVarianceAuthority(variants: [
            'disabled' => [true => 'cursor-not-allowed', false => 'cursor-pointer'],
        ]);

        static::assertSame('cursor-pointer', (string) $classes->use(['disabled' => false]));
    }

    public function testFalseDefaultSelectsTheFalseVariant(): void
    {
        $classes = new ClassVarianceAuthority(variants: ['disabled' => [
            true => 'disabled',
            false => 'enabled',
        ]], defaultVariants: ['disabled' => false]);

        static::assertSame('enabled', (string) $classes->use([]));
    }

    public function testFalseOverridesATrueDefault(): void
    {
        $classes = new ClassVarianceAuthority(variants: ['disabled' => [
            true => 'disabled',
            false => 'enabled',
        ]], defaultVariants: ['disabled' => true]);

        static::assertSame('enabled', (string) $classes->use(['disabled' => false]));
    }

    public function testTrueOverridesAFalseDefault(): void
    {
        $classes = new ClassVarianceAuthority(variants: ['disabled' => [
            true => 'disabled',
            false => 'enabled',
        ]], defaultVariants: ['disabled' => false]);

        static::assertSame('disabled', (string) $classes->use(['disabled' => true]));
    }

    public function testTrueRemainsBooleanForCompoundMatching(): void
    {
        $classes = new ClassVarianceAuthority(variants: ['disabled' => [true => 'disabled']], compoundVariants: [
            ['disabled' => true, 'class' => 'boolean-match'],
            ['disabled' => 1, 'class' => 'integer-match'],
            ['disabled' => '1', 'class' => 'string-match'],
        ]);

        static::assertSame('disabled boolean-match', (string) $classes->use(['disabled' => true]));
    }

    public function testFalseDefaultRemainsBooleanForCompoundMatching(): void
    {
        $classes = new ClassVarianceAuthority(
            variants: ['disabled' => [false => 'enabled']],
            compoundVariants: [
                ['intent' => 'info', 'disabled' => false, 'className' => 'hover-blue'],
                ['disabled' => 0, 'class' => 'integer-match'],
                ['disabled' => '0', 'class' => 'string-match'],
                ['intent' => 'danger', 'disabled' => false, 'class' => 'hover-red'],
            ],
            defaultVariants: ['intent' => 'info', 'disabled' => false],
        );

        static::assertSame('enabled hover-blue', (string) $classes->use([]));
    }

    public function testFalseWithoutAMatchingOptionKeepsTheBaseClasses(): void
    {
        $classes = new ClassVarianceAuthority('button', ['disabled' => [true => 'disabled']]);

        static::assertSame('button', (string) $classes->use(['disabled' => false]));
    }

    public function testMissingVariantDoesNotSelectFalse(): void
    {
        $classes = new ClassVarianceAuthority('button', ['disabled' => [false => 'enabled']]);

        static::assertSame('button', (string) $classes->use([]));
    }

    public function testStringAndIntegerVariantsStillSelectClasses(): void
    {
        $classes = new ClassVarianceAuthority('grid', [
            'size' => ['small' => 'text-sm'],
            'columns' => [2 => ['grid-cols-2', 'gap-2']],
        ]);

        static::assertSame(
            'grid text-sm grid-cols-2 gap-2',
            (string) $classes->use(['size' => 'small', 'columns' => 2]),
        );
    }
}
