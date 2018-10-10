<?php

namespace VCComponent\Laravel\User\Traits;

trait AuthHelper
{
    public function getCredentialField()
    {
        return $this->field;
    }

    public function getCredentialRule()
    {
        return $this->rule;
    }

    public function checkExistence($repository, $credentials)
    {
        return $repository->findByField($this->field, $credentials[$this->field])->first();
    }
}
