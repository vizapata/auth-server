<?php

namespace App\Application;

class PasswordHelper
{

    public static function hash($password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verify($password, $hashed)
    {
        return password_verify($password, $hashed);
    }
}
