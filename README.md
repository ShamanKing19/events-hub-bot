## Структура
1. Таблица студентов
2. Таблица мероприятий
3. Связь M:M с доп полем - кол-во баллов

## Функционал телеграм бота
1. Добавление/редактирование/удаление(мягкое) студента
2. Добавление/редактирование/удаление(мягкое) мероприятия
3. Привязка студента к мероприятию с указанием кол-ва баллов
4. Вывод топа студентов по баллам

docker compose -f docker-compose.prod.yml run --rm certbot certonly \
--webroot \
--webroot-path=/var/www/certbot \
--email gera.sukhomlin@gmail.com \
--agree-tos \
--no-eff-email \
-d events-hub.duckdns.org
