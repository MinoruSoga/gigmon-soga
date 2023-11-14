.PHONY: build sh run down

build:
	docker-compose build

up:
	docker-compose up

upd:
	docker-compose up -d

down:
	docker-compose down

migrate-up:
	docker exec gpt-works-app bash -c "cd /var/www/gpt-works && php artisan migrate"

migrate-down:
	docker exec gpt-works-app bash -c "cd /var/www/gpt-works && php artisan migrate:rollback"

shell:
	docker exec -it gpt-works-app bash
