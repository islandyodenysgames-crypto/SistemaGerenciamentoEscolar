# Sprint 2.7.5.0 — Central Inteligente de Notificações

## Objetivo
Centralizar comunicações da gestão e eventos dos alunos acompanhados, mantendo a gestão das intervenções exclusivamente na página Acompanhamentos.

## Alterações

### Gestão de ações
- Removidos Editar ação e Excluir ação do perfil individual do aluno.
- Mantidos os controles em `/acompanhamentos`, respeitando autoria e acesso da gestão.
- Adicionadas âncoras por aluno para navegação direta a partir das notificações.

### Central de notificações
- Filtros por Todas, Meus alunos, Inteligência, Ocorrências, Gestão e Sistema.
- Filtros por lidas e não lidas.
- Ação Abrir marca a notificação como lida e encaminha ao contexto relacionado.
- Avisos encaminham para `/avisos`.
- Ações e movimentações de acompanhamento encaminham para `/acompanhamentos`.
- Ocorrências e sinais de inteligência encaminham ao perfil do aluno.

### Avisos da gestão
- Um aviso ativo e já publicado gera notificações para os usuários ativos do público-alvo.
- O autor do aviso não recebe notificação redundante.
- Avisos agendados não geram notificação antes da data de publicação.
- Prioridade informativa, importante e urgente é convertida em severidade visual.

### Frequência
- A cada atualização de frequência, alunos acompanhados têm sua tendência reavaliada.
- Com dados suficientes, compara-se os últimos 30 dias aos 30 dias anteriores.
- Diferenças de pelo menos 3 pontos percentuais geram melhora ou piora confirmada.
- Sem dados suficientes, é emitido apenas um sinal inicial de presença ou ausência.
- Impressões digitais diárias evitam notificações repetidas do mesmo estado.

### Compatibilidade
- Nenhuma migração foi necessária.
- A tabela `occurrence_notifications` continua sendo usada.
- Notificações antigas permanecem disponíveis.

## Arquivos principais alterados
- `app/Views/components/students/profile/intelligence.php`
- `app/Views/pages/monitoring/index.php`
- `app/Views/pages/notifications/index.php`
- `app/Controllers/NotificationController.php`
- `app/Services/Occurrence/NotificationService.php`
- `app/Services/NoticeService.php`
- `app/Services/AttendanceService.php`
- `app/Repositories/Occurrence/NotificationRepository.php`
- `app/Repositories/UserRepository.php`
- `app/Repositories/AttendanceRepository.php`
- `routes/web.php`
- `public/assets/css/app.css`

## Validação
- Todos os arquivos PHP de `app`, `routes` e `database` foram verificados com `php -l`.
- O pacote ZIP foi testado com `unzip -t`.
