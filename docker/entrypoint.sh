#!/bin/sh
# Démarrage du conteneur : prépare le volume, migre la base, charge les données de démo une seule fois.
set -e
cd /var/www/html

: "${APP_SECRET:?APP_SECRET manquant : fly secrets set APP_SECRET=...}"
: "${DATABASE_URL:?DATABASE_URL manquant (voir fly.toml)}"

mkdir -p /data/sessions

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Données de démonstration (catalogue) : chargées une seule fois, jamais écrasées ensuite.
if [ ! -f /data/.donnees-demo ]; then
    echo "Premier démarrage : chargement du catalogue de démonstration."
    APP_ENV=dev php bin/console doctrine:fixtures:load --no-interaction
    touch /data/.donnees-demo
fi

chown -R www-data:www-data /data /var/www/html/var

exec apache2-foreground
