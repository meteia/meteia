<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

interface Comparable
{
    /**
     * Compares this object to another object.
     *   -1 is less than
     *    0 is equal
     *    1 is greater than.
     *
     * EX:
     *  IntegerLiteral(10).compare(IntegerLiteral(20)); // return -1
     *  IntegerLiteral(10).compare(IntegerLiteral(10)); // return  0
     *  IntegerLiteral(10).compare(IntegerLiteral(5));  // return  1
     *
     * @return int
     */
    public function compareTo(self $other);

    public function toNative();
}
