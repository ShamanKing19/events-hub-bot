build:
	docker compose build

down:
	docker compose down

restart:
	docker compose restart

exec:
	docker exec -it events-hub-bot-php-1 bash

dev:
	docker compose up -d

prod:
	docker compose -f docker-compose.prod.yml up -d

ngrok:
	ngrok http 80
