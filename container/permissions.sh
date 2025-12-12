#!/bin/bash
composer i

bash migrate.sh

npm ci --omit dev
npm config set cache /tmp --global
#su www-data -s /usr/bin/npm run build
npm run build

npm cache clean --force

rm -rf /app/node_modules/

# Permissions
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache