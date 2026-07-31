# Sprint 3.1.0.2 — Consolidação dos Acompanhamentos

## Alterações
- Corrigido o contraste do botão **Salvar alterações** no drawer de edição de ações.
- Vínculos cujo período terminou passam a ser concluídos automaticamente no próximo acesso do responsável ao módulo ou ao perfil do aluno.
- Criada notificação única de conclusão, vinculada ao aluno e ao período encerrado.
- Acompanhamentos concluídos permanecem na página **Acompanhamentos** como registro histórico.
- Cards concluídos recebem destaque visual e não permitem novas ações.
- Ações de vínculos concluídos ficam recolhidas em **Exibir ações realizadas**.
- Edição, exclusão de ações e exclusão de anexos são bloqueadas no servidor após a conclusão.
- O perfil do aluno volta a exibir participantes, plano e ações do acompanhamento, inclusive depois da conclusão.
- É possível iniciar um novo acompanhamento sem apagar o ciclo anterior.

## Banco de dados
Nenhuma nova migração é necessária. O estado concluído utiliza os campos existentes (`status`, `end_date` e `ended_at`).
