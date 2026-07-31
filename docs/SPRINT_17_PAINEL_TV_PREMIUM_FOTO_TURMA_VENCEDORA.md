# Sprint 17 — Painel TV Premium

## Objetivo

Elevar o Painel TV de uma página informativa para uma apresentação institucional de alta legibilidade, adequada a televisores Full HD e 4K, preservando clareza, identidade visual e atualização automática.

## Escopo funcional

### 1. Foto institucional das turmas — entregue nesta Sprint

- Adicionar foto no cadastro e na edição da turma.
- Aceitar JPG, PNG e WebP, com até 8 MB.
- Exigir resolução mínima de 900 × 500 px.
- Recomendar fotografia horizontal em proporção 16:9.
- Permitir substituição e remoção da foto.
- Armazenar o arquivo em `public/uploads/classes`.
- Exibir a foto somente da turma vencedora no card **Destaque do Dia**, na tela **Ranking diário de frequência por turma**.
- Manter o troféu como fallback quando a turma vencedora não possuir foto.
- Não exibir fotos das demais turmas no ranking.

### 2. Acabamento visual premium

- Animação suave das barras e indicadores na entrada de cada slide.
- Hierarquia visual própria por categoria: ranking, frequência, avisos, calendário e destaques.
- Transições discretas de 600 a 800 ms.
- Melhorias de tipografia e contraste para leitura à distância.
- Faixa institucional consistente em todas as telas.
- Preservação da apresentação em temas claro, escuro e automático.

### 3. Ranking diário

- Dar maior destaque visual à primeira colocada.
- Exibir fotografia institucional da vencedora.
- Manter medalhas e estados de desempenho.
- Garantir que a fotografia não altere o cálculo ou a ordem do ranking.

### 4. Avisos e calendário

- Preservar banners sem sobreposição de informações.
- Manter a paginação automática de eventos.
- Garantir tamanhos mínimos de fonte para uso em TV.

### 5. Desempenho e confiabilidade

- Usar imagens otimizadas para web.
- Aplicar cache busting pela data de atualização da foto.
- Manter fallback visual quando arquivos estiverem ausentes.
- Não bloquear a apresentação caso uma turma não possua fotografia.

## Migração

Arquivo:

`database/migrations/202608100001_add_photo_to_school_classes.php`

Novas colunas em `school_classes`:

- `photo_path`
- `photo_updated_at`

## Critérios de aceite

1. A gestão consegue cadastrar, substituir e remover a foto de uma turma.
2. Fotos inválidas ou pequenas são rejeitadas com mensagem clara.
3. A vencedora do ranking diário exibe sua foto no card **Destaque do Dia**.
4. Nenhuma outra turma tem sua foto exibida na lista do ranking.
5. Sem foto cadastrada, o painel usa o troféu anterior como fallback.
6. A imagem preserva enquadramento central e proporção 16:9.
7. A alteração não modifica o cálculo de frequência nem a classificação das turmas.
8. O Painel TV continua funcionando em tela cheia e em rotação automática.

## Arquivos principais alterados

- `app/Controllers/SchoolClassController.php`
- `app/Services/SchoolClassService.php`
- `app/Repositories/SchoolClassRepository.php`
- `app/Services/AttendanceAnalyticsService.php`
- `app/Views/pages/classes/create.php`
- `app/Views/pages/classes/edit.php`
- `app/Views/components/classes/photo-field.php`
- `app/Views/pages/tv-panel/show.php`
- `public/assets/css/pages/students.css`
- `public/assets/css/pages/tv-panel.css`
- `app/Views/layouts/tv.php`
