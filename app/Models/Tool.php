<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method create(array $payload)
 * @method static where(string $string, mixed $name)
 * @method static find(mixed $id)
 */
class Tool extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно назначать массово.
     *
     * @var string[]
     */
    protected $fillable = [
        'created_at',
        'updated_at',
        'name',
        'label',
        'description',
        'type',
        'enabled',
        'module_name',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }
}
