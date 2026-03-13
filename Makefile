build:
	docker compose build

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

exec:
	docker exec -it events-hub-bot-php-1 bash

prod:
	docker compose -f docker-compose.prod.yml up -d

ngrok:
	ngrok http 80
