<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use App\Models\DiseaseSymptom;
use App\Models\RuleAuditEvent;
use App\Models\Symptom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RuleManagementController extends Controller
{
    public function index(): View
    {
        $diseases = Disease::withCount('symptoms')->orderBy('kode')->get();
        $symptoms = Symptom::orderBy('kode')->get();

        return view('admin.rules.index', compact('diseases', 'symptoms'));
    }

    public function edit(Disease $disease): View
    {
        $allSymptoms = Symptom::orderBy('kode')->get();
        $existing = $disease->symptoms()->get()->keyBy('id');

        return view('admin.rules.edit', compact('disease', 'allSymptoms', 'existing'));
    }

    public function update(Request $request, Disease $disease): RedirectResponse
    {
        $validated = $request->validate([
            'symptoms' => ['required', 'array', 'max:21'],
            'symptoms.*' => ['array:selected,mb,md'],
            'symptoms.*.selected' => ['nullable', 'boolean'],
            'symptoms.*.mb' => ['required', 'numeric', 'in:0,1'],
            'symptoms.*.md' => ['required', 'numeric', 'in:0,1'],
        ]);
        $ids = array_keys($validated['symptoms']);
        if (collect($ids)->contains(fn ($id) => ! ctype_digit((string) $id))
            || Symptom::whereIn('id', $ids)->count() !== count($ids)) {
            throw ValidationException::withMessages(['symptoms' => 'Daftar gejala tidak valid. Muat ulang halaman.']);
        }
        DB::transaction(function () use ($validated, $disease, $request) {
            Disease::whereKey($disease->id)->lockForUpdate()->firstOrFail();
            $codes = Symptom::whereIn('id', array_keys($validated['symptoms']))->pluck('kode', 'id');
            foreach ($validated['symptoms'] as $id => $data) {
                $row = DiseaseSymptom::where('disease_id', $disease->id)->where('symptom_id', $id)->lockForUpdate()->first();
                $before = $row ? ['mb' => (float) $row->mb, 'md' => (float) $row->md, 'cf_pakar' => (float) $row->cf_pakar] : null;
                $selected = (bool) ($data['selected'] ?? false);
                if (! $selected && ! $row) {
                    continue;
                }
                if ($selected) {
                    $after = ['mb' => (float) $data['mb'], 'md' => (float) $data['md'], 'cf_pakar' => round($data['mb'] - $data['md'], 3)];
                    if ($before === $after) {
                        continue;
                    }
                    DiseaseSymptom::updateOrCreate(['disease_id' => $disease->id, 'symptom_id' => $id],
                        ['mb' => $after['mb'], 'md' => $after['md'], 'updated_by' => $request->user()->id]);
                } else {
                    $after = null;
                    $row->delete();
                }
                RuleAuditEvent::create([
                    'disease_id' => $disease->id, 'symptom_id' => $id, 'symptom_code' => $codes[$id],
                    'action' => ! $selected ? 'deleted' : ($before ? 'updated' : 'created'),
                    'before_values' => $before, 'after_values' => $after, 'actor_id' => $request->user()->id,
                ]);
            }
        }, 3);

        return to_route('admin.rules.edit', $disease)->with('status', 'Aturan berhasil disimpan dan riwayat perubahannya dicatat.');
    }

    public function auditLog(Disease $disease): View
    {
        $logs = RuleAuditEvent::where('disease_id', $disease->id)->with('actor')->latest('id')->paginate(20);

        return view('admin.rules.audit-log', compact('disease', 'logs'));
    }
}
