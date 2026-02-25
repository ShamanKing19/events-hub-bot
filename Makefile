up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build

restart:
	docker compose restart

exec:
	docker compose up -d && docker exec -it events-hub-bot-php-1 bash

ngrok:
	ngrok http 80
