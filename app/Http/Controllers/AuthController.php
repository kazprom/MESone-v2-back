<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Installer;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Psr\SimpleCache\InvalidArgumentException;

class AuthController extends Controller
{

    private $requestToken;
    private $freezeTime = 0;
    private $factor = 5;

    private const WRONG_AUTH_DATA = 'WRONG_AUTH_DATA';
    private const AUTH_FREEZE_TIME = 'AUTH_FREEZE_TIME';

    public function __construct(Request $request)
    {
        $userIp = $request->ip();
        $this->requestToken = md5($userIp);
    }

    /**
     * @param Exception $e
     * @throws Exception
     */
    private function dropException(Exception $e)
    {
        switch ($e->getMessage()) {
            case self::WRONG_AUTH_DATA:
                throw new CustomException(
                    self::WRONG_AUTH_DATA,
                    'AuthController',
                    'Не верный логин и/или пароль',
                );
            case self::AUTH_FREEZE_TIME:
                throw new CustomException(
                    self::AUTH_FREEZE_TIME . '_' . $this->freezeTime * $this->factor,
                    'AuthController',
                    'После не правельного ввода логина и/или пароля, нужно подождать ' . $this->freezeTime * $this->factor . ' секунд',
                );
            default:
                throw $e;
        }
    }

    /**
     * Аутентификация пользователя и генерация токена
     *
     * @param $_
     * @param array $args
     * @return Authenticatable|null
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function login($_, array $args): ?Authenticatable
    {
        try {
            if ($this->checkFreezeTime()) {
                throw new Exception(self::AUTH_FREEZE_TIME);
            }
            $guard = Auth::guard('api');
            $config = Config::get('installer');
            if ($args['remember']) {
                $guard->setTTL(365 * 24 * 60);
            }
            if (strtolower($args['login']) === strtolower($config['login'])) {
                /*
                 * Авторизация root
                 */
                if ($args['password'] == $config['password']) {
                    $installer = new Installer();
                    $installer->token = $guard->login($installer);
                    $this->removeFreezeTime();
                    return $guard->user();
                }
            } elseif (isset($args['domain_id'])) {
                /*
                * Aвторизация по домену
                */
                //TODO Aвторизация по домену
                if ($this->domain($args['login'], $args['password'], $args['domain_id'])) {
                    $userId = User::where('login', $args['login'])->value('id');
                    $token = $guard->loginUsingId($userId);
                    $user = $guard->user();
                    $user['token'] = $token;
                    $this->removeFreezeTime();
                    return $user;
                }
            } elseif ($token = $guard->attempt(['login' => $args['login'], 'password' => $args['password']])) {
                /*
                 * Стандартная авторизация
                 */
                $user = $guard->user();
                $user['token'] = $token;
                $this->removeFreezeTime();
                return $user;
            }
            $this->addFreezeTime();
            throw new Exception(self::WRONG_AUTH_DATA);
        } catch (Exception $exception) {
            $this->dropException($exception);
        } catch (InvalidArgumentException $exception) {
            throw $exception;
        }
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

    /**
     * @return bool
     * @throws Exception|InvalidArgumentException
     */
    private function checkFreezeTime(): bool
    {
        if (cache()->has($this->requestToken)) {
            $cache = cache()->get($this->requestToken);
            $this->freezeTime = $cache['count'];
            if (now()->timestamp < $cache['timestamp']) {
                return true;
            }
        } else {
            $payload = [
                'count' => $this->freezeTime,
                'timestamp' => null,
            ];
            cache()->put($this->requestToken, $payload, 60);
        }
        return false;
    }

    /**
     * @throws Exception
     */
    private function addFreezeTime(): void
    {
        $payload = [
            'count' => ++$this->freezeTime,
            'timestamp' => now()->addSeconds($this->freezeTime * $this->factor)->timestamp,
        ];
        cache()->put($this->requestToken, $payload, 360);
    }

    /**
     * @throws Exception
     */
    private function removeFreezeTime(): void
    {
        cache()->forget($this->requestToken);
    }
}
