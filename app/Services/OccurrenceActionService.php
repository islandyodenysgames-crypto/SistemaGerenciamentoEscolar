<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Occurrence\ActionService;

/**
 * Classe de compatibilidade.
 *
 * Mantém funcionando o código que ainda utiliza:
 *
 * App\Services\OccurrenceActionService
 *
 * A implementação real está em:
 *
 * App\Services\Occurrence\ActionService
 */
class OccurrenceActionService extends ActionService
{
}