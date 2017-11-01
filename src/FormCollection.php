<?php

namespace Palmtree\WordPress\Form;

use Palmtree\Collection\Collection;

class FormCollection extends Collection
{
    public function __construct()
    {
        parent::__construct(AbstractForm::class);
    }

    /**
     * @param int|string $key
     * @return AbstractForm
     */
    public function get($key)
    {
        return parent::get($key);
    }

    public function render($key)
    {
        return $this->get($key)->getForm()->render();
    }
}
