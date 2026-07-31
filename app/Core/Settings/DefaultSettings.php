<?php

declare(strict_types=1);

namespace App\Core\Settings;

final class DefaultSettings
{
    public static function intelligence(): array
    {
        return [
            self::item('recurrence_limit', 2, 'integer', 'Reincidência', 'Quantidade mínima de ocorrências', 'Número de registros no período para considerar reincidência.', 10),
            self::item('analysis_window_days', 30, 'integer', 'Reincidência', 'Janela de análise', 'Quantidade de dias utilizada nas análises de frequência e ocorrências.', 20),
            self::item('stale_critical_days', 7, 'integer', 'Reincidência', 'Prazo de ocorrência crítica', 'Dias para considerar uma ocorrência crítica aberta como atrasada.', 30),
            self::item('risk_weight_frequency', 40, 'integer', 'Risco escolar', 'Peso da frequência', 'Peso relativo das faltas sem justificativa no cálculo de risco.', 40),
            self::item('risk_weight_occurrences', 35, 'integer', 'Risco escolar', 'Peso das ocorrências', 'Peso relativo da quantidade de ocorrências.', 50),
            self::item('risk_weight_severity', 25, 'integer', 'Risco escolar', 'Peso da gravidade', 'Peso relativo das ocorrências de alta gravidade.', 60),
            self::item('risk_moderate_threshold', 20, 'integer', 'Níveis de risco', 'Atenção a partir de', 'Ao atingir esta pontuação, o aluno deixa o nível Baixo e passa para Atenção.', 70),
            self::item('risk_high_threshold', 45, 'integer', 'Níveis de risco', 'Risco alto a partir de', 'Ao atingir esta pontuação, o aluno passa a ser classificado com risco Alto.', 80),
            self::item('risk_critical_threshold', 70, 'integer', 'Níveis de risco', 'Risco crítico a partir de', 'Ao atingir esta pontuação, o aluno passa para o nível máximo de prioridade.', 90),
            self::item('class_attention_threshold', 20, 'integer', 'Turmas', 'Limite de atenção da turma', 'Pontuação mínima para uma turma aparecer em atenção.', 100),
            self::item('trend_stable_margin', 5, 'integer', 'Tendências', 'Margem de estabilidade', 'Variações percentuais abaixo deste valor são consideradas estáveis.', 110),
            self::item('notification_recurrent_student', true, 'boolean', 'Notificações', 'Aluno reincidente', 'Gerar notificação quando um aluno atingir reincidência.', 120),
            self::item('notification_critical_occurrence', true, 'boolean', 'Notificações', 'Ocorrência crítica', 'Gerar notificação para novas ocorrências críticas.', 130),
            self::item('notification_critical_class', true, 'boolean', 'Notificações', 'Turma crítica', 'Gerar notificação quando uma turma ultrapassar o limite crítico.', 140),
            self::item('recommendation_guardian_contact', true, 'boolean', 'Recomendações', 'Contato com responsável', 'Sugerir contato com responsável em casos de risco crítico.', 150),
            self::item('show_attachment_image_carousels', true, 'boolean', 'Exibição de anexos', 'Exibir imagens em carrossel', 'Mostra as imagens anexadas em um pequeno mural deslizante nos locais em que os arquivos são exibidos.', 160),
        ];
    }


    public static function goals(): array
    {
        return [
            self::goalItem(
                'frequency_goal',
                95.0,
                'float',
                'Frequência',
                'Meta de frequência da escola',
                'Percentual mínimo esperado de presença. Essa meta alimenta os painéis, gráficos, alertas e listas de alunos abaixo da meta.',
                10
            ),
        ];
    }

    private static function goalItem(string $key, mixed $value, string $type, string $category, string $label, string $description, int $sort): array
    {
        return [
            'group_name' => 'school_goals',
            'key_name' => $key,
            'value' => $value,
            'default_value' => $value,
            'type' => $type,
            'category' => $category,
            'label' => $label,
            'description' => $description,
            'editable' => true,
            'requires_restart' => false,
            'sort_order' => $sort,
        ];
    }

    private static function item(string $key, mixed $value, string $type, string $category, string $label, string $description, int $sort): array
    {
        return [
            'group_name' => 'intelligence',
            'key_name' => $key,
            'value' => $value,
            'default_value' => $value,
            'type' => $type,
            'category' => $category,
            'label' => $label,
            'description' => $description,
            'editable' => true,
            'requires_restart' => false,
            'sort_order' => $sort,
        ];
    }
}
