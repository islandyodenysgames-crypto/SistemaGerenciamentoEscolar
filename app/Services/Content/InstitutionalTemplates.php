<?php

declare(strict_types=1);

namespace App\Services\Content;

final class InstitutionalTemplates
{
    /** @return array<string,array<string,list<string>>> */
    public static function all(): array
    {
        return [
            'automatic_message' => [
                'institutional' => [
                    'Nossa escola alcançou {attendance}% de frequência hoje. Cada presença fortalece a aprendizagem.',
                    'Hoje registramos {present} presenças e {absent} faltas. Seguimos juntos pela aprendizagem.',
                    'A turma {class} lidera o ranking de hoje com {class_attendance}% de frequência.',
                ],
                'formal' => [
                    'O índice de frequência escolar registrado hoje é de {attendance}%.',
                    'Foram contabilizadas {present} presenças no período de referência.',
                ],
                'inspiring' => [
                    'Cada presença conta: hoje alcançamos {attendance}% e seguimos construindo um futuro melhor.',
                    'A dedicação de hoje transforma o amanhã. Já são {present} presenças registradas.',
                ],
                'young' => [
                    'Presença em alta! Hoje chegamos a {attendance}%. Vamos continuar assim!',
                    'A turma {class} mandou muito bem e lidera com {class_attendance}%!',
                ],
            ],
            'daily_tip' => [
                'institutional' => [
                    'Organize o material antes da aula e aproveite melhor cada momento de aprendizagem.',
                    'Reserve alguns minutos por dia para revisar o que foi estudado.',
                    'Tire dúvidas sempre que precisar: perguntar também é aprender.',
                    'Respeito, escuta e colaboração tornam a escola melhor para todos.',
                ],
                'formal' => [
                    'A organização prévia dos materiais favorece o aproveitamento das atividades escolares.',
                    'A revisão diária dos conteúdos contribui para a consolidação da aprendizagem.',
                ],
                'inspiring' => [
                    'Uma pequena revisão hoje pode se transformar em uma grande conquista amanhã.',
                    'Cada pergunta abre uma nova porta para o conhecimento.',
                ],
                'young' => [
                    'Separe o material antes da aula e chegue pronto para aprender!',
                    'Ficou com dúvida? Pergunte. Aprender junto é muito melhor!',
                ],
            ],
            'support_text' => [
                'institutional' => ['Sua presença transforma o hoje e constrói o amanhã.', 'A escola acontece com a participação de todos.'],
                'formal' => ['A assiduidade é parte essencial do compromisso com a aprendizagem.'],
                'inspiring' => ['Quando você está presente, novas possibilidades também estão.'],
                'young' => ['Chegue junto: sua presença faz toda a diferença!'],
            ],
            'motivation' => [
                'institutional' => ['Cada presença abre uma nova oportunidade de aprender.', 'Pequenos esforços diários constroem grandes resultados.', 'Juntos, construímos uma escola mais forte.'],
                'formal' => ['Compromisso e assiduidade sustentam uma trajetória escolar consistente.'],
                'inspiring' => ['Aprender hoje é transformar o amanhã.', 'Todo novo dia traz uma oportunidade de avançar.'],
                'young' => ['Bora aprender? Hoje é mais uma chance de fazer acontecer!', 'Seu esforço de hoje já é parte da sua conquista.'],
            ],
            'institutional_slogan' => [
                'institutional' => ['Educação, presença e futuro.', 'Uma escola presente transforma vidas.', 'Aprender, participar e crescer juntos.'],
                'formal' => ['Educação com compromisso, participação e resultados.'],
                'inspiring' => ['Conhecimento que aproxima e transforma.'],
                'young' => ['Nossa escola, nosso futuro!'],
            ],
            'footer_slogan' => [
                'institutional' => ['Cada presença conta. Cada aluno importa.', 'Nossa escola cresce com a participação de todos.'],
                'formal' => ['Presença e compromisso com a aprendizagem.'],
                'inspiring' => ['Presentes hoje, preparados para o amanhã.'],
                'young' => ['Todo mundo presente, todo mundo avançando!'],
            ],
            'highlight_message' => [
                'institutional' => ['Parabéns, turma {class}! O resultado de {class_attendance}% merece reconhecimento.', 'A turma {class} é o destaque do dia com {class_attendance}% de frequência.'],
                'formal' => ['A turma {class} obteve o maior índice diário: {class_attendance}%.'],
                'inspiring' => ['A união da turma {class} fez a diferença: {class_attendance}% de presença!'],
                'young' => ['Mandaram muito bem, {class}! Vocês chegaram a {class_attendance}%!'],
            ],
            'did_you_know' => [
                'institutional' => ['Hoje a frequência geral da escola é de {attendance}%.', 'Já foram registradas {present} presenças hoje.', 'A turma {class} lidera o ranking diário.', 'A maior evolução da semana pertence à turma {evolution_class}.'],
                'formal' => ['O índice diário de assiduidade é de {attendance}%.'],
                'inspiring' => ['Cada uma das {present} presenças de hoje representa uma oportunidade de aprender.'],
                'young' => ['Sabia que a turma {class} está no topo do ranking hoje?'],
            ],
            'hall_of_fame' => [
                'institutional' => ['Reconhecemos as turmas que transformam presença em aprendizagem.', 'Parabéns às turmas que se destacaram por compromisso e evolução.'],
                'formal' => ['Reconhecimento institucional aos melhores indicadores do período.'],
                'inspiring' => ['Grandes resultados nascem da constância de cada dia.'],
                'young' => ['Palmas para quem está fazendo a diferença!'],
            ],
            'calendar_message' => [
                'institutional' => ['Acompanhe os próximos compromissos da nossa comunidade escolar.', 'Organização e participação fazem parte de uma escola presente.'],
                'formal' => ['Consulte a programação institucional e antecipe seus compromissos.'],
                'inspiring' => ['Cada encontro é uma nova oportunidade de construir juntos.'],
                'young' => ['Fique ligado na programação da escola!'],
            ],
        ];
    }
}
