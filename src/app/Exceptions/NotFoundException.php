<?php

namespace VCComponent\Laravel\User\Exceptions;

class NotFoundException extends \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
{
    public function __construct($entity = null)
    {
        if ($entity) {
            $message = $entity . ' not found';
        } else {
            $message = 'Not Found';
        }
        parent::__construct($message, null, 1007);
    }
}
