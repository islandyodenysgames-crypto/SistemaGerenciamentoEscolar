<?php

declare(strict_types=1);

namespace App\Core;

use App\Database\Connection;
use App\Core\Settings\Contracts\SettingCacheInterface;
use App\Core\Settings\Contracts\SettingProviderInterface;
use App\Core\Settings\SettingCache;
use App\Core\Settings\SettingManager;
use App\Core\Settings\SettingValidator;
use App\Repositories\Settings\SettingRepository;
use App\Services\Settings\SettingService;
use App\Repositories\AttendanceRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\Intelligence\IntelligenceRepository;
use App\Repositories\Intelligence\IntelligenceDailySnapshotRepository;
use App\Repositories\Intelligence\StudentIntelligenceSnapshotRepository;
use App\Repositories\Monitoring\StudentMonitoringRepository;
use App\Repositories\Occurrence\NotificationRepository;
use App\Repositories\Occurrence\OccurrenceRepository;
use App\Repositories\SchoolClassRepository;
use App\Repositories\SchoolCalendarRepository;
use App\Repositories\StudentRepository;
use App\Repositories\UserRepository;
use App\Services\AttendanceAnalyticsService;
use App\Services\AttendanceService;
use App\Services\AuthService;
use App\Services\DashboardService;
use App\Services\EnrollmentService;
use App\Services\Intelligence\IntelligenceService;
use App\Services\Intelligence\IntelligenceDailySnapshotService;
use App\Services\Intelligence\StudentIntelligenceAlertService;
use App\Services\Intelligence\TrendAnalysisService;
use App\Services\Intelligence\PredictiveAnalysisService;
use App\Services\Intelligence\ClassTrendAnalysisService;
use App\Services\Intelligence\ClassPredictiveAnalysisService;
use App\Services\Monitoring\StudentMonitoringService;
use App\Rules\Occurrence\RecurrentStudentRule;
use App\Rules\Occurrence\RuleRegistry;
use App\Services\Occurrence\OccurrenceAnalysisContextFactory;
use App\Services\Occurrence\NotificationService;
use App\Services\Occurrence\OccurrenceIndicatorService;
use App\Services\Occurrence\OccurrenceAnalysisService;
use App\Services\Occurrence\OccurrenceService;
use App\Services\Occurrence\SeverityService;
use App\Services\ReportService;
use App\Services\SchoolClassService;
use App\Services\SchoolCalendarService;
use App\Services\SearchService;
use App\Services\StudentService;
use App\Services\UserService;

class AppServiceProvider
{
    public static function register(Container $container): void
    {
        /*
        |--------------------------------------------------------------------------
        | Shared instances
        |--------------------------------------------------------------------------
        */

        $container->instance(
            Connection::class,
            Connection::getInstance()
        );

        /*
        |--------------------------------------------------------------------------
        | Repositories
        |--------------------------------------------------------------------------
        */

        $container->singleton(AttendanceRepository::class);
        $container->singleton(StudentRepository::class);
        $container->singleton(SchoolClassRepository::class);
        $container->singleton(SchoolCalendarRepository::class);
        $container->singleton(EnrollmentRepository::class);
        $container->singleton(IntelligenceRepository::class);
        $container->singleton(IntelligenceDailySnapshotRepository::class);
        $container->singleton(StudentIntelligenceSnapshotRepository::class);
        $container->singleton(StudentMonitoringRepository::class);
        $container->singleton(SettingRepository::class);
        $container->singleton(OccurrenceRepository::class);
        $container->singleton(NotificationRepository::class);
        $container->singleton(UserRepository::class);

        /*
        |--------------------------------------------------------------------------
        | Services
        |--------------------------------------------------------------------------
        */

        $container->singleton(AuthService::class);
        $container->singleton(AttendanceService::class);
        $container->singleton(AttendanceAnalyticsService::class);
        $container->singleton(DashboardService::class);
        $container->singleton(EnrollmentService::class);
        $container->singleton(IntelligenceService::class);
        $container->singleton(IntelligenceDailySnapshotService::class);
        $container->singleton(StudentIntelligenceAlertService::class);
        $container->singleton(TrendAnalysisService::class);
        $container->singleton(PredictiveAnalysisService::class);
        $container->singleton(ClassTrendAnalysisService::class);
        $container->singleton(ClassPredictiveAnalysisService::class);
        $container->singleton(StudentMonitoringService::class);
        $container->singleton(SettingCache::class);
        $container->singleton(SettingValidator::class);
        $container->singleton(SettingManager::class);
        $container->singleton(SettingService::class);
        $container->singleton(ReportService::class);
        $container->singleton(SchoolClassService::class);
        $container->singleton(SchoolCalendarService::class);
        $container->singleton(SearchService::class);
        $container->singleton(StudentService::class);
        $container->singleton(UserService::class);
        $container->singleton(SeverityService::class);
        $container->singleton(OccurrenceAnalysisContextFactory::class);

        $container->singleton(
            RecurrentStudentRule::class,
            static function (Container $container): RecurrentStudentRule {
                $settings = $container->resolve(SettingManager::class);
                return new RecurrentStudentRule(
                    minimumOccurrences: max(2, (int) $settings->get('intelligence.recurrence_limit', 2)),
                    periodDays: max(1, (int) $settings->get('intelligence.analysis_window_days', 30))
                );
            }
        );
        $container->singleton(
            RuleRegistry::class,
            static fn (Container $container): RuleRegistry =>
                new RuleRegistry([
                    $container->resolve(RecurrentStudentRule::class),
                ])
        );

        $container->singleton(OccurrenceAnalysisService::class);
        $container->singleton(NotificationService::class);
        $container->singleton(OccurrenceIndicatorService::class);
        $container->singleton(OccurrenceService::class);
        $container->singleton(
            SettingProviderInterface::class,
            static fn (Container $container): SettingProviderInterface =>
                $container->resolve(SettingRepository::class)
        );
        $container->singleton(
            SettingCacheInterface::class,
            static fn (Container $container): SettingCacheInterface =>
                $container->resolve(SettingCache::class)
        );
    }
}