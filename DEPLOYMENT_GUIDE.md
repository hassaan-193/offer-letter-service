# Offer Letter Service — Production Deployment & Custom Domain Guide

This is a step-by-step guide to deploying the **Offer Letter Service** onto a production server (Ubuntu VPS or cPanel) and binding it to your company's custom domain with free SSL (HTTPS).

---

## Table of Contents
1. [Architecture & Server Requirements](#1-architecture--server-requirements)
2. [Step 1: Domain DNS Configuration](#step-1-domain-dns-configuration)
3. [Step 2: Server Packages Installation (Ubuntu VPS)](#step-2-server-packages-installation-ubuntu-vps)
4. [Step 3: Database Creation](#step-3-database-creation)
5. [Step 4: Deploying the Codebase](#step-4-deploying-the-codebase)
6. [Step 5: Configuring Environment Variables (.env)](#step-5-configuring-environment-variables-env)
7. [Step 6: Dependencies, App Key & Migrations](#step-6-dependencies-app-key--migrations)
8. [Step 7: Permissions & Storage](#step-7-permissions--storage)
9. [Step 8: Nginx Web Server Configuration](#step-8-nginx-web-server-configuration)
10. [Step 9: Free SSL Certificate (HTTPS via Let's Encrypt)](#step-9-free-ssl-certificate-https-via-lets-encrypt)
11. [Step 10: Production Optimization & Caching](#step-10-production-optimization--caching)
12. [Alternative: Deploying on cPanel / Shared Hosting](#alternative-deploying-on-cpanel--shared-hosting)
13. [Post-Deployment Security Checklist](#post-deployment-security-checklist)

---

## 1. Architecture & Server Requirements

* **Operating System**: Ubuntu 22.04 LTS or 24.04 LTS (recommended)
* **PHP Version**: PHP 8.2 or 8.3
* **Required PHP Extensions**: `php-fpm`, `php-mysql`, `php-mbstring`, `php-xml`, `php-curl`, `php-zip`, `php-gd`, `php-bcmath`, `php-intl`, `php-cli`
* **Database**: MySQL 8.0+ or MariaDB 10.6+
* **Web Server**: Nginx (recommended) or Apache 2.4
* **Composer**: Composer 2.x
* **SSL**: Let's Encrypt (Certbot)

---

## Step 1: Domain DNS Configuration

Log in to the domain registrar (GoDaddy, Namecheap, Cloudflare, etc.) where your supervisor purchased the domain.

Add the following **DNS Records**:

| Type | Name / Host | Value / Target | TTL |
|---|---|---|---|
| **A** | `@` (or leave empty) | `YOUR_SERVER_PUBLIC_IP` (e.g., `123.45.67.89`) | Automatic / 300s |
| **A** or **CNAME** | `www` | `YOUR_SERVER_PUBLIC_IP` (or `@`) | Automatic / 300s |

> [!NOTE]
> DNS propagation typically takes between **5 to 30 minutes**. You can verify it by running in your terminal:
> ```bash
> ping yourdomain.com
> ```

---

## Step 2: Server Packages Installation (Ubuntu VPS)

Connect to your server via SSH:
```bash
ssh root@YOUR_SERVER_PUBLIC_IP
```

Update system packages and install Nginx, PHP 8.2, MySQL, and Composer:
```bash
sudo apt update && sudo apt upgrade -y

# Install Nginx, Git, Unzip, and Curl
sudo apt install -y nginx git unzip curl certbot python3-certbot-nginx

# Install PHP 8.2 and required extensions
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl php8.2-cli

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

Verify installations:
```bash
php -v
nginx -v
composer -V
```

---

## Step 3: Database Creation

Log in to MySQL:
```bash
sudo mysql
```

Run the following SQL queries to create the database and user (replace `YourStrongPasswordHere` with a secure password):
```sql
CREATE DATABASE offer_letter_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'fts_user'@'localhost' IDENTIFIED BY 'YourStrongPasswordHere';

GRANT ALL PRIVILEGES ON offer_letter_service.* TO 'fts_user'@'localhost';

FLUSH PRIVILEGES;
EXIT;
```

---

## Step 4: Deploying the Codebase

### Option A: Via Git (Recommended)
```bash
cd /var/www
git clone https://github.com/your-org/offer-letter-service.git offer-letter-service
cd /var/www/offer-letter-service
```

### Option B: Via SCP or SFTP (From local machine)
If uploading from your Windows machine:
```powershell
scp -r d:\FTSITS\ft_portal_base(2)\offer-letter-service root@YOUR_SERVER_IP:/var/www/offer-letter-service
```

---

## Step 5: Configuring Environment Variables (.env)

Navigate to the project root:
```bash
cd /var/www/offer-letter-service
cp .env.example .env
nano .env
```

Set the following production settings inside `.env`:
```env
APP_NAME="FTS Offer Letter Portal"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=offer_letter_service
DB_USERNAME=fts_user
DB_PASSWORD=YourStrongPasswordHere

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true

FILESYSTEM_DISK=local
```
Save and exit (`Ctrl + O`, `Enter`, `Ctrl + X`).

---

## Step 6: Dependencies, App Key & Migrations

Inside `/var/www/offer-letter-service`, run:

```bash
# 1. Install production dependencies (without development packages)
composer install --no-dev --optimize-autoloader

# 2. Generate application encryption key
php artisan key:generate

# 3. Run all database migrations
php artisan migrate --force

# 4. Seed the initial Administrator account
php artisan db:seed --force
```

> [!TIP]
> The initial seeder creates an admin account:
> - **Email**: `admin@example.com`
> - **Password**: `ChangeThisPassword`
> 
> *Change this password immediately after first login.*

---

## Step 7: Permissions & Storage

Laravel needs write permissions to `storage` and `bootstrap/cache`:

```bash
# Set ownership to Nginx user (www-data)
sudo chown -R www-data:www-data /var/www/offer-letter-service

# Grant directory read/write permissions
sudo chmod -R 775 /var/www/offer-letter-service/storage
sudo chmod -R 775 /var/www/offer-letter-service/bootstrap/cache

# Ensure PDF and signature directories exist
mkdir -p /var/www/offer-letter-service/storage/app/offers
sudo chown -R www-data:www-data /var/www/offer-letter-service/storage/app
```

---

## Step 8: Nginx Web Server Configuration

Create an Nginx server block for your domain:
```bash
sudo nano /etc/nginx/sites-available/offer-letter-service
```

Paste the following configuration (replace `yourdomain.com` and `www.yourdomain.com` with your supervisor's actual domain):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/offer-letter-service/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    # Allow up to 20MB for signature submissions and PDF generation
    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site configuration and test Nginx:
```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/offer-letter-service /etc/nginx/sites-enabled/

# Remove default site if present
sudo rm -f /etc/nginx/sites-enabled/default

# Test Nginx syntax
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

---

## Step 9: Free SSL Certificate (HTTPS via Let's Encrypt)

Run Certbot to automatically configure SSL and redirect all HTTP traffic to HTTPS:

```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

* Follow the prompts: enter your email address and agree to the terms.
* Certbot will automatically issue the certificate, configure Nginx for SSL, and set up automatic renewal.

Verify SSL certificate renewal works:
```bash
sudo certbot renew --dry-run
```

---

## Step 10: Production Optimization & Caching

To make the application fast and responsive in production, compile and cache all configurations, routes, and Blade views:

```bash
cd /var/www/offer-letter-service

# Cache configurations
php artisan config:cache

# Cache route table
php artisan route:cache

# Cache compiled Blade views
php artisan view:cache
```

> [!NOTE]
> If you make any code updates or changes to `.env` in the future, clear and refresh the cache:
> ```bash
> php artisan optimize:clear
> php artisan optimize
> ```

---

## Alternative: Deploying on cPanel / Shared Hosting

If your company is using cPanel shared hosting instead of an Ubuntu VPS:

1. **Upload Files**:
   * Compress `offer-letter-service` into a `.zip` file (exclude `vendor/` and `.git/`).
   * In cPanel **File Manager**, upload and extract files to a folder outside public root: `/home/username/offer-letter-service`.
2. **Move Public Content**:
   * Move the contents of `offer-letter-service/public/` into `/home/username/public_html/`.
   * In `public_html/index.php`, update the paths to point to the parent directory:
     ```php
     require __DIR__.'/../offer-letter-service/vendor/autoload.php';
     $app = require_once __DIR__.'/../offer-letter-service/bootstrap/app.php';
     ```
3. **Database in cPanel**:
   * Go to **MySQL Database Wizard**, create database and user, and assign All Privileges.
   * Edit `/home/username/offer-letter-service/.env` with the cPanel database name, user, and password.
4. **Run Migrations via cPanel Terminal**:
   * Open **Terminal** in cPanel:
     ```bash
     cd ~/offer-letter-service
     composer install --no-dev --optimize-autoloader
     php artisan migrate --force
     php artisan db:seed --force
     php artisan storage:link
     ```
5. **Issue SSL**:
   * In cPanel, navigate to **SSL/TLS Status** or **AutoSSL** and click **Run AutoSSL** on your domain.

---

## Post-Deployment Security Checklist

- [ ] **Change Admin Password**: Log in to `https://yourdomain.com/login` using `admin@example.com` / `ChangeThisPassword` and update the password.
- [ ] **Verify `APP_DEBUG=false`**: Ensure `.env` has `APP_DEBUG=false` so stack traces are never exposed to the public.
- [ ] **Test Full Workflow**:
  1. Create a draft offer.
  2. Add custom content under **Card 5** (Clause 10).
  3. Publish and copy the candidate link (`https://yourdomain.com/offer/{token}`).
  4. Open in an incognito window, scroll to Page 5, and sign using the digital canvas.
  5. Download the final signed PDF and verify the letterhead alignment, custom clause, and digital verification seal.
- [ ] **Verify Deletion**: Test deleting a test offer letter to confirm cascading deletion cleans up both database records and storage files.
