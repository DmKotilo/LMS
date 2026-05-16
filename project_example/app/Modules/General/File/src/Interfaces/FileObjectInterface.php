<?php

namespace File\Interfaces;

interface FileObjectInterface
{
    public function store(?string $directory = null, ?string $disk = null): bool;
}
