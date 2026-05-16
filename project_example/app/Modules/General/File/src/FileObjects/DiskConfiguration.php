<?php

namespace File\FileObjects;

use File\Exceptions\FileModuleInvalidArgumentException;

final class DiskConfiguration
{
    private string $value;

    public function __construct(string $value)
    {
        $this->isValid($value);
        $this->value = $value;
    }

    private function isValid(string $value): void
    {
        $disks = array_keys(config('filesystems.disks'));

        if (! in_array($value, $disks)) {
            throw new FileModuleInvalidArgumentException("The disk '$value' is not defined in the configuration");
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
