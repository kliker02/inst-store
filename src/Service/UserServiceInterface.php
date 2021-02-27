<?php

namespace App\Service;

interface UserServiceInterface {

    /**
     * @return bool
     */
    public function updateBalance(int $User_ID);

}