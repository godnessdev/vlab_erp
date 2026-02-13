# Deployment Guide for AI Agents

## Overview
This ERP system requires a robust deployment strategy supporting multitenant architecture, high availability, and compliance requirements for Brazilian businesses.

## Infrastructure Architecture

### 1. Production Environment Stack

```yaml
# docker-compose.production.yml
version: '3.8'

services:
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./nginx/ssl:/etc/nginx/ssl
      - app_storage:/var/www/storage
    depends_on:
      - app
    restart: unless-stopped

  app:
    build:
      context: .
      dockerfile: Dockerfile.production
    environment:
      - APP_ENV=production
      - APP_KEY=${APP_KEY}
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - REDIS_HOST=redis
      - QUEUE_CONNECTION=redis
      - CACHE_DRIVER=redis
    volumes:
      - app_storage:/var/www/storage
    depends_on:
      - postgres
      - redis
    restart: unless-stopped
    deploy:
      replicas: 3
      resources:
        limits:
          memory: 512M
          cpus: '0.5'

  queue-worker:
    build:
      context: .
      dockerfile: Dockerfile.production
    command: php artisan queue:work redis --tries=3 --max-time=3600
    environment:
      - APP_ENV=production
      - QUEUE_CONNECTION=redis
      - REDIS_HOST=redis
    depends_on:
      - postgres
      - redis
    restart: unless-stopped
    deploy:
      replicas: 2

  scheduler:
    build:
      context: .
      dockerfile: Dockerfile.production
    command: php artisan schedule:work
    environment:
      - APP_ENV=production
    depends_on:
      - postgres
      - redis
    restart: unless-stopped

  postgres:
    image: postgres:16-alpine
    environment:
      - POSTGRES_DB=${DB_DATABASE}
      - POSTGRES_USER=${DB_USERNAME}
      - POSTGRES_PASSWORD=${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./database/init.sql:/docker-entrypoint-initdb.d/init.sql
    restart: unless-stopped

  redis:
    image: redis:7-alpine
    command: redis-server --appendonly yes
    volumes:
      - redis_data:/data
    restart: unless-stopped

volumes:
  postgres_data:
  redis_data:
  app_storage:
```

### 2. Production Dockerfile

```dockerfile
# Dockerfile.production
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    icu-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install \
    pdo_pgsql \
    gd \
    xml \
    zip \
    intl \
    opcache \
    bcmath \
    sockets

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure PHP for production
COPY ./docker/php/production.ini /usr/local/etc/php/conf.d/production.ini

# Set working directory
WORKDIR /var/www

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy application code
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Generate optimized autoloader and cache
RUN composer dump-autoload --optimize
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
```

### 3. Nginx Configuration

```nginx
# nginx/nginx.conf
upstream app {
    server app:9000;
}

server {
    listen 80;
    server_name erp.company.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name erp.company.com;
    
    ssl_certificate /etc/nginx/ssl/certificate.crt;
    ssl_certificate_key /etc/nginx/ssl/private.key;
    
    # SSL security configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    
    root /var/www/public;
    index index.php;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";
    
    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass app;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }
    
    # Static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(vendor|storage|bootstrap|database)/ {
        deny all;
    }
}
```

## Database Configuration

### 1. PostgreSQL Production Setup

```sql
-- database/init.sql
-- Performance optimizations
ALTER SYSTEM SET shared_buffers = '256MB';
ALTER SYSTEM SET effective_cache_size = '1GB';
ALTER SYSTEM SET maintenance_work_mem = '64MB';
ALTER SYSTEM SET checkpoint_completion_target = 0.9;
ALTER SYSTEM SET wal_buffers = '16MB';
ALTER SYSTEM SET default_statistics_target = 100;

-- Security configurations
ALTER SYSTEM SET ssl = on;
ALTER SYSTEM SET log_statement = 'mod';
ALTER SYSTEM SET log_min_duration_statement = 1000;

-- Row Level Security for multitenancy
ALTER DATABASE erp_production SET row_security = on;

-- Create application user
CREATE USER app_user WITH PASSWORD 'secure_password_here';
GRANT CONNECT ON DATABASE erp_production TO app_user;
```

### 2. Database Migration Strategy

```bash
#!/bin/bash
# scripts/deploy-database.sh

set -e

echo "Starting database deployment..."

# Backup current database
pg_dump $DATABASE_URL > backup_$(date +%Y%m%d_%H%M%S).sql

# Run migrations
php artisan migrate --force

# Seed production data if needed
if [ "$SEED_PRODUCTION" = "true" ]; then
    php artisan db:seed --class=ProductionSeeder --force
fi

# Update database statistics
php artisan db:analyze

echo "Database deployment completed successfully"
```

## Environment Configuration

### 1. Production Environment Variables

```bash
# .env.production
APP_NAME="ERP System"
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://erp.company.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=erp_production
DB_USERNAME=app_user
DB_PASSWORD=secure_password_here

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
FILESYSTEM_DISK=s3
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls

# AWS Configuration for file storage
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=sa-east-1
AWS_BUCKET=your-s3-bucket

# Security
SANCTUM_STATEFUL_DOMAINS=erp.company.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Rate limiting
THROTTLE_API_REQUESTS=1000
THROTTLE_API_DECAY=60

# Fiscal compliance
SEFAZ_ENVIRONMENT=production
SEFAZ_CERTIFICATE_PATH=/var/certificates/company.p12
SEFAZ_CERTIFICATE_PASSWORD=certificate_password
```

### 2. Laravel Configuration

```php
// config/production/database.php
return [
    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'require',
            'options' => [
                PDO::ATTR_TIMEOUT => 30,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],
    ],
];

// config/production/cache.php
return [
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],
    ],
];
```

