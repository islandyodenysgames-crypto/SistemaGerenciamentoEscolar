# Sprint 2.1 — Engine de Configurações

## Instalação

1. Substitua os arquivos do projeto pelos arquivos deste pacote.
2. No terminal, na raiz do projeto, execute:

```bash
php console.php migrate
composer dump-autoload
```

3. Acesse `Configurações > Inteligência Escolar`.

## Entregas

- Core genérico de configurações (`SettingManager`, cache, validação e contratos).
- Tabelas `system_settings` e `system_setting_history`.
- Interface de configurações no padrão visual do sistema.
- Valores recomendados e restauração de padrões.
- Histórico de auditoria por usuário.
- Integração real com a Central de Inteligência.

## Parâmetros integrados

- Janela de análise.
- Prazo para ocorrência crítica atrasada.
- Pesos de frequência, ocorrências e gravidade.
- Faixas de risco Atenção, Alto e Crítico.
- Margem de estabilidade das tendências.

## Ajuste visual e restauração

- O painel visual de histórico de alterações foi removido.
- O fluxo de salvamento/restauração não grava mais auditoria em `system_setting_history`.
- Os níveis Atenção, Alto e Crítico possuem identificação visual e textos distintos.
- A restauração usa diretamente os valores padrão de `system_settings`.
