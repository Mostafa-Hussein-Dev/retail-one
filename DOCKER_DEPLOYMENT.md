# Docker Deployment Guide for Laravel POS

Complete Docker setup for deploying the Laravel POS application on Render.com or any Docker-compatible platform.

## 📋 Prerequisites

- Docker installed locally
- Basic understanding of environment variables
- Render.com account (for deployment)
- Git repository

---

## 🏗️ File Structure

```
retail-pos/
├── Dockerfile                 # Main Docker image definition
├── docker-compose.yml         # Local development setup
├── .dockerignore             # Files excluded from Docker build
├── .env.render.example       # Example environment variables for Render
├── render.yaml               # Render.com deployment configuration
└── docker/
    ├── nginx.conf            # Nginx configuration
    ├── supervisord.conf      # Process manager configuration
    ├── docker-entrypoint.sh  # Startup script
    └── mysql/
        └── my.cnf            # MySQL configuration
```

---

## 🚀 Local Development with Docker

### 1. Setup Environment File

Copy your existing `.env` file or create a new one:

```bash
cp .env .env.local
```

### 2. Start Docker Compose

```bash
# Build and start all services
docker-compose up -d

# View logs
docker-compose logs -f app
```

### 3. Access Your Application

- **Main App**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Username: `pos_user`
  - Password: `secret`
- **Mailhog** (email testing): http://localhost:8025

### 4. Run Initial Setup

```bash
# Enter the container
docker-compose exec app bash

# Install dependencies (if needed)
composer install
npm install

# Run migrations
php artisan migrate

# Generate application key (if not set)
php artisan key:generate

# Link storage (if not linked)
php artisan storage:link

# Build frontend assets
npm run build

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 5. Stop Services

```bash
docker-compose down
```

### 6. Rebuild After Changes

```bash
# Rebuild specific service
docker-compose up -d --build app

# Rebuild all services
docker-compose build --no-cache
docker-compose up -d
```

---

## 🌐 Deploying to Render.com

### Option 1: Automatic Deployment (Recommended)

1. **Push Code to Git**
   ```bash
   git add .
   git commit -m "Add Docker configuration"
   git push origin main
   ```

2. **Create New Web Service on Render**
   - Go to [Render.com](https://render.com)
   - Click "New +" → "Web Service"
   - Connect your Git repository
   - Select the branch to deploy

3. **Configure Deployment**

   Render will auto-detect the `Dockerfile`. Configure:

   - **Name**: `laravel-pos` (or your preferred name)
   - **Region**: Oregon (or closest to your users)
   - **Branch**: `main`
   - **Root Directory**: `.` (leave empty)
   - **Dockerfile Path**: `./Dockerfile`
   - **Docker Context**: `.` (leave empty)

4. **Add Environment Variables** (in Render Dashboard)

   Required:
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=<generate with: php artisan key:generate>
   ```

   Database (Render provides these when you add a database):
   ```bash
   DB_CONNECTION=mysql
   DB_HOST=<from Render dashboard>
   DB_PORT=3306
   DB_DATABASE=<from Render dashboard>
   DB_USERNAME=<from Render dashboard>
   DB_PASSWORD=<from Render dashboard>
   ```

   Optional:
   ```bash
   CACHE_DRIVER=file
   SESSION_DRIVER=file
   QUEUE_CONNECTION=sync
   ```

5. **Add Database** (Optional)

   - Click "New +" → "PostgreSQL" or "MySQL"
   - Configure database settings
   - Render will provide connection details
   - Add these to your web service environment variables

6. **Deploy**

   - Click "Create Web Service"
   - Render will automatically build and deploy
   - Watch the build logs for any errors

### Option 2: Using render.yaml

1. **Copy render.yaml to your repository root** (already included)

2. **Connect Repository to Render**

3. **Render will automatically:**
   - Create a web service from `render.yaml`
   - Set up the database (if configured)
   - Configure environment variables

4. **Add missing environment variables** in the Render dashboard

---

## 🔧 Troubleshooting

### Build Issues

