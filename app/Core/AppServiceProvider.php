<?php

declare(strict_types=1);

namespace App\Core;

use App\Database\Connection;
use App\Repositories\AttendanceRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\SchoolClassRepository;
use App\Repositories\StudentRepository;
use App\Repositories\UserRepository;
use App\Services\AttendanceAnalyticsService;
use App\Services\AttendanceService;
use App\Services\AuthService;
use App\Services\DashboardService;
use App\Services\EnrollmentService;
use App\Services\ReportService;
use App\Services\SchoolClassService;
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
        $container->singleton(EnrollmentRepository::class);
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
        $container->singleton(ReportService::class);
        $container->singleton(SchoolClassService::class);
        $container->singleton(SearchService::class);
        $container->singleton(StudentService::class);
        $container->singleton(UserService::class);
    }
}