<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;
use Illuminate\Routing\Route;

class FormRequest extends BaseFormRequest
{
    public function validationData()
    {
        return $this->all();
    }

    public static function getInstance(Route $route, array $data)
    {
        return (new static(query: $data, request: $data))
            ->setRouteResolver(fn () => $route);
    }
}
