#!/bin/bash
# ─────────────────────────────────────────────────────────────────────────────
# Instala os git hooks do projeto Rango!
# Execute uma única vez após clonar o repositório.
# ─────────────────────────────────────────────────────────────────────────────

echo ""
echo "🔧 Instalando git hooks do Rango!..."

# Aponta o git para usar a pasta .githooks deste projeto
git config core.hooksPath .githooks

# Garante permissão de execução
chmod +x .githooks/pre-push

echo "✅ Hooks instalados com sucesso!"
echo ""
echo "📋 Convenção de versão nas mensagens de commit:"
echo "   [MAJOR] mensagem  →  bump principal  (ex: 1.0.0 → 2.0.0)"
echo "   [MINOR] mensagem  →  nova feature    (ex: 1.0.0 → 1.1.0)"
echo "   qualquer texto    →  correção/patch  (ex: 1.0.0 → 1.0.1)"
echo ""
echo "   O build number (+N) é incrementado automaticamente em todo push."
echo ""
