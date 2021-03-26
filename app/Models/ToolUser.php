<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(array[] $array)
 */
class ToolUser extends Model
{
    use HasFactory;

    protected $table = 'tool_user';

    protected $fillable = [
        'user_id',
        'tool_id',
        'created_at',
        'updated_at',
    ];
}
