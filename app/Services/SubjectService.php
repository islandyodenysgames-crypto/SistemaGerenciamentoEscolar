<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Repositories\SubjectRepository;
use InvalidArgumentException;
use Throwable;

class SubjectService
{
    public const GENERAL_CODE = 'GERAL';

    public function __construct(
        private SubjectRepository $repository
    ) {
    }

    /**
     * Retorna todas as disciplinas.
     */
    public function all(): array
    {
        $subjects = $this->repository->all();

        return $this->normalizeSubjects(
            $subjects
        );
    }

    /**
     * Retorna somente as disciplinas ativas.
     */
    public function active(): array
    {
        $subjects = $this->repository->active();

        return $this->normalizeSubjects(
            $subjects
        );
    }

    /**
     * Localiza uma disciplina pelo ID.
     */
    public function find(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $subject = $this->repository->find($id);

        if (!$subject) {
            return null;
        }

        return $this->normalizeSubject(
            $subject
        );
    }

    /**
     * Localiza uma disciplina pelo código.
     */
    public function findByCode(
        string $code
    ): ?array {
        $code = $this->normalizeCode($code);

        if ($code === '') {
            return null;
        }

        $subject = $this->repository
            ->findByCode($code);

        if (!$subject) {
            return null;
        }

        return $this->normalizeSubject(
            $subject
        );
    }

    /**
     * Retorna a disciplina padrão
     * Geral da Escola.
     */
    public function general(): ?array
    {
        $subject = $this->repository->general();

        if (!$subject) {
            return null;
        }

        return $this->normalizeSubject(
            $subject
        );
    }

    /**
     * Retorna o ID da disciplina padrão.
     */
    public function generalId(): ?int
    {
        $subject = $this->general();

        $id = (int) (
            $subject['id'] ?? 0
        );

        return $id > 0
            ? $id
            : null;
    }

    /**
     * Cria uma nova disciplina.
     */
    public function create(array $data): int
    {
        $normalized = $this->normalizeData(
            $data
        );

        if (
            $this->repository->nameExists(
                $normalized['name']
            )
        ) {
            throw new InvalidArgumentException(
                'Já existe uma disciplina com esse nome.'
            );
        }

        if (
            $this->repository->codeExists(
                $normalized['code']
            )
        ) {
            throw new InvalidArgumentException(
                'Já existe uma disciplina com esse código.'
            );
        }

        return $this->repository->create(
            $normalized
        );
    }

    /**
     * Atualiza uma disciplina.
     */
    public function update(
        int $id,
        array $data
    ): bool {
        $subject = $this->find($id);

        if (!$subject) {
            throw new InvalidArgumentException(
                'Disciplina não encontrada.'
            );
        }

        $normalized = $this->normalizeData(
            $data
        );

        if (
            $this->isGeneralSubject($subject)
            && $normalized['code']
                !== self::GENERAL_CODE
        ) {
            throw new InvalidArgumentException(
                'O código da disciplina Geral da Escola não pode ser alterado.'
            );
        }

        if (
            $this->repository->nameExists(
                $normalized['name'],
                $id
            )
        ) {
            throw new InvalidArgumentException(
                'Já existe outra disciplina com esse nome.'
            );
        }

        if (
            $this->repository->codeExists(
                $normalized['code'],
                $id
            )
        ) {
            throw new InvalidArgumentException(
                'Já existe outra disciplina com esse código.'
            );
        }

        /*
         * A disciplina padrão deve permanecer ativa.
         */
        if ($this->isGeneralSubject($subject)) {
            $normalized['active'] = 1;
        }

        return $this->repository->update(
            $id,
            $normalized
        );
    }

    /**
     * Ativa uma disciplina.
     */
    public function activate(int $id): bool
    {
        $subject = $this->find($id);

        if (!$subject) {
            throw new InvalidArgumentException(
                'Disciplina não encontrada.'
            );
        }

        return $this->repository->updateActive(
            $id,
            true
        );
    }

    /**
     * Inativa uma disciplina.
     */
    public function deactivate(int $id): bool
    {
        $subject = $this->find($id);

        if (!$subject) {
            throw new InvalidArgumentException(
                'Disciplina não encontrada.'
            );
        }

        if ($this->isGeneralSubject($subject)) {
            throw new InvalidArgumentException(
                'A disciplina Geral da Escola não pode ser inativada.'
            );
        }

        return $this->repository->updateActive(
            $id,
            false
        );
    }

