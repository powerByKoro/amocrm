# Деплой

1. `cp .env.example .env`
2. `sh docker/create.sh`
3. `cd docker && docker-compose up -d --build`
4. `docker exec -ti php /bin/bash` => `php artisan migrate`

# Работа веб-хуков
Нужно установить локально ngrok.
Запустить ngrok http 80. 
Точка входа должна начинаться с домена, который отдал ngrok.