**Issue**: `composer install` fails
```bash
# Solution: Ensure composer.json and composer.lock are committed
git add composer.json composer.lock
git commit -m "Fix composer files"
```

**Issue**: `npm run build` fails
```bash
# Solution: Ensure package.json and package-lock.json are committed
git add package.json package-lock.json
git commit -m "Fix npm files"
```

### Runtime Issues

**Issue**: 500 Internal Server Error
```bash
# Check logs
docker-compose logs app

# Common causes:
# - APP_KEY not set
# - Database connection failed
# - Storage permissions
# - Missing .env file
```

**Issue**: Database connection failed
```bash
# Verify database is running
docker-compose ps mysql

# Check database logs
docker-compose logs mysql

# Test connection from app container
docker-compose exec app php artisan db:show
```

**Issue**: Assets not loading
```bash
# Rebuild frontend assets
docker-compose exec app npm run build

# Clear and cache views
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan config:clear
```

### Migration Issues

**Issue**: Migrations fail on deployment
```bash
# Run migrations manually in Render shell
# Access shell from Render dashboard

php artisan migrate:fresh --seed
```

---

## 📊 Monitoring & Logging

### View Logs

**Docker Compose:**
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f mysql
```

**Render:**
- Go to your web service dashboard
- Click "Logs" tab
- Real-time logs available

### Health Checks

The Dockerfile includes a health check:
```bash
curl http://localhost/
```

Render uses this to monitor service health.

---

## 🔄 Update Deployment

### Automatic (Recommended)

When you push to your Git branch, Render automatically redeploys.

```bash
git add .
git commit -m "Update application"
git push origin main
```

### Manual

Trigger a manual deploy from Render dashboard:
- Go to web service
- Click "Manual Deploy" → "Deploy latest commit"

---

## 🛡️ Security Best Practices

1. **Never commit sensitive data**
   - Use environment variables for secrets
   - Add `.env` to `.gitignore`

2. **Rotate secrets regularly**
   - Change `APP_KEY`
   - Update database passwords

3. **Use HTTPS in production**
   - Render provides automatic SSL certificates

4. **Keep dependencies updated**
   ```bash
   composer update
   npm update
   ```

5. **Set appropriate file permissions**
   - Storage: 775
   - Cache: 775

---

## 📦 What's Included

### PHP Extensions
- bcmath (for precise calculations)
- exif (for image metadata)
- gd (for image processing)
- mbstring (for string manipulation)
- pdo_mysql (for MySQL database)
- zip (for file archives)
- redis (for caching)

### Features
- ✅ Multi-stage build (optimized image size)
- ✅ Nginx + PHP-FPM
- ✅ Supervisord (process manager)
- ✅ Automatic migrations
- ✅ Storage linking
- ✅ Route caching
- ✅ Health checks
- ✅ Vite asset building
- ✅ Optimized for production

---

## 🆘 Support

If you encounter issues:

1. Check Render deployment logs
2. Review Docker Compose logs locally
3. Verify environment variables
4. Test locally with `docker-compose` first

---

## 📝 Notes

- The app listens on port `80` internally (mapped to `8080` locally)
- Render automatically routes external traffic to port `80`
- All Laravel functionality preserved:
  - Routing ✅
  - Blade templates ✅
  - API endpoints ✅
  - Database connections ✅
  - File uploads ✅
  - Sessions ✅
  - Cache ✅

---

## 🚦 Production Checklist

Before deploying to production:

- [ ] Set `APP_DEBUG=false`
- [ ] Generate secure `APP_KEY`
- [ ] Configure production database
- [ ] Set up SSL (automatic on Render)
- [ ] Configure email settings
- [ ] Set up backups
- [ ] Configure logging
- [ ] Test all functionality
- [ ] Review security settings
- [ ] Monitor first deployment

---

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Docker Documentation](https://docs.docker.com/)
- [Render Documentation](https://render.com/docs)
- [Nginx Laravel Guide](https://www.nginx.com/resources/wiki/start/topics/examples/laravel/)
