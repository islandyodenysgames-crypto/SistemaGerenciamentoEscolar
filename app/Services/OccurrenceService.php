<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Occurrence\OccurrenceService as ModularOccurrenceService;

/**
 * Classe de compatibilidade.
 *
 * Mantém funcionando o código que ainda utiliza:
 *
 * App\Services\OccurrenceService
 *
 * A implementação real está em:
 *
 * App\Services\Occurrence\OccurrenceService
 */
class OccurrenceService extends ModularOccurrenceService
{
}