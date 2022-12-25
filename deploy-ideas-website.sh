#!/bin/bash

cd ideas-website/ && echo 'Inside Ideas Website Directory'

git reset --hard HEAD && git pull origin main && echo 'pulled main branch'

cp /home/opc/ideas-website-content/environment.php /home/opc/ideas-website-content/ideas-website/ && echo 'copied environment'

docker-compose up -d --build --force-recreate && echo 'Container up and Running'
