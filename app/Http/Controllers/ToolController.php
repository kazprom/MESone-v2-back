<?php

namespace App\Http\Controllers;

use App\Models\GroupTool;
use App\Models\ToolUser;
use Exception;
use App\Exceptions\CustomException;
use App\Models\Tool;

class ToolController extends Controller
{

    private const TOOLS_NAME_UNIQUE = 'TOOLS_NAME_UNIQUE';
    private const WRONG_TOOL_ID = 'WRONG_TOOL_ID';

    /**
     * Выкинуть исключение
     *
     * @param Exception $e
     * @throws CustomException
     */
    private function dropException(Exception $e)
    {
        $category = 'ToolController';

        switch ($e->getMessage()) {
            case self::WRONG_TOOL_ID:
                throw new CustomException(
                    self::WRONG_TOOL_ID,
                    $category,
                    'Не верный id инструмента.'
                );
        }

        switch ($e->getCode()) {
            case '23000':
                throw new CustomException(
                    self::TOOLS_NAME_UNIQUE,
                    $category,
                    'Имя инструмента должно быть уникальное.'
                );
            default:
                throw new CustomException(
                    $e->getMessage(),
                    $category
                );
        }
    }

    /**
     * Добавить новый инструмент
     *
     * @param $_
     * @param array $args
     * @return mixed
     * @throws CustomException
     */
    public function createTool($_, array $args)
    {
        try {
            $model = new Tool();
            $result = $model->create([
                'created_at' => now(),
                'name' => $args['name'],
                'module_name' => $args['module_name'],
                'type' => $args['type'] ?? null,
            ]);
            $result->enabled = false;
            return $result;
        } catch (Exception $e) {
            $this->dropException($e);
        }
    }

    /**
     * Изменить инструмент
     *
     * @param $_
     * @param array $args
     * @return mixed
     * @throws CustomException
     */
    public function updateTool($_, array $args)
    {
        try {
            $payload = ['update_at' => now()];
            foreach ($args as $key => $value) {
                switch ($key) {
                    case 'id':
                        // no break;
                    case 'directive':
                        break;
                    default:
                        $payload[$key] = $value;
                        break;
                }
            }
            $tool = Tool::find($args['id']);
            if (is_null($tool)) {
                throw new CustomException(self::WRONG_TOOL_ID);
            }
            $tool->update($payload);
            return $tool;
        } catch (Exception $e) {
            $this->dropException($e);
        }
    }

    /**
     * Удалить инструмент
     *
     * @param $_
     * @param array $args
     * @return bool
     * @throws CustomException
     */
    public function deleteTool($_, array $args): bool
    {
        try {
            $tool = Tool::find($args['id']);
            if (is_null($tool)) {
                throw new CustomException(self::WRONG_TOOL_ID);
            }
            return $tool->delete();
        } catch (Exception $e) {
            $this->dropException($e);
        }
    }

    /**
     * Проверить доступность имени для инструмента
     *
     * @param $_
     * @param array $args
     * @return bool
     */
    public function checkName($_, array $args): bool
    {
        return boolval(Tool::where('name', $args['name'])->first()) === false;
    }

    /**
     * Отменить доступ пользователю к инструменту
     *
     * @param $_
     * @param array $args
     * @return bool
     */
    public function removeUser($_, array $args): bool
    {
        return ToolUser::where([
            ['user_id', $args['user_id']],
            ['tool_id', $args['tool_id']],
        ])->delete();
    }

    /**
     * Отменить доступ группы к инструменту
     *
     * @param $_
     * @param array $args
     * @return bool
     */
    public function removeGroup($_, array $args): bool
    {
        return GroupTool::where([
            ['group_id', $args['group_id']],
            ['tool_id', $args['tool_id']],
        ])->delete();
    }
}
