<?php

namespace MyListerHub\Core\TypeScript;

use Based\TypeScript\Generators\AbstractGenerator as BaseGenerator;
use ReflectionClass;

abstract class AbstractGenerator extends BaseGenerator
{
    public function generate(ReflectionClass $reflection): ?string
    {
        $this->reflection = $reflection;
        $this->boot();

        if (empty(trim($definition = $this->getDefinition()))) {
            return "{$this->getIndentation()}export interface {$this->tsClassName()} {}".PHP_EOL;
        }

        return <<<TS
        {$this->getIndentation()}export interface {$this->tsClassName()} {
        {$this->getIndentation(1)}$definition
        {$this->getIndentation()}}

        TS;
    }

    protected function getIndentation(int $level = 0): string
    {
        $namespaced = config('typescript.namespace', true);
        $indentation = config('typescript.indentation', 4);

        return str_repeat(' ', $indentation * ((int) $namespaced + $level));
    }
}
