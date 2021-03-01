<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;
//use Illuminate\Http\Request;
use App\Exceptions\CustomException;

class GroupController extends Controller
{
    /**
     * Проверить на существование пользователя
     *
     * @param $id
     * @param bool $exception - Выбросить исключение
     * @return bool
     * @throws CustomException
     */
    private function checkUser($id, $exception = true): bool
    {
        if (is_null(User::where('id', $id)->first())) {
            if ($exception) {
                throw new CustomException(
                    "Пользователя с id#$id не существует.",
                    'GroupController'
                );
            }
            return false;
        }
        return true;
    }

    /**
     * Проверить на существование группы
     *
     * @param $id
     * @param bool $exception - Выбросить исключение
     * @return bool
     * @throws CustomException
     */
    private function checkGroup($id, $exception = true): bool
    {
        if (is_null(Group::where('id', $id)->first())) {
            if ($exception) {
                throw new CustomException(
                    "Группы с id#$id не существует.",
                    'GroupController'
                );
            }
            return false;
        }
        return true;
    }

    /**
     * Добавить пользователя в группу
     *
     * @param $_
     * @param array $args
     * @return bool
     * @throws CustomException
     */
    public function addUser($_, array $args): bool
    {
        $this->checkUser($args['user_id']);
        $this->checkGroup($args['group_id']);
        if (is_null(GroupUser::where([['group_id', $args['group_id']], ['user_id', $args['user_id']]])->first()) === false) {
            throw new CustomException(
                'Пользователь с id#' . $args['user_id'] . ' уже состоит в группе с id#' . $args['group_id'] . '.',
                'GroupController'
            );
        }
        return GroupUser::insert([
            'group_id' => $args['group_id'],
            'user_id' => $args['user_id']
        ]);
    }

    /**
     * Исключить пользователя из группы
     *
     * @param $_
     * @param array $args
     * @return bool
     */
    public function removeUser($_, array $args): bool
    {
        return GroupUser::where([['group_id', $args['group_id']], ['user_id', $args['user_id']]])->delete();
    }
}