    /**
     * Exclui uma disciplina.
     */
    public function delete(int $id): bool
    {
        $subject = $this->find($id);

        if (!$subject) {
            throw new InvalidArgumentException(
                'Disciplina não encontrada.'
            );
        }

        if ($this->isGeneralSubject($subject)) {
            throw new InvalidArgumentException(
                'A disciplina Geral da Escola não pode ser excluída.'
            );
        }

        if (
            $this->repository->hasRelations($id)
        ) {
            throw new InvalidArgumentException(
                'A disciplina não pode ser excluída porque possui usuários ou ocorrências vinculadas. Inative-a em vez de excluir.'
            );
        }

        $deleted = $this->repository->delete($id);

        if (!$deleted) {
            throw new InvalidArgumentException(
                'Não foi possível excluir a disciplina.'
            );
        }

        return true;
    }

    /**
     * Retorna as disciplinas vinculadas
     * a determinado usuário.
     */
    public function byUser(
        int $userId,
        bool $onlyActive = false
    ): array {
        if ($userId <= 0) {
            return [];
        }

        $subjects = $this->repository->byUser(
            $userId,
            $onlyActive
        );

        return $this->normalizeSubjects(
            $subjects
        );
    }

    /**
     * Retorna somente os IDs vinculados
     * ao usuário.
     */
    public function idsByUser(
        int $userId
    ): array {
        if ($userId <= 0) {
            return [];
        }

        return $this->normalizeSubjectIds(
            $this->repository->idsByUser(
                $userId
            )
        );
    }

