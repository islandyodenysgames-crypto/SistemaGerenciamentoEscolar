<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Occurrence\OccurrenceRepository as ModularOccurrenceRepository;

/**
 * Classe de compatibilidade.
 *
 * Mantém funcionando o código que ainda utiliza:
 *
 * App\Repositories\OccurrenceRepository
 *
 * A implementação real está em:
 *
 * App\Repositories\Occurrence\OccurrenceRepository
 */
class OccurrenceRepository extends ModularOccurrenceRepository
{
}