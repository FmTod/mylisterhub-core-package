<?php

namespace MyListerHub\Core\Concerns\API;

trait InitializesTraits
{
    /**
     * Initialize any initializable traits on the controller.
     */
    protected function initializeTraits(): void
    {
        $class = static::class;

        foreach (class_uses_recursive($class) as $trait) {
            if (method_exists($class, $method = 'initialize'.class_basename($trait))) {
                $this->{$method}();
            }
        }
    }

    public function __construct()
    {
        parent::__construct(...func_get_args());
        $this->initializeTraits();
    }
}
