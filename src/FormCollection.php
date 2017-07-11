<?php

namespace Palmtree\WordPress\Form;

use Palmtree\Collection\Collection;

class FormCollection extends Collection
{
    public function __construct($items = [])
    {
        parent::__construct($items, AbstractForm::class);
    }

    public function render($key)
    {
        /** @var AbstractForm $formController */
        $formController = $this->get($key);

        return $formController->getForm()->render();
    }
}
