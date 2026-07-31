<?php

declare(strict_types=1);

use App\Controllers\AttendanceController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DataMaintenanceController;
use App\Controllers\EnrollmentController;
use App\Controllers\FavoriteController;
use App\Controllers\IntelligenceController;
use App\Controllers\IntelligenceSettingsController;
use App\Controllers\IntelligentContentController;
use App\Controllers\NoticeController;
use App\Controllers\NotificationController;
use App\Controllers\OccurrenceController;
use App\Controllers\OccurrenceDashboardController;
use App\Controllers\ReportController;
use App\Controllers\SchoolClassController;
use App\Controllers\SchoolCalendarController;
use App\Controllers\SchoolSettingsController;
use App\Controllers\SchoolGoalsSettingsController;
use App\Controllers\SchoolYearController;
use App\Controllers\SearchController;
use App\Controllers\SettingsController;
use App\Controllers\StudentController;
use App\Controllers\StudentMonitoringController;
use App\Controllers\UserController;
use App\Controllers\SubjectController;
use App\Controllers\TvPanelController;

/** @var \App\Core\Router $router */

// ======================================================
// Dashboard
// ======================================================

$router->get(
    '/',
    [DashboardController::class, 'index']
);

$router->get(
    '/dashboard/frequencia-evolucao',
    [DashboardController::class, 'frequencyEvolution']
);

$router->get(
    '/dashboard/mapa-calor-frequencia',
    [DashboardController::class, 'frequencyHeatmap']
);

$router->get(
    '/inteligencia',
    [IntelligenceController::class, 'index']
);

$router->get(
    '/inteligencia/casos',
    [IntelligenceController::class, 'cases']
);

$router->get(
    '/inteligencia/comparacoes',
    [IntelligenceController::class, 'comparisons']
);

$router->get(
    '/configuracoes/inteligencia',
    [IntelligenceSettingsController::class, 'index']
);

$router->post(
    '/configuracoes/inteligencia',
    [IntelligenceSettingsController::class, 'update']
);

$router->post(
    '/configuracoes/inteligencia/restaurar',
    [IntelligenceSettingsController::class, 'reset']
);

$router->get('/configuracoes/metas', [SchoolGoalsSettingsController::class, 'index']);
$router->post('/configuracoes/metas', [SchoolGoalsSettingsController::class, 'update']);
$router->post('/configuracoes/metas/restaurar', [SchoolGoalsSettingsController::class, 'reset']);

$router->get('/configuracoes/ano-letivo', [SchoolYearController::class, 'index']);
$router->post('/configuracoes/ano-letivo', [SchoolYearController::class, 'store']);
$router->post('/configuracoes/ano-letivo/atualizar', [SchoolYearController::class, 'update']);
$router->post('/configuracoes/ano-letivo/ativar', [SchoolYearController::class, 'activate']);
$router->post('/configuracoes/ano-letivo/encerrar', [SchoolYearController::class, 'close']);
$router->post('/configuracoes/ano-letivo/arquivar', [SchoolYearController::class, 'archive']);
$router->post('/configuracoes/ano-letivo/reabrir', [SchoolYearController::class, 'reopen']);
$router->post('/configuracoes/ano-letivo/periodos/salvar', [SchoolYearController::class, 'savePeriod']);
$router->post('/configuracoes/ano-letivo/periodos/excluir', [SchoolYearController::class, 'deletePeriod']);
$router->post('/configuracoes/ano-letivo/dias/gerar', [SchoolYearController::class, 'generateDays']);
$router->post('/configuracoes/ano-letivo/dias/salvar', [SchoolYearController::class, 'saveDay']);
$router->post('/configuracoes/ano-letivo/periodos/fechar', [SchoolYearController::class, 'closePeriod']);
$router->post('/configuracoes/ano-letivo/periodos/reabrir', [SchoolYearController::class, 'reopenPeriod']);
$router->post('/configuracoes/ano-letivo/encerrar-seguro', [SchoolYearController::class, 'closeYearSafely']);
$router->post('/configuracoes/ano-letivo/assistente', [SchoolYearController::class, 'createNextYear']);



