<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Disease extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode', 'nama', 'cluster_key', 'keterangan_singkat',
        'panduan_normal_ringan', 'panduan_sedang', 'panduan_berat',
    ];

    public function symptoms(): BelongsToMany
    {
        return $this->belongsToMany(Symptom::class, 'disease_symptom')
            ->withPivot(['rule_code', 'mb', 'md', 'cf_pakar', 'updated_by'])
            ->withTimestamps();
    }

    public function panduanUntuk(string $severity): string
    {
        return match ($severity) {
            'sedang' => $this->panduan_sedang,
            'berat' => $this->panduan_berat,
            default => $this->panduan_normal_ringan,
        };
    }

    public static function findByCluster(string $clusterKey): ?self
    {
        return static::where('cluster_key', $clusterKey)->first();
    }
}
