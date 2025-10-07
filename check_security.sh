#!/bin/bash

# ===================================
# Stars Doors - Security Check
# ===================================

echo "🔍 Vérification de sécurité avant commit..."
echo ""

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERRORS=0

# Vérifier les fichiers .env
echo "📋 Vérification des fichiers .env..."
if git ls-files | grep -q "\.env$"; then
    echo -e "${RED}❌ ERREUR: Fichier .env trouvé dans git!${NC}"
    ERRORS=$((ERRORS+1))
else
    echo -e "${GREEN}✅ Aucun fichier .env tracké${NC}"
fi

# Vérifier les fichiers SQL
echo "📋 Vérification des fichiers SQL..."
if git ls-files | grep -q "\.sql$"; then
    echo -e "${YELLOW}⚠️  ATTENTION: Fichiers SQL trouvés:${NC}"
    git ls-files | grep "\.sql$"
    echo -e "${YELLOW}   Assurez-vous qu'ils ne contiennent pas de données sensibles${NC}"
else
    echo -e "${GREEN}✅ Aucun fichier SQL tracké${NC}"
fi

# Vérifier les uploads
echo "📋 Vérification des uploads..."
if git ls-files | grep -E "uploads/.*\.(jpg|jpeg|png|webp|gif)$"; then
    echo -e "${RED}❌ ERREUR: Images utilisateur trouvées dans git!${NC}"
    git ls-files | grep -E "uploads/.*\.(jpg|jpeg|png|webp|gif)$"
    ERRORS=$((ERRORS+1))
else
    echo -e "${GREEN}✅ Aucune image utilisateur trackée${NC}"
fi

# Vérifier les logs
echo "📋 Vérification des logs..."
if git ls-files | grep -q "\.log$"; then
    echo -e "${RED}❌ ERREUR: Fichiers log trouvés dans git!${NC}"
    ERRORS=$((ERRORS+1))
else
    echo -e "${GREEN}✅ Aucun fichier log tracké${NC}"
fi

# Vérifier le .gitignore
echo "📋 Vérification du .gitignore..."
if [ -f .gitignore ]; then
    echo -e "${GREEN}✅ .gitignore existe${NC}"
else
    echo -e "${RED}❌ ERREUR: .gitignore manquant!${NC}"
    ERRORS=$((ERRORS+1))
fi

echo ""
echo "================================"

if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✅ Aucun problème de sécurité détecté!${NC}"
    echo -e "${GREEN}   Vous pouvez commiter en toute sécurité.${NC}"
    exit 0
else
    echo -e "${RED}❌ $ERRORS problème(s) de sécurité détecté(s)!${NC}"
    echo -e "${RED}   Corrigez-les avant de commiter.${NC}"
    exit 1
fi
