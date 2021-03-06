<?php declare(strict_types=1);

namespace Palmtree\WordPress\Form;

use Palmtree\Collection\Map;

class FormCollection
{
    /** @var Map */
    private $map;

    public function __construct()
    {
        $this->map = new Map(AbstractForm::class);
    }

    public function set(string $key, AbstractForm $form): self
    {
        $this->map->set($key, $form);

        return $this;
    }

    public function get(string $key): AbstractForm
    {
        return $this->map->get($key);
    }

    public function has(string $key): bool
    {
        return $this->map->containsKey($key);
    }

    public function render(string $key): string
    {
        return $this->get($key)->getForm()->render();
    }
}
