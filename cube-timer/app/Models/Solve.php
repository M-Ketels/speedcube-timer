<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Solve extends Model
{
    protected $fillable = [
        'user_id',
        'puzzle_type',
        'solve_time_ms',
        'scramble',
        'penalty'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
