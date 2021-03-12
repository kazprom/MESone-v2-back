<?php

namespace App\Exceptions;

use Exception;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

class CustomException extends Exception implements RendersErrorsExtensions
{
    /**
     * @var @string
     */
    protected $category;
    protected $reason;

    /**
     * CustomException constructor.
     *
     * @param string $message - Сообщение об ошибке
     * @param string|null $category - Категория исключений
     * @param string|null $reason - Причина, по которой возникла эта ошибка
     */
    public function __construct(string $message, string $category = null, string $reason = null)
    {
        parent::__construct($message);
        $this->category = $category ?? 'Custom Exception';
        $this->reason = $reason;
    }

    /**
     * Возвращает истину, если сообщение об исключении безопасно для отображения клиенту
     *
     * @return bool
     * @api
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Возвращает строку, описывающую категорию ошибки
     *
     * Значение "graphql" зарезервировано для ошибок, вызванных синтаксическим анализом или проверкой запроса,
     * не используйте его.
     *
     * @return string
     * @api
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Вернуть содержимое, которое помещено в часть "extensions" возвращенной ошибки.
     *
     * @return array
     */
    public function extensionsContent(): array
    {
        $result = [];
        if (is_null($this->reason) === false) {
            array_push($result, ['reason' => $this->reason]);
        }
        return $result;
    }
}
