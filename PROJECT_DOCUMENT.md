# Laravel Task & Role Management Portal

Project Path: /var/www/task-portal (deployment target)
Local Dev Path: C:/xampp/htdocs/task-portal

Overview

-   A lightweight workflow orchestration module to manage tasks, assign roles, and track completion metrics.
-   Covers authentication, role-based authorization, CRUD operations, activity logging, REST API endpoints, and queue-ready email notifications.

Tech Stack

1. Core

-   Language: PHP 8.2+
-   Framework: Laravel 12.x (tested on 12.44.0)
-   Web Server (production): Nginx with PHP-FPM
-   OS (production): Ubuntu 22.04 LTS or newer
-   Package Manager: Composer

2. Authentication & Authorization

-   Authentication: Session-based auth with email verification (Breeze-style scaffolding)
-   API Auth: Laravel Sanctum (token or session-based)
-   RBAC: spatie/laravel-permission
    -   Middleware aliases: role, permission, role_or_permission (registered in bootstrap/app.php)
    -   User model uses Spatie HasRoles trait
-   Policies: TaskPolicy for create/update permissions

3. Database & ORM

-   Database: MySQL (InnoDB, utf8mb4)
-   ORM: Eloquent ORM
-   Migrations:
    -   Users, cache, jobs table
    -   Tasks: tasks table with status enum (Pending, In Progress, Completed)
    -   Roles & Permissions: per Spatie package
    -   Activity Logs: task_activities
-   Seeders:
    -   Creates roles (Admin, Manager, Staff) and demo users (verified)
    -   Seeds an initial task for Staff

4. Queues & Mail

-   Queues: Laravel Queue (supports sync/dev and Redis for production)
-   Job: SendTaskUpdateEmail (queued)
-   Mailable: TaskStatusUpdated
-   Mailers: Configurable via .env (log in dev, SMTP in prod)

5. Views & Frontend

-   Blade templates (Tailwind CSS classes)
-   Vite for asset bundling (optional; only minimal assets in this project)

6. Logging & Activity Tracking

-   Laravel application logging (config/logging.php)
-   TaskActivity model + table to track task creation and status changes
-   TaskObserver writes activity records and logs to file

Key Composer Packages

-   laravel/framework ^12
-   laravel/sanctum
-   spatie/laravel-permission

Important Code Locations

-   Controllers: app/Http/Controllers/TaskController.php
-   Policies: app/Policies/TaskPolicy.php
-   Observers: app/Observers/TaskObserver.php
-   Models: app/Models/Task.php, app/Models/User.php, app/Models/TaskActivity.php
-   Mail/Jobs: app/Mail/TaskStatusUpdated.php, app/Jobs/SendTaskUpdateEmail.php
-   Providers: app/Providers/AppServiceProvider.php, app/Providers/AuthServiceProvider.php
-   Bootstrap Configuration: bootstrap/app.php (middleware aliases), bootstrap/providers.php (AuthServiceProvider registration)
-   Routes: routes/web.php, routes/api.php
-   Views: resources/views/tasks/index.blade.php, resources/views/emails/task_status_updated.blade.php
-   Migrations/Seeders: database/migrations/\*, database/seeders/DatabaseSeeder.php

Authorization & Roles

-   Roles: Admin, Manager, Staff
-   Create Task: Admin/Manager only
-   Update Task: Admin/Manager, or Staff only for their assigned tasks
-   Staff visibility: Staff sees only their tasks; Admin/Manager see all

Seeded Demo Accounts (after php artisan migrate --seed)

-   Admin: admin@example.com / password
-   Manager: manager@example.com / password
-   Staff: staff@example.com / password

REST API Endpoints (Sanctum-protected)

-   GET /api/tasks — list tasks (scoped for Staff to their own)
-   POST /api/tasks — create task (Admin/Manager only)

Deployment Guide (Ubuntu + Nginx)

1. Install System Packages

```bash
sudo apt update
sudo apt install -y nginx mysql-server \
  php8.2-fpm php8.2-cli php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-mysql php8.2-gd \
  unzip git redis-server

# Install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# Start services
sudo systemctl enable --now nginx php8.2-fpm mysql redis-server
```

