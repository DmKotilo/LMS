<?php

namespace File\Http\Requests;

//TODO: Test
use File\Traits\Requests\InteractsWithFileModule;
use App\Http\Requests\FormRequest;

/**
 * @method File\FileObjects\Fileobject|array|null fileObject()
 */
class TestFormRequest extends FormRequest
{
    use InteractsWithFileModule;
}
