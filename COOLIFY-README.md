# Coolify Deployment Guide

This Laravel application has been configured for deployment on Coolify with SQLite database.

## Files Created for Coolify

- `docker-compose.coolify.yml` - Coolify-optimized Docker Compose configuration
- `Dockerfile.coolify` - Optimized Dockerfile with SQLite support
- `docker-entrypoint-coolify.sh` - Custom entrypoint script for Coolify deployment

## Environment Variables

Set these environment variables in your Coolify project:

### Required Variables
```bash
APP_NAME="PHP Sign"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=base64:your-generated-key-here
```

### Database (SQLite - No additional config needed)
```bash
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
```

### Cache Configuration
```bash
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### Mail Configuration (Optional)
```bash
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Optional Settings
```bash
TELESCOPE_ENABLED=false
LOG_LEVEL=error
SESSION_LIFETIME=120
```

## Deployment Steps

1. **Import Project to Coolify**
   - Connect your Git repository to Coolify
   - Select "Docker Compose" as deployment type

2. **Configure Build Settings**
   - Set Docker Compose file path to: `docker-compose.coolify.yml`
   - Ensure the build context is set to the repository root

3. **Set Environment Variables**
   - Add all required environment variables listed above
   - Generate APP_KEY using: `php artisan key:generate --show`

4. **Deploy**
   - Trigger deployment from Coolify dashboard
   - Monitor logs for any issues

## Key Features

- **SQLite Database**: No external database service required
- **Persistent Storage**: Database and uploads are stored in Docker volumes
- **Health Checks**: Built-in health checks for both app and webserver
- **Production Optimized**: Caching enabled, debug disabled
- **Auto-Migration**: Database migrations run automatically on startup

## Persistent Volumes

The following data is persisted across deployments:
- SQLite database (`database_data` volume)
- Laravel storage directory (`storage_data` volume)
- Bootstrap cache (`bootstrap_cache` volume)

## Troubleshooting

### Common Issues

1. **Permission Errors**
   - The entrypoint script automatically sets correct permissions
   - If issues persist, check volume mount permissions

2. **Migration Errors**
   - Ensure APP_KEY is set before first deployment
   - Check application logs in Coolify dashboard

3. **File Upload Issues**
   - Verify storage volume is properly mounted
   - Check nginx client_max_body_size in default.conf

### Accessing Logs
- View logs through Coolify dashboard
- App logs: Check the `app` service logs
- Web server logs: Check the `webserver` service logs

## Security Notes

- APP_DEBUG is set to false for production
- Database file permissions are set correctly
- HTTPS is recommended for production deployments
- Consider enabling additional Laravel security features as needed

## Customization

To customize the deployment:
1. Modify `docker-compose.coolify.yml` for service configuration
2. Update `Dockerfile.coolify` for build requirements
3. Adjust `docker-entrypoint-coolify.sh` for startup procedures 