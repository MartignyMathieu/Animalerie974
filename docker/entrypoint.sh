#!/bin/sh
# Démarrage du conteneur : prépare le volume, migre la base, charge les données de démo une seule fois.
set -e
cd /var/www/html

: "${APP_SECRET:?APP_SECRET manquant : fly secrets set APP_SECRET=...}"
: "${DATABASE_URL:?DATABASE_URL manquant (voir fly.toml)}"

# Port d'écoute : imposé par l'hébergeur via PORT (Render), 8080 par défaut (Fly.io).
PORT="${PORT:-8080}"
sed -ri "s/^Listen [0-9]{4,5}$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s#<VirtualHost \*:[0-9]+>#<VirtualHost *:${PORT}>#" /etc/apache2/sites-available/000-default.conf

# /data est un volume sur Fly.io ; sur Render (offre gratuite) c'est un simple dossier du conteneur,
# donc la base est recréée à chaque redémarrage.
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
