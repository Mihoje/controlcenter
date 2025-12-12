#!/bin/bash
cd ../

composer install

npm ci --omit dev
npm config set cache /tmp --global
su www-data -s /usr/bin/npm run build

npm cache clean --force

rm -rf /app/node_modules/

# Permissions
chgrp -R www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache