<?php

namespace File\Traits;

use File\Exceptions\FileModuleException;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;


trait MultiFileable
{
    use Fileable;
    /**
     *
     * @var string method name
     */
    protected string $fileRelation;

    public function selectFileRelation($relation): self
    {
        if (!method_exists($this, $relation)) {
            throw new FileModuleException("Метода '$relation' нет у класса " . class_basename($this), 500);
        }

        $this->fileRelation = $relation;

        return $this;
    }

    public function files(): MorphOneOrMany
    {
        return $this->{$this->fileRelation}();
    }
}
