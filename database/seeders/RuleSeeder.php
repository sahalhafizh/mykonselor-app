<?php

namespace Database\Seeders;

use App\Models\Disease;
use App\Models\DiseaseSymptom;
use App\Models\Symptom;
use App\Services\CertaintyFactorService;
use Illuminate\Database\Seeder;

class RuleSeeder extends Seeder
{
    public function run(): void
    {
        $clusterMap = [
            'stress' => ['G01', 'G06', 'G08', 'G11', 'G12', 'G14', 'G18'],
            'anxiety' => ['G02', 'G04', 'G07', 'G09', 'G15', 'G19', 'G20'],
            'depression' => ['G03', 'G05', 'G10', 'G13', 'G16', 'G17', 'G21'],
        ];

        $specialMbMd = [
            'G02' => ['mb' => 0, 'md' => 1],
            'G05' => ['mb' => 0, 'md' => 1],
        ];

        $cf = app(CertaintyFactorService::class);

        foreach ($clusterMap as $clusterKey => $symptomCodes) {
            $disease = Disease::where('cluster_key', $clusterKey)->firstOrFail();

            foreach ($symptomCodes as $index => $code) {
                $symptom = Symptom::where('kode', $code)->firstOrFail();
                $mb = $specialMbMd[$code]['mb'] ?? 1;
                $md = $specialMbMd[$code]['md'] ?? 0;

                DiseaseSymptom::updateOrCreate(
                    ['disease_id' => $disease->id, 'symptom_id' => $symptom->id],
                    [
                        'rule_code' => sprintf('R%02d', $index + 1),
                        'mb' => $mb, 'md' => $md,
                        'cf_pakar' => $cf->calculateExpertCf($mb, $md),
                    ]
                );
            }
        }
    }
}
