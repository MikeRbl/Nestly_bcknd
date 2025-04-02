# 🏡 Nestly Backend 

**Nestly** es una plataforma de alquiler de propiedades desarrollada con Laravel, MySQL y Docker.

## 🚀 Características principales
- CRUD completo de propiedades
- Sistema de solicitudes de alquiler

## 🛠 Tecnologías
- **Laravel 10** - Framework PHP
- **MySQL 8** - Base de datos
- **Docker** - Contenedores (PHP + MySQL)

# ## 📥 Instalación  
```sh
git clone https://github.com/MikeRbl/Nestly_bcknd.git
cd nestly/backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
