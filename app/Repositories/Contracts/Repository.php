<?php

namespace App\Repositories\Contracts;

interface Repository
{
    public function model();

    public function useScope(array|string $scope);

    public function useWith(array|string $with);

    public function useHas(array|string $has, bool $boolean = true);

    public function useDoesntHave(array|string $doesntHave);
}
