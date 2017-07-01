<?php

namespace Palmtree\WordPress\Form;

use Palmtree\WordPress\AbstractCollection;

class FormCollection extends AbstractCollection
{

    public function render($key)
    {
        $formController = $this->getItem($key);

        return $formController->getForm()->render();
    }
}
