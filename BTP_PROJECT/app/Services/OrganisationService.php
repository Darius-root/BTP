<?php

namespace App\Services;

use App\Models\Organisation;

class OrganisationService
{
    public function system(): Organisation
    {
        return Organisation::where('is_system', true)->firstOrFail();
    }

    public function systemId(): int
    {
        return $this->system()->id;
    }
}