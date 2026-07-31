# Sprint 3.1.0.3.2 — Consolidação do Perfil do Aluno

- Mantém o `case_id` após criar, editar, prorrogar, encerrar ou registrar ações.
- Redireciona de volta ao caso correto e à seção correta do perfil.
- Indicadores do painel usam sempre o período do caso selecionado, inclusive em consulta somente leitura.
- Ordena casos ativos por atualização e concluídos pelo encerramento/atualização mais recente.
- Padroniza os estados visuais sem criar estados artificiais no banco.
- Exibe período e atalho claro para abrir cada caso.
- Reforça o vínculo de formulários com o `monitoring_id/case_id` selecionado.

Não há migração de banco de dados nesta sprint.
