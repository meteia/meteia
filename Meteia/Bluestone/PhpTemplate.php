<?php

declare(strict_types=1);

namespace Meteia\Bluestone;

use Meteia\Bluestone\Errors\TemplateNotFound;

trait PhpTemplate
{
    public function __toString(): string
    {
        ob_start();

        include $this->_getTemplatePath(static::class);

        return ob_get_clean();
    }

    private function _getTemplatePath(string $viewClassName): string
    {
        $templateClass = new \ReflectionClass($viewClassName);

        while ($templateClass) {
            $templatePath = str_replace('.php', '.tpl', $templateClass->getFileName());
            if (is_readable($templatePath)) {
                return $templatePath;
            }

            $templateClass = $templateClass->getParentClass();
        }

        throw new TemplateNotFound($viewClassName);
    }
}
