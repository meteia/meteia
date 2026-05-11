<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects;

use Meteia\Yeso\Exceptions\MethodNotFound;

trait WithMagicMethod
{
    public function __call($name, $arguments)
    {
        if (strncmp($name, 'with', 4) === 0) {
            return $this->with(lcfirst(substr($name, 4)), ...$arguments);
        }

        throw new MethodNotFound();
    }

    private function with($key, $value): self
    {
        $copy = clone $this;
        $copy->{$key} = $value;

        return $copy;
    }
}
