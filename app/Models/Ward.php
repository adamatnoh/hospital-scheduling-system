<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
