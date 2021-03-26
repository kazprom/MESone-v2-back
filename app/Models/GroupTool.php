<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(array[] $array)
 */
class GroupTool extends Model
{
    use HasFactory;

    protected $table = 'group_tool';

    protected $fillable = [
        'group_id',
        'tool_id',
        'created_at',
        'updated_at',
    ];
}
