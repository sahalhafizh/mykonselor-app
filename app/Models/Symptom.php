<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Symptom extends Model
{
    use HasFactory;

    protected $fillable = ['kode', 'deskripsi', 'kategori'];

    public function diseases(): BelongsToMany
    {
        return $this->belongsToMany(Disease::class, 'disease_symptom')
            ->withPivot(['rule_code', 'mb', 'md', 'cf_pakar', 'updated_by'])
            ->withTimestamps();
    }
}
