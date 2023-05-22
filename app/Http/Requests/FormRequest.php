<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;

class FormRequest extends BaseFormRequest
{
    public function validationData()
    {
        return in_array($this->method(), ['POST', 'PUT', 'PATCH']) ? $this->post() : $this->query();
    }
}
