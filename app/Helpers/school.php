<?php

declare(strict_types=1);

use App\Repositories\SchoolRepository;
use App\Services\SchoolService;

if (!function_exists('school')) {

    function school(?string $key = null, mixed $default = null): mixed
    {
        static $school = null;

        if ($school === null) {

            $repository = new SchoolRepository();

            $service = new SchoolService(
                $repository
            );

            $school = $service->current();

        }

        if ($key === null) {
            return $school;
        }

        return $school[$key] ?? $default;
    }

}