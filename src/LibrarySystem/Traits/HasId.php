<?php

namespace LibrarySystem\Traits;

trait HasId
{
    private string $id;

    public function generateUniqueId(): string
    {
        return uniqid('id_', true);
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}

