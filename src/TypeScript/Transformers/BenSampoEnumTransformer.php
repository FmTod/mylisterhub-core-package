<?php

namespace MyListerHub\Core\TypeScript\Transformers;

use BenSampo\Enum\Enum;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class BenSampoEnumTransformer implements Transformer
{
    public function __construct(
        protected readonly TypeScriptTransformerConfig $config
    ) {
    }

    public function transform(ReflectionClass $class, string $name): ?TransformedType
    {
        if ($class->isSubclassOf(Enum::class) === false) {
            return null;
        }

        return $this->config->shouldTransformToNativeEnums()
            ? $this->toEnum($class, $name)
            : $this->toType($class, $name);
    }

    protected function toEnum(ReflectionClass $class, string $name): TransformedType
    {
        /** @var Enum $enum */
        $enum = $class->getName();

        $options = array_map(
            static fn ($key, $value) => "'$key' = '$value'",
            array_keys($enum::asArray()),
            $enum::asArray(),
        );

        return TransformedType::create(
            $class,
            $name,
            implode(', ', $options),
            keyword: 'enum'
        );
    }

    private function toType(ReflectionClass $class, string $name): TransformedType
    {
        /** @var Enum $enum */
        $enum = $class->getName();

        $options = array_map(
            static fn ($enum) => "'$enum'",
            $enum::asArray(),
        );

        return TransformedType::create(
            $class,
            $name,
            implode(' | ', $options)
        );
    }
}
