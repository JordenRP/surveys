# Survey Application

## Описание
Простое веб-приложение для проведения анкетирования. Пользователи могут:
- Заполнять анкеты.
- Просматривать свои анкеты в личном кабинете.
- Администраторы могут управлять всеми анкетами (редактирование и удаление).

## Технологический стек
- Backend: PHP (чистый).
- Frontend: JavaScript (чистый).
- База данных: PostgreSQL.
- Развертывание: Docker Compose.

## Запуск приложения
1. Убедитесь, что Docker и Docker Compose установлены.
2. Клонируйте проект:
   ```bash
   git clone git@github.com:JordenRP/surveys.git
   ```
3. Перейдите в директорию проекта:
   ```bash
   cd surveys
   ```
4. Запустите приложение:
   ```bash
   docker compose up --build
   ```
5. Откройте браузер и перейдите по адресу:
   ```
   http://localhost:8080
   ```

## Основные страницы
- Главная страница: [http://localhost:8080](http://localhost:8080)
- Личный кабинет: [http://localhost:8080/dashboard.php](http://localhost:8080/dashboard.php)
- Административная панель: [http://localhost:8080/admin.php](http://localhost:8080/admin.php)

## Работа с пользователями
- По умолчанию создаётся администратор с данными:
  - Логин: `admin`
  - Пароль: `123456`

- Для создания администратора выполните SQL-запрос:
  ```sql
  UPDATE users SET is_admin = TRUE WHERE username = '<username>';
  ```

## Примечания
- После первого запуска база данных будет автоматически инициализирована.
- При необходимости настройки базы данных можно изменить в `docker-compose.yml`.

