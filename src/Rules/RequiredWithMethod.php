<?php

namespace MyListerHub\Core\Rules;

/**
 * @method static static store(bool|callable $condition = true)
 * @method static static update(bool|callable $condition = true)
 * @method static static destroy(bool|callable $condition = true)
 */
class RequiredWithMethod
{
    /**
     * @var callable|bool
     */
    protected $condition = true;

    public function __construct(
        protected string $method = 'store',
        bool|callable $condition = true,
    ) {
        $this->condition = $condition;
    }

    public function __toString(): string
    {
        if (request()?->route()?->getActionMethod() !== $this->method) {
            return '';
        }

        if (is_callable($this->condition)) {
            return call_user_func($this->condition) ? 'required' : '';
        }

        return $this->condition ? 'required' : '';
    }

    public static function __callStatic(string $name, array $arguments): self
    {
        return new static($name, ...$arguments);
    }

    public function if(bool|callable $condition): self
    {
        $this->condition = $condition;

        return $this;
    }
}
