<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Core\Settings\DefaultSettings;
use App\Core\Settings\SettingManager;
use App\Repositories\Settings\SettingRepository;

final class SettingService
{
    public function __construct(private SettingRepository $repository, private SettingManager $manager)
    {
    }

    public function goalsPage(): array
    {
        $this->seedGoalsDefaults();
        $settings = $this->repository->all('school_goals');
        return [
            'settings' => $settings,
            'frequencyGoal' => (float) $this->manager->get('school_goals.frequency_goal', 95.0),
        ];
    }

    public function saveGoals(array $input, ?int $userId): void
    {
        $this->seedGoalsDefaults();
        $settings = $this->repository->all('school_goals');
        foreach ($settings as $setting) {
            if (!(bool) $setting['editable']) continue;
            $key = (string) $setting['key_name'];
            $value = $this->manager->validator()->cast($input[$key] ?? $setting['value'], (string) $setting['type']);
            if ($key === 'frequency_goal' && ((float)$value < 0 || (float)$value > 100)) {
                throw new \InvalidArgumentException('A meta de frequência deve estar entre 0% e 100%.');
            }
            $encoded = $this->encode($value, (string) $setting['type']);
            if ($encoded !== (string) $setting['value']) {
                $this->repository->updateValue('school_goals', $key, $encoded);
                $this->manager->forget('school_goals.' . $key);
            }
        }
    }

    public function resetGoals(?int $userId): void
    {
        $this->seedGoalsDefaults();
        $this->repository->resetGroup('school_goals');
        $this->manager->flush();
    }

    public function intelligencePage(): array
    {
        $this->seedDefaults();
        $settings = $this->repository->all('intelligence');
        $categories = [];
        foreach ($settings as $setting) {
            $categories[(string) $setting['category']][] = $setting;
        }
        return ['categories' => $categories];
    }

    public function saveIntelligence(array $input, ?int $userId): void
    {
        $this->seedDefaults();
        $settings = $this->repository->all('intelligence');
        $castValues = [];
        foreach ($settings as $setting) {
            $key = (string) $setting['key_name'];
            if (!(bool) $setting['editable']) {
                continue;
            }
            $raw = $setting['type'] === 'boolean' ? ($input[$key] ?? '0') : ($input[$key] ?? $setting['value']);
            $castValues[$key] = $this->manager->validator()->cast($raw, (string) $setting['type']);
        }
        $this->manager->validator()->validateIntelligence($castValues);

        foreach ($settings as $setting) {
            $key = (string) $setting['key_name'];
            if (!array_key_exists($key, $castValues)) {
                continue;
            }
            $newValue = $this->encode($castValues[$key], (string) $setting['type']);
            $oldValue = (string) $setting['value'];
            if ($newValue === $oldValue) {
                continue;
            }
            $this->repository->updateValue('intelligence', $key, $newValue);
            $this->manager->forget('intelligence.' . $key);
        }
    }

    public function resetIntelligence(?int $userId): void
    {
        $this->seedDefaults();
        $this->repository->resetGroup('intelligence');
        $this->manager->flush();
    }

    private function seedGoalsDefaults(): void
    {
        foreach (DefaultSettings::goals() as $setting) {
            $setting['setting_key'] = $setting['group_name'] . '.' . $setting['key_name'];
            $setting['value'] = $this->encode($setting['value'], $setting['type']);
            $setting['default_value'] = $this->encode($setting['default_value'], $setting['type']);
            $this->repository->upsert($setting);
        }
    }

    private function seedDefaults(): void
    {
        foreach (DefaultSettings::intelligence() as $setting) {
            $setting['setting_key'] = $setting['group_name'] . '.' . $setting['key_name'];
            $setting['value'] = $this->encode($setting['value'], $setting['type']);
            $setting['default_value'] = $this->encode($setting['default_value'], $setting['type']);
            $this->repository->upsert($setting);
        }
    }

    private function encode(mixed $value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            default => (string) $value,
        };
    }
}
