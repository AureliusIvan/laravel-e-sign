# Description: Makefile for docker-compose
dev:
	@docker compose --env-file ./.env up -d

dev-build:
	# Set proper permissions
	@sudo chmod -R 775 storage
	@sudo chmod -R 775 bootstrap/cache
	@docker compose --env-file ./.env up -d --force-recreate --build

dev-down:
	@docker compose down

prod:
	@echo "copy .env file"
	@#cp .env.example .env
	# if .env file not exist then copy .env.example to .env
	@if [ ! -f .env ]; then cp .env.example .env; fi
	@echo "adding permissions..."
	@NGINX_PORT=82 docker compose -f prod.docker-compose.yml --env-file ./.env up -d --force-recreate --build
	@docker exec laravel_app npm install
	@docker exec laravel_app npm run build
	@docker exec laravel_app chown -R www-data:www-data /var/www/html
	@docker exec laravel_app chmod -R 775 /var/www/html/storage
	@docker exec laravel_app chmod -R 775 /var/www/html/bootstrap/cache
	@docker exec laravel_app php artisan optimize:clear
	@docker exec laravel_app php artisan optimize
	@docker exec laravel_app php artisan storage:link

seed:
	@docker exec laravel_app php artisan migrate --force
	@docker exec laravel_app php artisan db:seed --class=AllSeeder --force

prod-down:
	@docker compose -f prod.docker-compose.yml down
