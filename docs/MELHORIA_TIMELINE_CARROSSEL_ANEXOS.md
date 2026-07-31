# Melhoria — Timeline recente e carrossel de imagens

## Linha do tempo

A linha do tempo do acompanhamento passa a ordenar os eventos por `event_at DESC, id DESC`, mostrando os registros mais recentes primeiro.

## Carrossel de imagens anexadas

Foi criado o componente reutilizável `media/attachment-carousel`.

Ele é exibido em:

- ações de acompanhamento;
- ocorrências no perfil do aluno;
- ações de ocorrências;
- painel de ocorrências recentes;
- criação/edição de ocorrências com anexos existentes;
- avisos no dashboard;
- cartões de avisos;
- formulário de edição de avisos.

Somente anexos identificados como imagem são incluídos. Os demais arquivos continuam aparecendo nas listas normais.

## Configuração

A opção `intelligence.show_attachment_image_carousels` é criada automaticamente nas Configurações da Inteligência e fica ativada por padrão.
