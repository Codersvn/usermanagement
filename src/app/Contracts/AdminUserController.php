<?php

namespace VCComponent\Laravel\User\Contracts;

use Illuminate\Http\Request;
use VCComponent\Laravel\User\Repositories\UserRepository;
use VCComponent\Laravel\User\Validators\UserValidator;

interface AdminUserController
{
    public function __construct(UserRepository $repository, UserValidator $validator);
    public function index(Request $request);
    public function list(Request $request);
    public function show(Request $request, $id);
    public function store(Request $request);
    public function update(Request $request, $id);
    public function destroy($id);
    public function bulkUpdateStatus(Request $request);
    public function status(Request $request, $id);
}
