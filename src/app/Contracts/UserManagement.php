<?php

namespace VCComponent\Laravel\User\Contracts;

interface UserManagement
{
		public function ableToShow($id);

		public function ableToCreate();

    public function ableToUpdate($id);
    
    public function ableToDelete($id);
}
