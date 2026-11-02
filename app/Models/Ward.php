<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $department_id
 * @property string $name
 * @property string|null $colour
 */
class Ward extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'colour',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
