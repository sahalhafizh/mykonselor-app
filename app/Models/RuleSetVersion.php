<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuleSetVersion extends Model
{
    public $timestamps = false;

    protected $fillable = ['fingerprint', 'snapshot'];

    protected function casts(): array
    {
        return ['snapshot' => 'array', 'created_at' => 'datetime'];
    }
}
