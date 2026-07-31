<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\ReportService;

class ReportController extends Controller
{
    public function __construct(private ReportService $service) {}

    private function guard(): void
    {
        if (!Session::has('user')) Response::redirect(base_url('login'));
    }

    public function index(): void
    {
        $this->guard();
        $this->view('pages/reports/index', ['title' => 'Relatórios - ' . app_name()] + $this->service->overview());
    }

    public function daily(): void
    {
        $this->guard();
        $date = (string) Request::get('data', date('Y-m-d'));
        $this->view('pages/reports/diario', ['title' => 'Relatório Diário - ' . app_name(), 'date' => $date] + $this->service->daily($date));
    }

    public function consolidated(): void
    {
        $this->guard();
        $start = (string) Request::get('inicio', date('Y-m-d', strtotime('-29 days')));
        $end = (string) Request::get('fim', date('Y-m-d'));
        $classId = (int) Request::get('turma', 0);
        $this->view('pages/reports/consolidado', ['title' => 'Relatório Consolidado - ' . app_name()] + $this->service->consolidated($start, $end, $classId ?: null));
    }


    public function occurrences(): void
    {
        $this->guard();
        $start = (string) Request::get('inicio', date('Y-m-d', strtotime('-29 days')));
        $end = (string) Request::get('fim', date('Y-m-d'));
        $classId = (int) Request::get('turma', 0);
        $type = (string) Request::get('tipo', '');
        $severity = (string) Request::get('gravidade', '');
        $status = (string) Request::get('status', '');
        $this->view('pages/reports/ocorrencias', ['title' => 'Relatório de Ocorrências - ' . app_name()] + $this->service->occurrenceReport($start, $end, $classId ?: null, $type ?: null, $severity ?: null, $status ?: null));
    }

    public function exportCsv(): never
    {
        $this->guard();
        $start = (string) Request::get('inicio', date('Y-m-d', strtotime('-29 days')));
        $end = (string) Request::get('fim', date('Y-m-d'));
        $classId = (int) Request::get('turma', 0);
        $data = $this->service->consolidated($start, $end, $classId ?: null);
        $filename = 'relatorio_frequencia_' . $data['startDate'] . '_a_' . $data['endDate'] . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'wb');
        fputcsv($out, ['Aluno', 'Matrícula', 'Turma', 'Registros', 'Presentes', 'Faltas sem justificativa', 'Faltas justificadas', 'Atestados médicos', 'Faltas de ônibus', 'Frequência (%)', 'Situação'], ';');
        foreach ($data['studentPerformance'] as $row) {
            $totalRecords = (int) ($row['total_records'] ?? 0);
            $percentage = (float) ($row['percentage'] ?? 0);
            $situation = $totalRecords === 0
                ? 'Sem registros'
                : ($percentage >= (float) $data['goal'] ? 'Dentro da meta' : 'Abaixo da meta');
            fputcsv($out, [
                $row['student_name'],
                $row['registration'],
                $row['class_name'],
                $totalRecords,
                $row['presentes'],
                $row['faltas'],
                $row['faltas_justificadas'],
                $row['atestados'],
                $row['faltas_onibus'],
                number_format($percentage, 1, ',', ''),
                $situation,
            ], ';');
        }
        fclose($out);
        exit;
    }
}
