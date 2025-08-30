<?php

namespace LibrarySystem\Classes;

use LibrarySystem\Traits\HasId;
use LibrarySystem\Traits\Loggable;

abstract class User
{
    use HasId, Loggable;

    private string $name;

    public function __construct(string $id, string $name)
    {
        $this->setId($id);
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function interactWithLibrary(Library $library): void;
}

