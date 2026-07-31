<?php

declare(strict_types=1);

namespace App\Auth;

class Permissions
{
    /*
     * Dashboard
     */
    public const DASHBOARD_VIEW = 'dashboard.view';

    public const INTELLIGENCE_VIEW = 'intelligence.view';

    /*
     * Usuários
     */
    public const USERS_MANAGE = 'users.manage';

    /*
     * Configurações
     */
    public const SETTINGS_MANAGE = 'settings.manage';

    /*
     * Alunos / Turmas
     */
    public const STUDENTS_VIEW = 'students.view';

    public const STUDENTS_MANAGE = 'students.manage';

    public const STUDENT_MONITORING_VIEW = 'student_monitoring.view';

    public const STUDENT_MONITORING_MANAGE = 'student_monitoring.manage';

    /*
     * Matrículas
     */
    public const ENROLLMENTS_VIEW = 'enrollments.view';

    public const ENROLLMENTS_MANAGE = 'enrollments.manage';

    /*
     * Frequência
     */
    public const ATTENDANCE_VIEW = 'attendance.view';

    public const ATTENDANCE_MANAGE = 'attendance.manage';

    /*
     * Ocorrências
     */
    public const OCCURRENCES_VIEW = 'occurrences.view';

    public const OCCURRENCES_CREATE = 'occurrences.create';

    public const OCCURRENCES_MANAGE = 'occurrences.manage';

    /*
    * Disciplinas
    */
    public const SUBJECTS_VIEW = 'subjects.view';
    
    public const SUBJECTS_MANAGE = 'subjects.manage';

    /*
     * Avisos
     */
    public const NOTICES_VIEW = 'notices.view';

    public const NOTICES_MANAGE = 'notices.manage';

    /*
     * Calendário Escolar
     */
    public const CALENDAR_VIEW = 'calendar.view';
    public const CALENDAR_MANAGE = 'calendar.manage';

    /*
     * Relatórios
     */
    public const REPORTS_VIEW = 'reports.view';

    /**
     * Todas as permissões do sistema.
     */
    public static function all(): array
    {
        return [

            self::DASHBOARD_VIEW,
            self::INTELLIGENCE_VIEW,

            self::USERS_MANAGE,

            self::SETTINGS_MANAGE,

            self::STUDENTS_VIEW,
            self::STUDENTS_MANAGE,
            self::STUDENT_MONITORING_VIEW,
            self::STUDENT_MONITORING_MANAGE,

            self::ENROLLMENTS_VIEW,
            self::ENROLLMENTS_MANAGE,

            self::ATTENDANCE_VIEW,
            self::ATTENDANCE_MANAGE,

            self::OCCURRENCES_VIEW,
            self::OCCURRENCES_CREATE,
            self::OCCURRENCES_MANAGE,
            self::SUBJECTS_VIEW,
            self::SUBJECTS_MANAGE,

            self::NOTICES_VIEW,
            self::NOTICES_MANAGE,
            self::CALENDAR_VIEW,
            self::CALENDAR_MANAGE,

            self::REPORTS_VIEW,
        ];
    }

    /**
     * Permissões por perfil.
     */
    public static function forRole(string $role): array
    {
        $role = Roles::normalize($role);

        /*
         * Administrador
         * Direção
         * Coordenação
         *
         * Acesso total.
         */
        if (Roles::hasFullAccess($role)) {
            return self::all();
        }

        return match ($role) {

            /*
             * Secretaria
             */
            Roles::SECRETARY => [

                self::DASHBOARD_VIEW,
                self::INTELLIGENCE_VIEW,

                /*
                 * Alunos
                 */
                self::STUDENTS_VIEW,
                self::STUDENTS_MANAGE,

                /*
                 * Matrículas
                 */
                self::ENROLLMENTS_VIEW,
                self::ENROLLMENTS_MANAGE,

                /*
                 * Frequência
                 */
                self::ATTENDANCE_VIEW,
                self::ATTENDANCE_MANAGE,

                /*
                 * Ocorrências
                 *
                 * Apenas consulta.
                 */
                self::OCCURRENCES_VIEW,
                self::SUBJECTS_VIEW,

                /*
                 * Avisos
                 *
                 * Apenas consulta.
                 */
                self::NOTICES_VIEW,
                self::CALENDAR_VIEW,

                /*
                 * Relatórios
                 */
                self::REPORTS_VIEW,
            ],

            /*
             * Professor
             */
            Roles::TEACHER => [

                self::DASHBOARD_VIEW,

                /*
                 * Apenas consulta alunos.
                 */
                self::STUDENTS_VIEW,
                self::STUDENT_MONITORING_VIEW,
                self::STUDENT_MONITORING_MANAGE,

                /*
                 * Não possui acesso
                 * ao módulo de frequência.
                 */

                /*
                 * Pode registrar ocorrências.
                 */
                self::OCCURRENCES_VIEW,
                self::OCCURRENCES_CREATE,
                self::SUBJECTS_VIEW,

                /*
                 * Pode apenas visualizar avisos.
                 */
                self::NOTICES_VIEW,
                self::CALENDAR_VIEW,

                /*
                 * Pode emitir relatórios.
                 */
                self::REPORTS_VIEW,
            ],

            default => [],
        };
    }

    /**
     * Verifica se determinado perfil
     * possui uma permissão.
     */
    public static function roleHas(
        string $role,
        string $permission
    ): bool {

        return in_array(
            $permission,
            self::forRole($role),
            true
        );
    }
}