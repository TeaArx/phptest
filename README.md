# Wordstat Collector (PHP)

Система для сбора статистики запросов из Яндекс.Вордстат по брендам и регионам, сохранения в PostgreSQL и выгрузки в Excel. Есть простой веб-интерфейс: запуск сбора и скачивание отчета.

## Стек технологий

- PHP 8.2
- PostgreSQL
- PhpSpreadsheet (экспорт XLSX)
- Symfony Dotenv
- Чистый PHP (без фреймворков)

## Структура проекта

```
.
├─ public/            # Веб-корень (login.php, admin.php, run.php, export.php)
├─ src/               # Код (БД, аутентификация, сборщик, экспортер, клиент API)
├─ sql/               # schema.sql и seed.sql
├─ vendor/            # Зависимости Composer
├─ .env               # Конфигурация окружения
├─ composer.json
└─ Dockerfile
```

## Установка

1. Создать базу данных PostgreSQL:
   ```bash
   createdb wordstat
   psql -d wordstat -f sql/schema.sql
   psql -d wordstat -f sql/seed.sql
   ```

2. Настроить файл `.env`:
   ```
   APP_ENV=local
   APP_SECRET=change_me

   DB_HOST=localhost
   DB_PORT=5432
   DB_NAME=wordstat
   DB_USER=postgres
   DB_PASS=postgres

   ADMIN_LOGIN=admin
   ADMIN_PASSWORD=admin123

   YANDEX_TOKEN=ваш_токен
   ```

3. Установить зависимости (если нужно обновить):
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

## Запуск локально

### Встроенный сервер PHP
```bash
php -S 127.0.0.1:8000 -t public
```
Открыть в браузере: `http://127.0.0.1:8000/login.php`

Войти с логином и паролем из `.env`.

### Docker
```bash
docker build -t wordstat-app .
docker run --env-file .env -p 8080:80 wordstat-app
```
Открыть: `http://localhost:8080/login.php`

## Использование

1. Авторизация  
   Перейдите на `/login.php`, введите логин и пароль администратора.

2. Панель администратора (`/admin.php`)  
   - «Собрать» — запуск сбора за последние 30 дней.  
   - «Скачать XLSX» — выгрузка актуальной статистики.

3. Выход — `/logout.php`.

## Переменные окружения

- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` — настройки БД  
- `ADMIN_LOGIN`, `ADMIN_PASSWORD` — доступ в админку  
- `YANDEX_TOKEN` — токен API Яндекс.Директ/Вордстат  

## Частые проблемы

- **404 на /admin.php в Docker**: DocumentRoot не указывает на `public`. Используйте `/public/admin.php` или поправьте конфиг.  
- **Ошибка API**: проверьте `YANDEX_TOKEN`.  
- **Пустой Excel**: убедитесь, что сбор завершился успешно и данные появились в таблице `stats`.  
