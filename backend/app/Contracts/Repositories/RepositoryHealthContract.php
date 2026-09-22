<?php

namespace App\Contracts\Repositories;

use App\Domain\Repository\RepositoryHealth;

interface RepositoryHealthContract
{
    public function evaluate(): RepositoryHealth;
}