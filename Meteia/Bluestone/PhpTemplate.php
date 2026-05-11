<?php

declare(strict_types=1);

namespace Meteia\Bluestone;

use Meteia\Bluestone\Errors\TemplateNotFound;
use ReflectionClass;

trait PhpTemplate
{
    public function __toString(): string
    {
        ob_start();

        include $this->_getTemplatePath(static::class);

        $output = ob_get_clean();
        \assert($output !== false);

        return $output;
    }

    /**
     * @param class-string $viewClassName
     */
    private function _getTemplatePath(string $viewClassName): string
    {
        $templateClass = new ReflectionClass($viewClassName);

        while ($templateClass) {
            $fileName = $templateClass->getFileName();
            \assert($fileName !== false);
            $templatePath = str_replace('.php', '.tpl', $fileName);
            if (is_readable($templatePath)) {
                return $templatePath;
            }

            $templateClass = $templateClass->getParentClass();
        }

        throw new TemplateNotFound($viewClassName);
    }
}
