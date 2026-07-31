<?php

declare(strict_types=1);

namespace App\Services\Occurrence\Concerns;

trait OccurrenceCatalogOperations
{
    public function types(): array
        {
            return [
                'OBSERVATION' => 'Observação',
                'WARNING' => 'Advertência',
                'SUSPENSION' => 'Suspensão',
                'REFERRAL' => 'Encaminhamento',
                'PRAISE' => 'Elogio',
                'OTHER' => 'Outro',
            ];
        }

    public function statuses(): array
        {
            return [
                'OPEN' => 'Aberta',
                'RESOLVED' => 'Resolvida',
            ];
        }

    /**
         * Níveis de gravidade disponíveis.
         */
        public function severities(): array
        {
            return $this->severityService->all();
        }

    /**
         * Classes CSS dos níveis de gravidade.
         */
        public function severityClasses(): array
        {
            return $this->severityService->classes();
        }

    /**
         * Ícones Lucide dos níveis de gravidade.
         */
        public function severityIcons(): array
        {
            return $this->severityService->icons();
        }

    /**
         * Pesos numéricos dos níveis de gravidade.
         */
        public function severityWeights(): array
        {
            return $this->severityService->weights();
        }

    public function titleSuggestions(): array
        {
            return [
                'OBSERVATION' => [
                    'Atrasos',
                    'Infrequência',
                    'Problemas de Saúde',
                    'Realização de Atividades',
                    'Não Entrega de Atividades',
                    'Faltas por Transporte',
                    'Saídas da Escola',
                    'Saídas da Sala',
                    'Mudança de Comportamento',
                    'Atendimento Individual',
                    'Conversa com o Responsável',
                ],

                'WARNING' => [
                    'Indisciplina',
                    'Atrasos',
                    'Desrespeito às Normas da Escola',
                    'Uso Indevido de Celular',
                    'Conversa Excessiva',
                    'Agressão Verbal',
                    'Agressão Física',
                    'Bullying',
                    'Uso de Linguagem Inadequada',
                    'Dano ao Patrimônio Escolar',
                    'Saídas da Sala',
                    'Saídas da Escola',
                    'Não Entrega de Atividades',
                ],

                'SUSPENSION' => [
                    'Indisciplina Grave',
                    'Agressão Física',
                    'Agressão Verbal',
                    'Bullying',
                    'Desrespeito às Normas da Escola',
                    'Dano ao Patrimônio Escolar',
                    'Saída da Escola sem Autorização',
                    'Reincidência de Comportamento Inadequado',
                ],

                'REFERRAL' => [
                    'Encaminhamento à Coordenação',
                    'Encaminhamento à Direção',
                    'Convocação do Responsável',
                    'Atendimento Pedagógico',
                    'Atendimento Psicossocial',
                    'Problemas de Saúde',
                    'Infrequência',
                    'Faltas por Transporte',
                    'Dificuldades de Aprendizagem',
                    'Mudança de Comportamento',
                ],

                'PRAISE' => [
                    'Excelente Participação',
                    'Excelente Frequência',
                    'Destaque Acadêmico',
                    'Bom Comportamento',
                    'Colaboração com a Turma',
                    'Evolução no Desempenho',
                    'Liderança Positiva',
                    'Compromisso com as Atividades',
                    'Pontualidade',
                ],

                'OTHER' => [
                    'Indisciplina',
                    'Atrasos',
                    'Desrespeito às Normas da Escola',
                    'Infrequência',
                    'Problemas de Saúde',
                    'Realização de Atividades',
                    'Não Entrega de Atividades',
                    'Faltas por Transporte',
                    'Saídas da Escola',
                    'Saídas da Sala',
                    'Outro',
                ],
            ];
        }

}