// ======================================================
// Painel TV Institucional
// ======================================================
$router->get('/painel-tv', [TvPanelController::class, 'show']);
$router->get('/painel-tv/dados', [TvPanelController::class, 'data']);
$router->get('/painel-tv/configuracoes', [TvPanelController::class, 'settings']);
$router->post('/painel-tv/configuracoes', [TvPanelController::class, 'save']);
$router->get('/configuracoes/conteudo-inteligente', [IntelligentContentController::class, 'index']);
$router->post('/configuracoes/conteudo-inteligente/aprovar', [IntelligentContentController::class, 'approve']);
$router->post('/configuracoes/conteudo-inteligente/publicar', [IntelligentContentController::class, 'publish']);
$router->post('/configuracoes/conteudo-inteligente/descartar', [IntelligentContentController::class, 'discard']);
$router->post('/configuracoes/conteudo-inteligente/editar', [IntelligentContentController::class, 'edit']);
$router->post('/configuracoes/conteudo-inteligente/limpar-cache', [IntelligentContentController::class, 'clearCache']);

// ======================================================
// Calendário Escolar
// ======================================================

$router->get('/calendario', [SchoolCalendarController::class, 'index']);
$router->get('/calendario/novo', [SchoolCalendarController::class, 'create']);
$router->post('/calendario', [SchoolCalendarController::class, 'store']);
$router->get('/calendario/editar', [SchoolCalendarController::class, 'edit']);
$router->post('/calendario/editar', [SchoolCalendarController::class, 'update']);
$router->post('/calendario/excluir', [SchoolCalendarController::class, 'delete']);

// ======================================================
// Autenticação
// ======================================================

$router->get(
    '/login',
    [AuthController::class, 'login']
);

$router->post(
    '/login',
    [AuthController::class, 'authenticate']
);

$router->get(
    '/logout',
    [AuthController::class, 'logout']
);

// ======================================================
// Usuários
// ======================================================

$router->get(
    '/usuarios',
    [UserController::class, 'index']
);

$router->get(
    '/usuarios/novo',
    [UserController::class, 'create']
);

$router->post(
    '/usuarios',
    [UserController::class, 'store']
);

$router->get(
    '/usuarios/editar',
    [UserController::class, 'edit']
);

$router->post(
    '/usuarios/editar',
    [UserController::class, 'update']
);

$router->post(
    '/usuarios/excluir',
    [UserController::class, 'delete']
);

// ======================================================
// Disciplinas
// ======================================================

$router->get(
    '/disciplinas',
    [SubjectController::class, 'index']
);

$router->get(
    '/disciplinas/nova',
    [SubjectController::class, 'create']
);

$router->post(
    '/disciplinas',
    [SubjectController::class, 'store']
);

$router->get(
    '/disciplinas/editar',
    [SubjectController::class, 'edit']
);

$router->post(
    '/disciplinas/editar',
    [SubjectController::class, 'update']
);

$router->post(
    '/disciplinas/ativar',
    [SubjectController::class, 'activate']
);

$router->post(
    '/disciplinas/inativar',
    [SubjectController::class, 'deactivate']
);

$router->post(
    '/disciplinas/excluir',
    [SubjectController::class, 'delete']
);

// ======================================================
// Turmas
// ======================================================

$router->get(
    '/turmas',
    [SchoolClassController::class, 'index']
);

$router->get(
    '/turmas/novo',
    [SchoolClassController::class, 'create']
);

$router->post(
    '/turmas',
    [SchoolClassController::class, 'store']
);

$router->get(
    '/turmas/editar',
    [SchoolClassController::class, 'edit']
);

$router->post(
    '/turmas/editar',
    [SchoolClassController::class, 'update']
);

$router->post(
    '/turmas/excluir',
    [SchoolClassController::class, 'delete']
);

// ======================================================
// Alunos
// ======================================================

$router->get(
    '/alunos',
    [StudentController::class, 'index']
);

$router->get(
    '/alunos/novo',
    [StudentController::class, 'create']
);

$router->post(
    '/alunos',
    [StudentController::class, 'store']
);

$router->get(
    '/alunos/editar',
    [StudentController::class, 'edit']
);

$router->post(
    '/alunos/editar',
    [StudentController::class, 'update']
);

$router->post(
    '/alunos/excluir',
    [StudentController::class, 'delete']
);

$router->post(
    '/alunos/excluir-selecionados',
    [StudentController::class, 'deleteSelected']
);