    /**
     * Sincroniza as disciplinas de um usuário.
     *
     * A sincronização substitui os vínculos anteriores.
     */
    public function syncUserSubjects(
        int $userId,
        array $subjectIds
    ): void {
        if ($userId <= 0) {
            throw new InvalidArgumentException(
                'O usuário informado é inválido.'
            );
        }

        $subjectIds = $this->normalizeSubjectIds(
            $subjectIds
        );

        /*
         * Garante que todos os IDs informados
         * correspondam a disciplinas existentes.
         */
        foreach ($subjectIds as $subjectId) {
            $subject = $this->find($subjectId);

            if (!$subject) {
                throw new InvalidArgumentException(
                    'Uma das disciplinas selecionadas não foi encontrada.'
                );
            }
        }

        $db = Connection::getInstance();

        try {
            $db->beginTransaction();

            $this->repository->syncUserSubjects(
                $userId,
                $subjectIds
            );

            $db->commit();
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Remove todos os vínculos de disciplinas
     * de determinado usuário.
     */
    public function detachAllFromUser(
        int $userId
    ): bool {
        if ($userId <= 0) {
            return false;
        }

        return $this->repository
            ->detachAllFromUser($userId);
    }

    /**
     * Retorna os usuários vinculados
     * à disciplina.
     */
    public function usersBySubject(
        int $subjectId
    ): array {
        if ($subjectId <= 0) {
            return [];
        }

        $users = $this->repository
            ->usersBySubject($subjectId);

        foreach ($users as &$user) {
            $user['id'] = (int) (
                $user['id'] ?? 0
            );

            $user['active'] = (int) (
                $user['active'] ?? 0
            );
        }

        unset($user);

        return $users;
    }

    /**
     * Retorna disciplinas que podem ser usadas
     * por um usuário ao registrar ocorrência.
     *
     * Quando $canAccessAll for verdadeiro,
     * retorna todas as disciplinas ativas.
     *
     * Para professores, retorna apenas as
     * disciplinas vinculadas ao próprio usuário.
     */
    public function availableForUser(
        int $userId,
        bool $canAccessAll = false
    ): array {
        if ($canAccessAll) {
            return $this->active();
        }

        if ($userId <= 0) {
            return [];
        }

        return $this->byUser(
            $userId,
            true
        );
    }

    /**
     * Verifica se determinado usuário possui
     * acesso a uma disciplina.
     */
    public function userHasSubject(
        int $userId,
        int $subjectId
    ): bool {
        if (
            $userId <= 0
            || $subjectId <= 0
        ) {
            return false;
        }

        return in_array(
            $subjectId,
            $this->idsByUser($userId),
            true
        );
    }

    /**
     * Ranking de disciplinas pela quantidade
     * de ocorrências.
     */
    public function rankingByOccurrences(
        int $limit = 10
    ): array {
        $items = $this->repository
            ->rankingByOccurrences($limit);

        foreach ($items as &$item) {
            $item['id'] = (int) (
                $item['id'] ?? 0
            );

            $item['active'] = (int) (
                $item['active'] ?? 0
            );

            $item['total_occurrences'] = (int) (
                $item['total_occurrences'] ?? 0
            );

            $item['open_occurrences'] = (int) (
                $item['open_occurrences'] ?? 0
            );

            $item['critical_occurrences'] = (int) (
                $item['critical_occurrences'] ?? 0
            );
        }

        unset($item);

        return $items;
    }

    /**
     * Lista simplificada para selects.
     */
    public function options(
        bool $onlyActive = true
    ): array {
        $subjects = $onlyActive
            ? $this->active()
            : $this->all();

        $options = [];

        foreach ($subjects as $subject) {
            $id = (int) (
                $subject['id'] ?? 0
            );

            if ($id <= 0) {
                continue;
            }

            $name = trim(
                (string) (
                    $subject['name'] ?? ''
                )
            );

            $code = trim(
                (string) (
                    $subject['code'] ?? ''
                )
            );

            $options[$id] = $code !== ''
                ? $name . ' (' . $code . ')'
                : $name;
        }

        return $options;
    }

    /**
     * Valida e normaliza os dados da disciplina.
     */
    private function normalizeData(
        array $data
    ): array {
        $name = $this->normalizeName(
            $data['name'] ?? ''
        );

        $code = $this->normalizeCode(
            $data['code'] ?? ''
        );

        $description = trim(
            (string) (
                $data['description'] ?? ''
            )
        );

        $active = !empty(
            $data['active']
        )
            ? 1
            : 0;

        if ($name === '') {
            throw new InvalidArgumentException(
                'Informe o nome da disciplina.'
            );
        }

        if (mb_strlen($name) > 120) {
            throw new InvalidArgumentException(
                'O nome da disciplina deve possuir no máximo 120 caracteres.'
            );
        }

        if ($code === '') {
            throw new InvalidArgumentException(
                'Informe o código da disciplina.'
            );
        }

        if (mb_strlen($code) > 30) {
            throw new InvalidArgumentException(
                'O código da disciplina deve possuir no máximo 30 caracteres.'
            );
        }

        if (
            !preg_match(
                '/^[A-Z0-9_-]+$/',
                $code
            )
        ) {
            throw new InvalidArgumentException(
                'O código pode conter apenas letras, números, hífen e sublinhado.'
            );
        }

        if (mb_strlen($description) > 2000) {
            throw new InvalidArgumentException(
                'A descrição deve possuir no máximo 2.000 caracteres.'
            );
        }

        return [
            'name' => $name,

            'code' => $code,

            'description' =>
                $description !== ''
                    ? $description
                    : null,

            'active' => $active,
        ];
    }

    /**
     * Normaliza o nome da disciplina.
     */
    private function normalizeName(
        mixed $name
    ): string {
        $name = trim(
            preg_replace(
                '/\s+/u',
                ' ',
                (string) $name
            ) ?? ''
        );

        if ($name === '') {
            return '';
        }

        return mb_convert_case(
            $name,
            MB_CASE_TITLE,
            'UTF-8'
        );
    }

    /**
     * Normaliza o código da disciplina.
     */
    private function normalizeCode(
        mixed $code
    ): string {
        $code = strtoupper(
            trim((string) $code)
        );

        $code = preg_replace(
            '/\s+/',
            '_',
            $code
        ) ?? '';

        return trim($code);
    }

    /**
     * Normaliza IDs de disciplinas.
     */
    private function normalizeSubjectIds(
        array $subjectIds
    ): array {
        $normalized = [];

        foreach ($subjectIds as $subjectId) {
            $subjectId = (int) $subjectId;

            if ($subjectId <= 0) {
                continue;
            }

            $normalized[$subjectId] =
                $subjectId;
        }

        return array_values($normalized);
    }

    /**
     * Normaliza uma lista de disciplinas.
     */
    private function normalizeSubjects(
        array $subjects
    ): array {
        foreach ($subjects as &$subject) {
            $subject = $this->normalizeSubject(
                $subject
            );
        }

        unset($subject);

        return $subjects;
    }

    /**
     * Normaliza os tipos retornados pelo PDO.
     */
    private function normalizeSubject(
        array $subject
    ): array {
        $subject['id'] = (int) (
            $subject['id'] ?? 0
        );

        $subject['active'] = (int) (
            $subject['active'] ?? 0
        );

        if (
            array_key_exists(
                'total_users',
                $subject
            )
        ) {
            $subject['total_users'] = (int) (
                $subject['total_users'] ?? 0
            );
        }

        if (
            array_key_exists(
                'total_occurrences',
                $subject
            )
        ) {
            $subject['total_occurrences'] = (int) (
                $subject['total_occurrences'] ?? 0
            );
        }

        $subject['is_general'] =
            $this->isGeneralSubject($subject);

        return $subject;
    }

    /**
     * Verifica se é a disciplina especial
     * Geral da Escola.
     */
    private function isGeneralSubject(
        array $subject
    ): bool {
        return strtoupper(
            trim(
                (string) (
                    $subject['code'] ?? ''
                )
            )
        ) === self::GENERAL_CODE;
    }
}