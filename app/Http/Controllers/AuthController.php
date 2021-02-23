<?php

namespace App\Http\Controllers;

use App\Models\Installer;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
{

    /**
     * Аутентификация пользователя и генерация токена
     *
     * @param null $_
     * @param array<string, mixed> $args
     * @return Authenticatable|null
     */
    public function login($_, array $args): ?Authenticatable
    {
        $guard = Auth::guard('api');
        $config = Config::get('installer');
        if ($args['remember']) {
            $guard->setTTL(365 * 24 * 60);
        }
        if (strtolower($args['login']) === strtolower($config['login']) && $args['password'] == $config['password']) {
            /*
             * Авторизация root
             */
            $installer = new Installer();
            $installer->token = $guard->login($installer);
            return $guard->user();
        } elseif (isset($args['domain_id'])) {
            /*
            * Aвторизация по домену
            */
            if ($this->domain($args['login'], $args['password'], $args['domain_id'])) {
                $userId = User::where('login', $args['login'])->value('id');
                $token = $guard->loginUsingId($userId);
                $user = $guard->user();
                $user['token'] = $token;
                return $user;
            }
        } elseif ($token = $guard->attempt(['login' => $args['login'], 'password' => $args['password']])) {
            /*
             * Стандартная авторизация
             */
            $user = $guard->user();
            $user['token'] = $token;
            return $user;
        }
        return null;
    }

    /**
     * Обновление текущего токена
     *
     * @return string
     */
    public function refreshToken(): string
    {
        return Auth::refresh();
    }

    /**
     * Удаление из базы текущего токена пользователя
     *
     * @return Authenticatable|null
     */
    public function logout(): ?Authenticatable
    {
        $user = Auth::user();
        Auth::logout();
        return $user;
    }

    /**
     * Проверка login в базе
     *
     * @param null $_
     * @param array<string, mixed> $args
     * @return bool
     */
    public function checkLogin($_, array $args): bool
    {
        if ($args['login'] === 'admin' || $args['login'] === Config::get('installer.login')) {
            return false;
        }
        return is_null(User::where('login', $args['login'])->value('login'));
    }

    /**
     * Проверка токена
     *
     * @return bool
     */
    public function checkToken(): bool
    {
        return Auth::check();
    }
}
