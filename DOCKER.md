# Docker Setup for Atelier Laravel Project

Bu Docker konfiguratsiyasi Atelier Laravel proyektini ishga tushirish uchun tayyorlangan.

## Servislar

### 1. MySQL Database
- **Container nomi**: `atelier_mysql`
- **Port**: `3307:3306` (host:container)
- **Database**: `atelier_db`
- **Username**: `atelier_user`
- **Password**: `atelier_password`
- **Root password**: `root_secret`

### 2. phpMyAdmin
- **Container nomi**: `atelier_phpmyadmin`
- **Port**: `8081:80`
- **URL**: http://localhost:8081
- MySQL serverga avtomatik ulanadi

### 3. PHP-FPM
- **Container nomi**: `atelier_php`
- **Version**: PHP 8.2-FPM
- **Extensions**: PDO, PDO_MySQL, GD
- Avtomatik ravishda storage va cache papkalariga ruxsat beradi

### 4. Nginx
- **Container nomi**: `atelier_nginx`
- **Port**: `8080:80`
- **URL**: http://localhost:8080
- Laravel routing uchun sozlangan

## Ishga tushirish

### 1. Docker containerlarni ishga tushirish
```bash
docker-compose up -d
```

### 2. Environment faylini nusxalash
```bash
cp .env.docker .env
```

### 3. Composer dependencies o'rnatish
```bash
docker-compose exec php composer install
```

### 4. Application key generatsiya qilish
```bash
docker-compose exec php php artisan key:generate
```

### 5. Database migratsiyalarni ishga tushirish
```bash
docker-compose exec php php artisan migrate
```

### 6. (Ixtiyoriy) Seed data qo'shish
```bash
docker-compose exec php php artisan db:seed
```

## Foydali buyruqlar

### Containerlarni to'xtatish
```bash
docker-compose down
```

### Containerlarni qayta ishga tushirish
```bash
docker-compose restart
```

### PHP containerga kirish
```bash
docker-compose exec php bash
```

### Loglarni ko'rish
```bash
docker-compose logs -f
```

### Ma'lum bir servisning logini ko'rish
```bash
docker-compose logs -f nginx
docker-compose logs -f php
docker-compose logs -f mysql
```

### Cache tozalash
```bash
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan route:clear
docker-compose exec php php artisan view:clear
```

## Muammolarni hal qilish

### Storage ruxsatlari muammosi
Agar storage yoki cache bilan bog'liq xatoliklar bo'lsa:
```bash
docker-compose exec php chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec php chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

### Database ulanish muammosi
MySQL container to'liq ishga tushishini kuting (healthcheck):
```bash
docker-compose ps
```

### Portlar band bo'lsa
Agar 8080, 8081 yoki 3307 portlari band bo'lsa, `docker-compose.yml` faylidagi portlarni o'zgartiring.

## Kirish manzillari

- **Laravel Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **MySQL**: localhost:3307

## Eslatma

- `.env.docker` faylini `.env` ga nusxalashni unutmang
- Birinchi marta ishga tushirganda, PHP container dependencies o'rnatadi, bu biroz vaqt olishi mumkin
- Barcha ma'lumotlar `mysql_data` volume'da saqlanadi