$router->get(
    '/alunos/turma',
    [StudentController::class, 'classStudents']
);

$router->get(
    '/alunos/perfil',
    [StudentController::class, 'profile']
);

$router->get(
    '/alunos/relatorio',
    [StudentController::class, 'report']
);


$router->get('/acompanhamentos', [StudentMonitoringController::class, 'index']);
$router->post('/acompanhamentos/iniciar', [StudentMonitoringController::class, 'store']);
$router->post('/acompanhamentos/encerrar', [StudentMonitoringController::class, 'stop']);
$router->post('/acompanhamentos/prorrogar', [StudentMonitoringController::class, 'extend']);
$router->post('/acompanhamentos/plano/salvar', [StudentMonitoringController::class, 'savePlan']);
$router->post('/acompanhamentos/acompanhantes/adicionar', [StudentMonitoringController::class, 'addParticipants']);
$router->post('/acompanhamentos/acoes/registrar', [StudentMonitoringController::class, 'storeAction']);
$router->post('/acompanhamentos/faltas/reabrir', [StudentMonitoringController::class, 'reopenAbsenceTreatment']);
$router->post('/acompanhamentos/acoes/atualizar', [StudentMonitoringController::class, 'updateAction']);
$router->post('/acompanhamentos/acoes/excluir', [StudentMonitoringController::class, 'deleteAction']);
$router->post('/acompanhamentos/acoes/anexos/excluir', [StudentMonitoringController::class, 'deleteActionAttachment']);
$router->post('/acompanhamentos/vinculo-concluido/excluir', [StudentMonitoringController::class, 'deleteConcludedLink']);
$router->post('/acompanhamentos/recomendacoes/atualizar', [StudentMonitoringController::class, 'updateRecommendation']);

// ======================================================
// Ocorrências
// ======================================================

$router->get(
    '/ocorrencias/providencia',
    [OccurrenceController::class, 'actionCreate']
);

$router->post(
    '/ocorrencias/providencia',
    [OccurrenceController::class, 'actionStore']
);

$router->post('/ocorrencias/providencia/anexo/excluir', [OccurrenceController::class, 'actionAttachmentDelete']);

$router->get(
    '/ocorrencias',
    [OccurrenceDashboardController::class, 'index']
);

$router->get(
    '/ocorrencias/nova',
    [OccurrenceController::class, 'create']
);

$router->post(
    '/ocorrencias',
    [OccurrenceController::class, 'store']
);

$router->get(
    '/ocorrencias/editar',
    [OccurrenceController::class, 'edit']
);

$router->post(
    '/ocorrencias/editar',
    [OccurrenceController::class, 'update']
);

$router->post(
    '/ocorrencias/resolver',
    [OccurrenceController::class, 'resolve']
);

$router->post(
    '/ocorrencias/reabrir',
    [OccurrenceController::class, 'reopen']
);

$router->post(
    '/ocorrencias/excluir',
    [OccurrenceController::class, 'delete']
);

$router->post(
    '/ocorrencias/anexos/excluir',
    [OccurrenceController::class, 'deleteAttachment']
);

/*
 * Abre novamente o formulário após um erro,
 * recuperando alunos e dados pela sessão.
 */
$router->get(
    '/ocorrencias/multiplas/nova',
    [OccurrenceController::class, 'createMultiple']
);

/*
 * Recebe inicialmente os alunos selecionados
 * no painel da turma.
 */
$router->post(
    '/ocorrencias/multiplas/nova',
    [OccurrenceController::class, 'createMultiple']
);

/*
 * Salva a ocorrência para todos os alunos.
 */
$router->post(
    '/ocorrencias/multiplas',
    [OccurrenceController::class, 'storeMultiple']
);


// ======================================================
// Notificações Inteligentes
// ======================================================

$router->get(
    '/notificacoes',
    [NotificationController::class, 'index']
);

$router->get(
    '/notificacoes/resumo',
    [NotificationController::class, 'summary']
);

$router->get(
    '/notificacoes/abrir',
    [NotificationController::class, 'open']
);

$router->post(
    '/notificacoes/ler',
    [NotificationController::class, 'read']
);

$router->post(
    '/notificacoes/ler-todas',
    [NotificationController::class, 'readAll']
);

