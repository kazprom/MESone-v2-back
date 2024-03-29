<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static where(string $string, mixed $group_id)
 * @method static find($id)
 */
class Group extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно назначать массово.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'enabled',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(Tool::class);
    }
}
