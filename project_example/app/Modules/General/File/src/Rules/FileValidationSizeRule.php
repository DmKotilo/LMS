<?php

namespace File\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\File;

class FileValidationSizeRule implements ValidationRule
{
    public ?int $size;

    /**
     * @param int $size size in kb
     * @return static
     */
    public static function size(?int $size): static
    {
        return tap(new static(), fn($instance) => $instance->size = $size);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $decoded_data = base64_decode($value);
        fwrite($tmpFile = tmpfile(), $decoded_data);
        $path = stream_get_meta_data($tmpFile)['uri'];
        $tmpFileObj = new File($path);

        $fileSize = $tmpFileObj->getSize();

        if ($fileSize > ($this->size * 1000)) {
            $fail('Размер файла не должен быть больше 10мб');
        }
    }

}
