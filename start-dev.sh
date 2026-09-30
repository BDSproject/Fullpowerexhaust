#!/usr/bin/env bash
#
# FULLPOWEREXHAUST — serveur de développement local
# Démarre PrestaShop 9 (mode dev) sur http://localhost:8000
#
# Prérequis : PHP 8.x + MySQL local avec la base fullpowerexhaust_dev
#
# Front office : http://localhost:8000/
# Back office  : http://localhost:8000/admin123/  (admin@fullpowerexhaust.com / admin123)

set -e
cd "$(dirname "$0")"

PORT="${1:-8000}"

echo "▶ Démarrage du serveur PrestaShop (dev) sur http://localhost:${PORT}"
echo "  Front : http://localhost:${PORT}/"
echo "  Admin : http://localhost:${PORT}/admin123/"
echo "  Arrêt : Ctrl+C"
echo ""

php -d memory_limit=768M -d display_errors=1 -S 127.0.0.1:${PORT} router.php
