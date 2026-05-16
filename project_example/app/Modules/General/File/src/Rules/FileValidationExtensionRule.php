<?php

namespace File\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\File;


class FileValidationExtensionRule implements ValidationRule
{
    public ?array $types = [];

    public static function types(?array $extensions)
    {
        return tap(new static(), fn ($instance) => $instance->types = $extensions);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $decoded_data = base64_decode($value);
        fwrite($tmpFile = tmpfile(), $decoded_data);
        $path = stream_get_meta_data($tmpFile)['uri'];
        $tmpFileObj = new File($path);

        if (! in_array($tmpFileObj->extension(), $this->types)){
            $fail('The file has an unexpected extension');
        }
    }

}
