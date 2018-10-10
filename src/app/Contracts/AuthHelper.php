<?php

namespace VCComponent\Laravel\User\Contracts;

use Illuminate\Http\Request;

interface AuthHelper
{
    public function parseRequest(Request $request);
    public function isEmpty(Request $request);
    public function isExists(Request $request, $id);
}
