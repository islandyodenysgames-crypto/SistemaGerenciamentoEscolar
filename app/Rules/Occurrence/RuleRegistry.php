<?php

declare(strict_types=1);

namespace App\Rules\Occurrence;

use InvalidArgumentException;
use RuntimeException;

final class RuleRegistry
{
    /**
     * @var array<string, RuleInterface>
     */
    private array $rules = [];

    /**
     * @param iterable<RuleInterface> $rules
     */
    public function __construct(
        iterable $rules = []
    ) {
        foreach ($rules as $rule) {
            $this->register($rule);
        }
    }

    public function register(
        RuleInterface $rule
    ): void {
        $name = trim($rule->name());

        if ($name === '') {
            throw new InvalidArgumentException(
                'O nome da regra não pode ser vazio.'
            );
        }

        if (isset($this->rules[$name])) {
            throw new RuntimeException(
                sprintf(
                    'A regra "%s" já está registrada.',
                    $name
                )
            );
        }

        $this->rules[$name] = $rule;
    }

    public function has(string $name): bool
    {
        return isset($this->rules[$name]);
    }

    public function get(string $name): RuleInterface
    {
        if (!$this->has($name)) {
            throw new RuntimeException(
                sprintf(
                    'A regra "%s" não está registrada.',
                    $name
                )
            );
        }

        return $this->rules[$name];
    }

    public function remove(string $name): void
    {
        unset($this->rules[$name]);
    }

    /**
     * @return RuleInterface[]
     */
    public function all(): array
    {
        $rules = array_values($this->rules);

        usort(
            $rules,
            static function (
                RuleInterface $first,
                RuleInterface $second
            ): int {
                $priorityComparison =
                    $first->priority()
                    <=> $second->priority();

                if ($priorityComparison !== 0) {
                    return $priorityComparison;
                }

                return $first->name()
                    <=> $second->name();
            }
        );

        return $rules;
    }

    public function count(): int
    {
        return count($this->rules);
    }

    public function isEmpty(): bool
    {
        return $this->rules === [];
    }
}