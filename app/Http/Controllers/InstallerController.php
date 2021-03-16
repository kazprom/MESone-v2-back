<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;

use App\Exceptions\CustomException;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use PDO;
use PDOException;
use Nuwave\Lighthouse\Schema\Context;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException as JWTException;

class InstallerController extends Controller
{
    private $path;
    private $env;
    private const PDO_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    private const DATABASE_EXIST = 'DATABASE_EXIST';

    /**
     * InstallerController constructor.
     */
    public function __construct()
    {
        $this->path = base_path('.env');
        $env = file_get_contents($this->path);
        $this->env = explode("\n", $env);
    }

    /**
     * Выкинуть исключение для PDO
     *
     * @param Exception $e
     * @throws CustomException
     */
    private function dropPDOException(Exception $e)
    {
        switch ($e->getCode()) {
            case 'HY000':
                throw new CustomException(
                    self::DATABASE_EXIST,
                    'InstallerController',
                    'Невозможно создать базу данных, база данных существует'
                );
//            case 1008:
//                throw new CustomException(
//                    'Невозможно удалить базу данных, база данных не существует',
//                    'InstallerController'
//                );
//            case 1045:
//                throw new CustomException(
//                    "Пользователю отказано в доступе",
//                    'InstallerController'
//                );
//            case 1049:
//                throw new CustomException(
//                    'База данных не существует',
//                    'InstallerController'
//                );
            default:
                throw new CustomException(
                    $e->getMessage(),
                    'InstallerController'
                );
        }
    }

    /**
     * Проверка токена установщика
     *
     * @param Context $context
     * @return void
     * @throws CustomException
     * @throws Exception
     */
    private function isInstaller(Context $context): void
    {
        try {
            $token = $context->request()->bearerToken();
            $config = Config::get('jwt');
            $payload = JWT::decode($token, $config['secret'], array($config['algo']));
            if ($payload->installer !== true) {
                throw new JWTException(null, 999);
            }
        } catch (JWTException $exception) {
            switch ($exception->getCode()) {
                case 0:
                    throw new CustomException(
                        'Ключ установщика не действителен',
                        'InstallerController'
                    );
                default:
                    throw new CustomException(
                        'Вы не установщик',
                        'InstallerController'
                    );
            }
        }
    }

    /**
     * Получить содержимое env файла
     *
     * @param $_
     * @param array $args
     * @return string
     */
    public function getEnv($_, array $args): string
    {
        $result = [];
        foreach ($this->env as $value) {
            $key_value = explode('=', $value);
            if (
                empty($key_value[0]) ||  // Пустое значение
                $key_value[0][0] === '#' || // Закоментированое значение
                empty($args['keys']) === false && array_search($key_value[0], $args['keys']) === false // Вывод конктетных ключей
            ) continue;
            $result[$key_value[0]] = $key_value[1];
        }
        return json_encode($result);
    }

    /**
     * Измениь содержимое env файла
     *
     * @param $_
     * @param array $args
     * @param Context $context
     * @return bool
     * @throws CustomException
     */
    public function setEnv($_, array $args, Context $context): bool
    {
        $this->isInstaller($context);
        $values = json_decode($args['json'], true);
        foreach ($values as $key => $value) {
            foreach ($this->env as &$item) {
                $key_value = explode('=', $item);
                if ($key_value[0] !== $key && $key_value[0] !== "#$key") {
                    continue;
                }
                $item = "$key=$value";
                continue 2;
            }
            array_push($this->env, "$key=$value\n");
        }
        return file_put_contents($this->path, implode("\n", $this->env));
    }

    /**
     * Обновить все ключи
     *
     * @param $_
     * @param array $args
     * @param Context $context
     * @return bool
     * @throws CustomException
     */
    public function regenerationKeys($_, array $args, Context $context): bool
    {
        $this->isInstaller($context);
        try {
            Artisan::call('key:generate');
            Artisan::call('jwt:secret --force');
        } catch (Exception $exception) {
            throw $exception;
        }
        return true;
    }

    /**
     * Запрос на пинг хоста или ip
     *
     * @param $_
     * @param array $args
     * @return array
     */
    public function pingHost($_, array $args): array
    {
        $command = 'ping ';
        if (PHP_OS === 'Linux') {
            $command .= '-c 4 ';
        }
        exec($command . $args['host'], $output, $status);
        foreach ($output as &$item) {
            if (mb_check_encoding($item, 'CP-866')) {
                $item = mb_convert_encoding($item, 'UTF-8', 'CP-866');
            }
        }
        return [
            'status' => $status === 0,
            'log' => $output,
        ];
    }

    /**
     * Тест соединения с базой данных
     *
     * @param $_
     * @param array $args
     * @return bool
     * @throws CustomException
     */
    public function checkDbConnection($_, array $args): bool
    {
        try {
            $type = config('database.default');
            $database = config('database.connections.' . $type . '.database');
            $host = config('database.connections.' . $type . '.host');
            $port = config('database.connections.' . $type . '.port');
            $dsn = $type . ':dbname=' . $database . ';host=' . $host . ':' . $port;

            $username = config('database.connections.' . $type . '.username');
            $password = config('database.connections.' . $type . '.password');
            new PDO($dsn, $username, $password, self::PDO_OPTIONS);
        } catch (PDOException $e) {
            $this->dropPDOException($e);
        }
        return true;
    }

    /**
     * Создание базы данных
     *
     * @param $_
     * @param array $args
     * @param Context $context
     * @return bool
     * @throws CustomException
     */
    public function createDataBase($_, array $args, Context $context): bool
    {
        $this->isInstaller($context);
        try {
            $type = config('database.default');
            $host = config('database.connections.' . $type . '.host');
            $port = config('database.connections.' . $type . '.port');
            $dsn = $type . ':host=' . $host . ':' . $port;

            $username = config('database.connections.' . $type . '.username');
            $password = config('database.connections.' . $type . '.password');
            $conn = new PDO($dsn, $username, $password, self::PDO_OPTIONS);

            $database = config('database.connections.' . $type . '.database');
            $conn->exec("CREATE DATABASE `$database`");
        } catch (Exception $e) {
            $this->dropPDOException($e);
        }
        return true;
    }

    /**
     * Сброс базы данных
     *
     * @param $_
     * @param array $args
     * @param Context $context
     * @return bool
     * @throws CustomException
     */
    public function resetDataBase($_, array $args, Context $context): bool
    {
        $this->isInstaller($context);
        try {
            Artisan::call('migrate:fresh --seed');
        } catch (PDOException $e) {
            $this->dropPDOException($e);
        }
        return true;
    }

    /**
     * Удаление базы данных
     *
     * @param $_
     * @param array $args
     * @param Context $context
     * @return bool
     * @throws CustomException
     */
    public function dropDataBase($_, array $args, Context $context): bool
    {
        $this->isInstaller($context);
        try {
            $type = config('database.default');
            $host = config('database.connections.' . $type . '.host');
            $port = config('database.connections.' . $type . '.port');
            $dsn = $type . ':host=' . $host . ':' . $port;

            $username = config('database.connections.' . $type . '.username');
            $password = config('database.connections.' . $type . '.password');
            $conn = new PDO($dsn, $username, $password, self::PDO_OPTIONS);

            $database = config('database.connections.' . $type . '.database');
            $conn->exec("DROP DATABASE `$database`");
        } catch (PDOException $e) {
            $this->dropPDOException($e);
        }
        return true;
    }
}
