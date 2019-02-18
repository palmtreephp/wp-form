<?php

namespace Palmtree\WordPress\Form;

use Palmtree\Collection\Map;

/**
 * @method AbstractForm get(string $key)
 */
class FormCollection extends Map
{
    public function __construct()
    {
        parent::__construct(AbstractForm::class);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function render($key)
    {
        return $this->get($key)->getForm()->render();
    }
}
