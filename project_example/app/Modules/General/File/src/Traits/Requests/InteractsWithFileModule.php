<?php

namespace File\Traits\Requests;

use File\Factories\FileObjectFactory;
use File\FileObjects\FileObject;

trait InteractsWithFileModule
{
    public function fileObject(string $key): FileObject|array|null
    {

        $files = $this->allFiles();

        if (empty($files)) {
            $files = $this->input($key);
        }

        if (!isset($files)) {
            return null;
        }

        $result = [];
        foreach ($files as $file) {
            $result[] = FileObjectFactory::build($file);
        }

        return $result;
    }
}
