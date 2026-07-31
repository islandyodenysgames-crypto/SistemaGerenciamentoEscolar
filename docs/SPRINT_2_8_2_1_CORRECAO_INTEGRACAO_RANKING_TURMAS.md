# Sprint 2.8.2.1 — Correção da integração do ranking positivo

## Correção

A Central já recebia do `IntelligenceService` a chave `improving_classes`, mas a View
`pages/intelligence/index.php` não a convertia para a variável local
`$improvingClasses`.

Foi adicionada a leitura defensiva:

```php
$improvingClasses = is_array($data['improving_classes'] ?? null)
    ? $data['improving_classes']
    : [];
```

O bloco visual também foi protegido com `empty()` e fallback no `foreach`, impedindo
warnings caso o dado esteja ausente ou inválido.

Nenhuma migração é necessária.
