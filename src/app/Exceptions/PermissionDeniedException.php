<?php

namespace VCComponent\Laravel\User\Exceptions;

class PermissionDeniedException extends \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
{
    public function __construct()
    {
        parent::__construct('Permission Denied', null, 1013);
    }
}
