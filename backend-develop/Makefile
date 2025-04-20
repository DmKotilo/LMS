ifneq (,$(wildcard ./.env))
    include .env
    export
endif

all: help

help:
	@echo "help                  			# this help"
	@echo "setup-docker          			# install/upgrade docker and docker-compose"
	@echo "build                 			# build images"
	@echo "start                 			# build and start only backend"
	@echo "compose                 			# composer install"
	@echo "migrate                 			# artisan migrate"
	@echo "stop                  			# stop and delete containers"
	@echo "clear             			# stop all containers and clear all unusable data"
	@echo "================================================================================================"

setup-docker:
	apt update && apt install docker.io -y
	curl -fsSL https://get.docker.com | sh
	curl -L "https://github.com/docker/compose/releases/download/v2.23.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
	chmod +x /usr/local/bin/docker-compose
	docker -v && docker-compose -v

setup:
	[ -f .env ] || cp -f .env.example .env
	[ -f laravel/.env ] || cp laravel/.env.example laravel/.env
	sudo chmod -R 777 laravel/storage


setup-network:
	docker network inspect appnet || docker network create appnet

build:
	docker-compose -f docker-compose.local.yml build

start: setup setup-network
	docker-compose -f docker-compose.local.yml up -d
	docker-compose -f docker-compose.local.yml exec app composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction
	docker run --rm -v "${PWD}/laravel:/app" node:18-bookworm-slim bash -c "cd /app && npm install ; npm run build"
	grep -q "^APP_KEY=....*$\" laravel/.env || docker-compose -f docker-compose.local.yml exec app php artisan key:generate
	[ -L laravel/public/storage ] || docker-compose -f docker-compose.local.yml exec app php artisan storage:link

migrate:
	docker-compose -f docker-compose.local.yml exec app php artisan migrate

keygen:
	grep -q "^APP_KEY=....*$\" laravel/.env || docker-compose -f docker-compose.local.yml exec app php artisan key:generate

storlink:
	[ -L laravel/public/storage ] || docker-compose -f docker-compose.local.yml exec app php artisan storage:link

# after start

compose:
	docker-compose -f docker-compose.local.yml exec app composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction

build-front:
	docker run --rm -v "${PWD}/laravel:/app" node:18-bookworm-slim bash -c "cd /app && npm install ; npm run build"

# stop and clear

stop:
	docker-compose -f docker-compose.local.yml down

prune:
	docker container prune -f
	docker image prune -a -f
	docker volume prune -f

clear: stop prune
	sudo rm -rf data ${PROJECT}-frontend/dist ${PROJECT}-frontend/.output