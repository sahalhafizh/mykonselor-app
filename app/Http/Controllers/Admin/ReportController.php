<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ResearchReportWorkbook;
use App\Http\Controllers\Controller;
use App\Services\ResearchReportService;
use App\Support\ResearchStudy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private ResearchReportService $reports) {}

    public function index(Request $request): View
    {
        [$from, $to] = $this->parseRange($request);

        $assessments = $this->reports->selected($from, $to)
            ->with(['user', 'result'])
            ->latest('completed_at')->orderByDesc('id')->paginate(20)->withQueryString();
        $summary = $this->reports->summary($from, $to);
        $metadata = $this->reports->metadata($from, $to);

        return view('admin.reports.index', compact('assessments', 'summary', 'metadata'));
    }

    public function exportExcel(Request $request)
    {
        [$rows, $from, $to, $anonymized] = $this->exportRows($request);
        $metadata = $this->reports->metadata($from, $to);
        $summary = $this->reports->summary($from, $to);
        $response = Excel::download(new ResearchReportWorkbook($rows, $anonymized, $metadata, $summary),
            'laporan-mykonselor-'.now()->format('Ymd-His').'.xlsx');
        $this->recordExport($request, 'xlsx', $rows->count(), $from, $to, $anonymized, $metadata);

        return $response;
    }

    public function exportPdf(Request $request)
    {
        [$assessments, $from, $to, $anonymized] = $this->exportRows($request);
        $metadata = $this->reports->metadata($from, $to);
        $summary = $this->reports->summary($from, $to);
        $response = Pdf::loadView('admin.reports.pdf', compact('assessments', 'from', 'to', 'anonymized', 'metadata', 'summary'))
            ->download('laporan-mykonselor-'.now()->format('Ymd-His').'.pdf');
        $this->recordExport($request, 'pdf', $assessments->count(), $from, $to, $anonymized, $metadata);

        return $response;
    }

    private function exportRows(Request $request): array
    {
        $request->validate(['from' => ['required', 'date_format:Y-m-d'], 'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'anonymized' => ['nullable', 'boolean']]);
        [$from, $to] = $this->parseRange($request);
        if (ResearchStudy::isResearch() && ! ResearchStudy::isReady()) {
            throw ValidationException::withMessages(['from' => 'Konfigurasi periode dan protokol penelitian belum lengkap.']);
        }
        if ($from->diffInDays($to) > max(1, config('security.export_max_days'))) {
            throw ValidationException::withMessages(['to' => 'Rentang ekspor terlalu panjang. Pilih periode yang lebih pendek.']);
        }
        $limit = max(1, min(10000, config('security.export_max_rows')));
        $rows = $this->reports->selected($from, $to)
            ->with(['user', 'result'])->latest('completed_at')->orderByDesc('id')->limit($limit + 1)->get();
        if ($rows->count() > $limit) {
            throw ValidationException::withMessages(['from' => 'Data melebihi batas '.$limit.' baris. Persempit tanggal ekspor.']);
        }

        return [$rows, $from, $to, $request->boolean('anonymized')];
    }

    private function recordExport(Request $request, string $format, int $count, $from, $to, bool $anonymized, array $metadata): void
    {
        DB::table('export_audit_events')->insert([
            'actor_id' => $request->user()->id, 'format' => $format, 'row_count' => $count,
            'from_at' => $from, 'to_at' => $to, 'anonymized' => $anonymized, 'created_at' => now(),
            'report_context' => json_encode($metadata, JSON_THROW_ON_ERROR),
        ]);
    }

    protected function parseRange(Request $request): array
    {
        $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])],
        ]);
        $timezone = config('app.display_timezone', 'Asia/Jakarta');
        $from = $request->filled('from') ? Carbon::parse($request->from, $timezone)->startOfDay()->utc() : null;
        $to = $request->filled('to') ? Carbon::parse($request->to, $timezone)->endOfDay()->utc() : null;

        return [$from, $to];
    }
}
