<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Installer implements Authenticatable, JWTSubject
{
    public $id = 0;
    public $first_name = 'Установщик';
    public $login;
    public $password;
    public $enabled = true;
    public $token;

    public function __construct()
    {
        $this->login = Config::get('installer.login');
        $password = Config::get('installer.password');
        $this->password = Hash::make($password);
    }

    public function getAuthIdentifierName(): string
    {
        return 'login';
    }

    public function getAuthIdentifier(): string
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }

    public function getRememberToken(): string
    {
        if (empty($this->getRememberTokenName() === false)) {
            return $this->{$this->getRememberTokenName()};
        }
    }

    public function setRememberToken($value)
    {
        if (empty($this->getRememberTokenName()) === false) {
            $this->{$this->getRememberTokenName()} = $value;
        }
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    public function getJWTIdentifier(): string
    {
        return $this->id;
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'installer' => true,
        ];
    }
}
