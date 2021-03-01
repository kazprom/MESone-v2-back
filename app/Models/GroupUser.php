<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(array[] $array)
 * @method static insert(array $array)
 */
class GroupUser extends Model
{
    protected $table = 'group_user';
    protected $fillable = ['group_id', 'user_id'];
}