## Deployment Pipeline

### 1. CI/CD Configuration (GitHub Actions)

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_PASSWORD: postgres
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: pdo, pdo_pgsql, zip, gd
      
      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader
      
      - name: Copy environment file
        run: cp .env.testing .env
      
      - name: Generate application key
        run: php artisan key:generate
      
      - name: Run tests
        run: php artisan test --parallel
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_DATABASE: postgres
          DB_USERNAME: postgres
          DB_PASSWORD: postgres

  deploy:
    needs: test
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Deploy to production
        uses: appleboy/ssh-action@v0.1.7
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/erp
            
            # Backup current version
            cp -r current backup_$(date +%Y%m%d_%H%M%S)
            
            # Pull latest changes
            git pull origin main
            
            # Install dependencies
            composer install --no-dev --optimize-autoloader
            
            # Run deployment script
            ./scripts/deploy.sh
```

### 2. Deployment Script

```bash
#!/bin/bash
# scripts/deploy.sh

set -e

echo "Starting deployment process..."

# Put application in maintenance mode
php artisan down --retry=60 --secret="deployment-secret-key"

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run database migrations
php artisan migrate --force

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
php artisan queue:restart

# Restart PHP-FPM
sudo service php8.4-fpm restart

# Restart nginx
sudo service nginx restart

# Clear application cache one more time
php artisan cache:clear

# Bring application back up
php artisan up

echo "Deployment completed successfully!"
```

## Monitoring and Logging

### 1. Application Monitoring

```php
// config/logging.php
return [
    'channels' => [
        'production' => [
            'driver' => 'stack',
            'channels' => ['daily', 'slack'],
            'ignore_exceptions' => false,
        ],
        
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'warning'),
            'days' => 14,
        ],
        
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'ERP System',
            'emoji' => ':boom:',
            'level' => 'error',
        ],
    ],
];
```

### 2. Health Checks

```php
// app/Http/Controllers/HealthController.php
class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];
        
        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');
        
        return response()->json([
            'status' => $allHealthy ? 'ok' : 'error',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
        ], $allHealthy ? 200 : 503);
    }
    
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Database connected'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }
    
    private function checkRedis(): array
    {
        try {
            Redis::ping();
            return ['status' => 'ok', 'message' => 'Redis connected'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Redis connection failed'];
        }
    }
}

// routes/web.php
Route::get('/health', [HealthController::class, 'check']);
```

## Security Configuration

### 1. SSL/TLS Certificate Management

```bash
#!/bin/bash
# scripts/setup-ssl.sh

# Install certbot for Let's Encrypt
sudo apt update
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d erp.company.com

# Setup auto-renewal
echo "0 12 * * * /usr/bin/certbot renew --quiet" | sudo crontab -
```

### 2. Firewall Configuration

```bash
#!/bin/bash
# scripts/setup-firewall.sh

# Configure UFW firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH
sudo ufw allow ssh

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow database access only from application servers
sudo ufw allow from 10.0.1.0/24 to any port 5432

# Enable firewall
sudo ufw enable
```

## Backup and Recovery

### 1. Automated Backups

```bash
#!/bin/bash
# scripts/backup.sh

set -e

BACKUP_DIR="/var/backups/erp"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Database backup
pg_dump $DATABASE_URL | gzip > $BACKUP_DIR/database_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/storage

# Upload to S3
aws s3 cp $BACKUP_DIR/database_$DATE.sql.gz s3://erp-backups/database/
aws s3 cp $BACKUP_DIR/files_$DATE.tar.gz s3://erp-backups/files/

# Clean old local backups (keep 7 days)
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

echo "Backup completed: $DATE"

# Add to cron: 0 2 * * * /var/www/erp/scripts/backup.sh
```

### 2. Recovery Procedures

```bash
#!/bin/bash
# scripts/restore.sh

BACKUP_FILE=$1

if [ -z "$BACKUP_FILE" ]; then
    echo "Usage: $0 <backup_file>"
    exit 1
fi

# Put application in maintenance mode
php artisan down

# Restore database
gunzip -c $BACKUP_FILE | psql $DATABASE_URL

# Clear caches
php artisan cache:clear
php artisan config:clear

# Bring application back up
php artisan up

echo "Restore completed from: $BACKUP_FILE"
```

## Performance Optimization

### 1. PHP-FPM Configuration

```ini
; /etc/php/8.4/fpm/pool.d/www.conf
[www]
user = www-data
group = www-data

listen = 127.0.0.1:9000
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

; Production optimizations
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2. Redis Configuration

```conf
# /etc/redis/redis.conf
maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Security
requirepass your_redis_password
```

## AI Agent Deployment Guidelines

### 1. Configuration Management

```php
// WRONG - Hardcoded configuration
$apiUrl = 'https://sefaz.sp.gov.br/ws';

// CORRECT - Environment-based configuration
$apiUrl = config('services.sefaz.url');
```

### 2. Error Handling in Production

```php
// WRONG - Exposing internal details
catch (Exception $e) {
    return response()->json(['error' => $e->getMessage()]);
}

// CORRECT - Safe error handling
catch (Exception $e) {
    Log::error('Order creation failed', ['error' => $e->getMessage()]);
    return response()->json(['error' => 'An error occurred processing your request']);
}
```

### 3. Performance Considerations

- Always use Redis for caching in production
- Enable OPcache for PHP
- Use CDN for static assets
- Implement proper database indexes
- Monitor and optimize slow queries
- Use queue workers for heavy operations

### 4. Security Best Practices

- Never commit secrets to version control
- Use HTTPS everywhere
- Implement proper CSRF protection
- Validate and sanitize all inputs
- Keep dependencies updated
- Monitor for security vulnerabilities
