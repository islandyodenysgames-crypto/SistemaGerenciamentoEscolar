# Sprint 2.7.1.3 — Formulário de acompanhamento recolhido

- O formulário de acompanhamento e plano inicia oculto.
- O botão **Acompanhar aluno** abre o formulário e desaparece junto com o estado vazio.
- Dentro do formulário permanece apenas a ação **Iniciar acompanhamento com plano** e a opção **Cancelar**.
- Ao cancelar, o formulário volta a ser ocultado e o botão **Acompanhar aluno** reaparece.
- Foi adicionada uma regra CSS explícita para impedir que `display: grid` sobrescreva o atributo `hidden`.