$router->post(
    '/notificacoes/excluir',
    [NotificationController::class, 'delete']
);

// ======================================================
// Matrículas
// ======================================================

$router->get(
    '/matriculas',
    [EnrollmentController::class, 'index']
);

$router->get(
    '/matriculas/novo',
    [EnrollmentController::class, 'create']
);

$router->post(
    '/matriculas',
    [EnrollmentController::class, 'store']
);

$router->post(
    '/matriculas/cancelar',
    [EnrollmentController::class, 'cancel']
);

$router->get(
    '/matriculas/importar',
    [EnrollmentController::class, 'import']
);

$router->post(
    '/matriculas/importar',
    [EnrollmentController::class, 'importStore']
);

$router->post(
    '/matriculas/importar/confirmar',
    [EnrollmentController::class, 'importConfirm']
);

// ======================================================
// Frequência
// ======================================================

$router->get(
    '/frequencia',
    [AttendanceController::class, 'index']
);

$router->get(
    '/frequencia/novo',
    [AttendanceController::class, 'create']
);

$router->post(
    '/frequencia',
    [AttendanceController::class, 'store']
);

$router->get(
    '/frequencia/ver',
    [AttendanceController::class, 'show']
);

$router->get(
    '/frequencia/editar',
    [AttendanceController::class, 'edit']
);

$router->post(
    '/frequencia/editar',
    [AttendanceController::class, 'update']
);

$router->post(
    '/frequencia/excluir',
    [AttendanceController::class, 'delete']
);

$router->get(
    '/frequencia/historico',
    [AttendanceController::class, 'history']
);

$router->post(
    '/frequencia/excluir-selecionadas',
    [AttendanceController::class, 'deleteSelected']
);

// ======================================================
// Relatórios
// ======================================================

$router->get(
    '/relatorios',
    [ReportController::class, 'index']
);

$router->get(
    '/relatorios/diario',
    [ReportController::class, 'daily']
);

$router->get(
    '/relatorios/consolidado',
    [ReportController::class, 'consolidated']
);

$router->get(
    '/relatorios/ocorrencias',
    [ReportController::class, 'occurrences']
);

$router->get(
    '/relatorios/exportar-csv',
    [ReportController::class, 'exportCsv']
);

// ======================================================
// Busca Inteligente
// ======================================================

$router->get(
    '/busca',
    [SearchController::class, 'index']
);

$router->get('/busca/sugestoes', [SearchController::class, 'suggestions']);
$router->post('/favoritos/alternar', [FavoriteController::class, 'toggle']);

// ======================================================
// Configurações
// ======================================================

$router->get(
    '/configuracoes',
    [SettingsController::class, 'index']
);

$router->get('/configuracoes/dados', [DataMaintenanceController::class, 'index']);
$router->post('/configuracoes/dados/exportar', [DataMaintenanceController::class, 'export']);
$router->post('/configuracoes/dados/restaurar', [DataMaintenanceController::class, 'restore']);
$router->post('/configuracoes/dados/limpar', [DataMaintenanceController::class, 'clear']);

$router->get(
    '/configuracoes/identidade',
    [SchoolSettingsController::class, 'identity']
);

$router->post(
    '/configuracoes/identidade',
    [SchoolSettingsController::class, 'update']
);

// ======================================================
// Avisos da Gestão
// ======================================================

$router->get(
    '/avisos',
    [NoticeController::class, 'index']
);

$router->get(
    '/avisos/novo',
    [NoticeController::class, 'create']
);

$router->post(
    '/avisos',
    [NoticeController::class, 'store']
);

$router->get(
    '/avisos/editar',
    [NoticeController::class, 'edit']
);

$router->post(
    '/avisos/editar',
    [NoticeController::class, 'update']
);

$router->post(
    '/avisos/ativar',
    [NoticeController::class, 'activate']
);

$router->post(
    '/avisos/arquivar',
    [NoticeController::class, 'archive']
);

$router->post(
    '/avisos/fixar',
    [NoticeController::class, 'pin']
);

$router->post(
    '/avisos/desafixar',
    [NoticeController::class, 'unpin']
);

$router->post(
    '/avisos/excluir',
    [NoticeController::class, 'delete']
);

$router->post(
    '/avisos/anexos/excluir',
    [NoticeController::class, 'deleteAttachment']
);
