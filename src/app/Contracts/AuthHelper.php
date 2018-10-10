<?php

namespace VCComponent\Laravel\User\Contracts;

interface AuthHelper
{
    public function getCredentialField();
    public function getCredentialRule();
    public function checkExistence($repository, $credentials);
}
