<?php

namespace MyListerHub\Core\TypeScript;

use Based\TypeScript\TypeScriptGenerator as BaseGenerator;
use Illuminate\Support\Collection;
use ReflectionClass;

class TypeScriptGenerator extends BaseGenerator
{
    protected function makeNamespace(string $namespace, Collection $reflections): string
    {
        return $reflections->map(fn (ReflectionClass $reflection) => $this->makeInterface($reflection))
            ->whereNotNull()
            ->whenNotEmpty(function (Collection $definitions) use ($namespace) {
                if (! config('typescript.namespace', true)) {
                    return $definitions;
                }

                $tsNamespace = str_replace('\\', '.', $namespace);

                return $definitions->prepend("declare namespace {$tsNamespace} {")->push('}'.PHP_EOL);
            })
            ->join(PHP_EOL);
    }
}