2. Prepare Application Directory

```bash
sudo mkdir -p /var/www/task-portal
# Copy or git clone your project into /var/www/task-portal
# Example:
# sudo git clone <your-repo-url> /var/www/task-portal

sudo chown -R www-data:www-data /var/www/task-portal
sudo find /var/www/task-portal -type f -exec chmod 0644 {} \;
sudo find /var/www/task-portal -type d -exec chmod 0755 {} \;
sudo chgrp -R www-data /var/www/task-portal/storage /var/www/task-portal/bootstrap/cache
sudo chmod -R ug+rwx /var/www/task-portal/storage /var/www/task-portal/bootstrap/cache
```

3. Environment Configuration

```bash
cd /var/www/task-portal
cp .env.example .env
php artisan key:generate
```

Edit .env (example):

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_portal
DB_USERNAME=task_portal_user
DB_PASSWORD=your-strong-password

SESSION_DRIVER=database
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-user
MAIL_PASSWORD=your-pass
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Task Portal"
```

4. Install Dependencies & Build Assets

```bash
cd /var/www/task-portal
composer install --no-dev --optimize-autoloader
# Optional (if you decide to build assets via Vite):
# curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
# sudo apt install -y nodejs
# npm ci && npm run build
```

5. Database Setup

```bash
# Create DB and user
sudo mysql -e "CREATE DATABASE task_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'task_portal_user'@'localhost' IDENTIFIED BY 'your-strong-password';"
sudo mysql -e "GRANT ALL PRIVILEGES ON task_portal.* TO 'task_portal_user'@'localhost'; FLUSH PRIVILEGES;"

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force
```

6. Nginx Configuration
   Create /etc/nginx/sites-available/task-portal with:

```
server {
    listen 80;
    server_name your-domain.com;

    root /var/www/task-portal/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        try_files $uri /index.php?$query_string;
        expires max;
        log_not_found off;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Then enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/task-portal /etc/nginx/sites-enabled/task-portal
sudo nginx -t
sudo systemctl reload nginx
```

7. Laravel Production Optimization

```bash
cd /var/www/task-portal
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

8. Queue Worker (for async email)
   Create /etc/systemd/system/task-portal-queue.service:

```
[Unit]
Description=Task Portal Queue Worker
After=redis-server.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/task-portal/artisan queue:work --queue=default --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Then:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now task-portal-queue
```

9. Scheduler (optional)

```bash
# as root
sudo crontab -e
# add the following line
* * * * * cd /var/www/task-portal && php artisan schedule:run >> /dev/null 2>&1
```

10. SSL/TLS

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

11. Laravel 12 Middleware Aliases (Important)

-   Registered in bootstrap/app.php:
    -   role => Spatie\Permission\Middleware\RoleMiddleware
    -   permission => Spatie\Permission\Middleware\PermissionMiddleware
    -   role_or_permission => Spatie\Permission\Middleware\RoleOrPermissionMiddleware
-   AuthServiceProvider registered in bootstrap/providers.php so policies are loaded.

12. Post-Deployment Verification

-   Log in at /login using seeded credentials:
    -   admin@example.com / password
    -   manager@example.com / password
    -   staff@example.com / password
-   Visit /tasks:
    -   Admin/Manager can assign tasks to users.
    -   Staff sees only their tasks and can only update their own tasks.
-   Confirm activity logs in task_activities and status changes in storage/logs/laravel.log.
-   For email notifications: set QUEUE_CONNECTION (redis or database), configure SMTP, and uncomment dispatch in App\Observers\TaskObserver.

Security & Hardening

-   Ensure APP_DEBUG=false in production.
-   Keep storage and bootstrap/cache writable by www-data only.
-   Use SESSION_DRIVER=database in multi-instance setups.
-   Restrict SSH and DB access; use strong passwords.
-   Regularly update packages and rotate keys as needed.

Notes

-   The project uses Laravel Sanctum for API auth (bearer tokens or SPA cookie-based sessions).
-   Roles and permissions are managed by Spatie package and enforced via middleware and policies.
-   Minimal frontend dependencies; Blade + Tailwind classes suffice for UI.
