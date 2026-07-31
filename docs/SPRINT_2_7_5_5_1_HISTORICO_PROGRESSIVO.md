# Sprint 2.7.5.5.1 — Histórico Progressivo

## Objetivo

Manter o perfil do aluno enxuto mesmo quando a linha do tempo do acompanhamento possuir muitos registros.

## Implementação

- São exibidos inicialmente os 8 eventos mais recentes.
- O botão **Ver mais** libera os próximos 8 eventos.
- O texto do botão informa quantos registros serão exibidos no próximo clique.
- Quando todos os eventos estiverem visíveis, o botão muda para **Recolher histórico**.
- Ao recolher, a timeline retorna aos 8 eventos iniciais e a página rola suavemente até o início do histórico.
- O botão não é renderizado quando existem 8 eventos ou menos.
- Nenhuma migração, rota ou consulta adicional foi criada.

## Arquivos alterados

- `app/Views/components/students/profile/intelligence.php`
- `public/assets/css/pages/students-profile/intelligence.css`
- `public/assets/js/pages/students.js`

## Banco de dados

Nenhuma alteração necessária.
