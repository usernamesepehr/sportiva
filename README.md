# 🏋️‍♂️ Sportiva

**Sportiva** is a modern sports products shop API built with Laravel 12 and PHP 8.4. It offers a secure and scalable backend for managing sports products and categories, featuring JWT-based authentication and interactive API documentation via Swagger.

---

## 🚀 Features

- Laravel 12 RESTful API
- JWT Authentication for secure user access
- Products and Categories management
- Swagger UI for API documentation
- MySQL database integration

---

## 🛠️ Tech Stack

- **Framework**: Laravel 12
- **Language**: PHP 8.4
- **Database**: MySQL
- **Auth**: [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth)
- **Docs**: Swagger

---

## ⚙️ Installation

```bash
git clone https://github.com/usernamesepehr/sportiva.git
cd sportiva
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan serve

