<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static where(string $string, mixed $group_id)
 */
class Group extends Model
{
    protected $fillable = ['name', 'description', 'enabled'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User');
    }

//    public function functions(): BelongsToMany
//    {
//        return $this->belongsToMany('App\Models\Functions', 'group_function', 'group_id', 'function_id');
//    }
}
