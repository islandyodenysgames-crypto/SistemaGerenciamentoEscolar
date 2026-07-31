<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Occurrence\ActionRepository;

/**
 * Classe de compatibilidade.
 *
 * Mantém funcionando o código que ainda utiliza:
 *
 * App\Repositories\OccurrenceActionRepository
 *
 * A implementação real está em:
 *
 * App\Repositories\Occurrence\ActionRepository
 */
class OccurrenceActionRepository extends ActionRepository
{
}