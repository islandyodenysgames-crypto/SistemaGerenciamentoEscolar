<?php

declare(strict_types=1);

namespace App\Repositories\Settings;

use App\Core\Settings\Contracts\SettingProviderInterface;
use App\Repositories\BaseRepository;

final class SettingRepository extends BaseRepository implements SettingProviderInterface
{
    public function all(?string $group = null): array
    {
        $sql = 'SELECT * FROM system_settings';
        $params = [];
        if ($group !== null) {
            $sql .= ' WHERE group_name = :group_name';
            $params['group_name'] = $group;
        }
        $sql .= ' ORDER BY category, sort_order, id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(string $key): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM system_settings WHERE setting_key = :setting_key LIMIT 1');
        $stmt->execute(['setting_key' => $key]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function upsert(array $setting): void
    {
        $stmt = $this->db->prepare('INSERT INTO system_settings (group_name,key_name,setting_key,value,default_value,type,category,label,description,editable,requires_restart,sort_order,created_at,updated_at) VALUES (:group_name,:key_name,:setting_key,:value,:default_value,:type,:category,:label,:description,:editable,:requires_restart,:sort_order,NOW(),NOW()) ON DUPLICATE KEY UPDATE default_value=VALUES(default_value), type=VALUES(type), category=VALUES(category), label=VALUES(label), description=VALUES(description), editable=VALUES(editable), requires_restart=VALUES(requires_restart), sort_order=VALUES(sort_order), updated_at=NOW()');

        // O PDO pode converter `false` em string vazia ao executar um array de
        // parâmetros. Em colunas TINYINT/BOOLEAN, o MySQL em modo estrito
        // rejeita esse valor. Normalizamos os campos tipados antes do INSERT.
        $stmt->execute([
            'group_name' => (string) $setting['group_name'],
            'key_name' => (string) $setting['key_name'],
            'setting_key' => (string) $setting['setting_key'],
            'value' => (string) $setting['value'],
            'default_value' => (string) $setting['default_value'],
            'type' => (string) $setting['type'],
            'category' => (string) $setting['category'],
            'label' => (string) $setting['label'],
            'description' => (string) $setting['description'],
            'editable' => !empty($setting['editable']) ? 1 : 0,
            'requires_restart' => !empty($setting['requires_restart']) ? 1 : 0,
            'sort_order' => (int) $setting['sort_order'],
        ]);
    }

    public function updateValue(string $group, string $key, string $value): void
    {
        $stmt = $this->db->prepare('UPDATE system_settings SET value = :value, updated_at = NOW() WHERE group_name = :group_name AND key_name = :key_name AND editable = 1');
        $stmt->execute(['value' => $value, 'group_name' => $group, 'key_name' => $key]);
    }

    public function resetGroup(string $group): void
    {
        $stmt = $this->db->prepare('UPDATE system_settings SET value = default_value, updated_at = NOW() WHERE group_name = :group_name AND editable = 1');
        $stmt->execute(['group_name' => $group]);
    }

    public function recordHistory(string $group, string $key, ?string $oldValue, string $newValue, ?int $userId, string $action): void
    {
        $stmt = $this->db->prepare('INSERT INTO system_setting_history (group_name,key_name,old_value,new_value,user_id,action,created_at) VALUES (:group_name,:key_name,:old_value,:new_value,:user_id,:action,NOW())');
        $stmt->execute(['group_name'=>$group,'key_name'=>$key,'old_value'=>$oldValue,'new_value'=>$newValue,'user_id'=>$userId,'action'=>$action]);
    }

    public function history(string $group, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $stmt = $this->db->prepare("SELECT h.*, u.name AS user_name FROM system_setting_history h LEFT JOIN users u ON u.id = h.user_id WHERE h.group_name = :group_name ORDER BY h.id DESC LIMIT {$limit}");
        $stmt->execute(['group_name' => $group]);
        return $stmt->fetchAll();
    }
}
