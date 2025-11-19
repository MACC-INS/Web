FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Create non-root user
RUN groupadd -g 1000 app && \
    useradd -u 1000 -ms /bin/bash -g app app

# Security: Disable dangerous functions
RUN echo "disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source,phpinfo,dl" >> /usr/local/etc/php/conf.d/security.ini

# Security: PHP configuration
RUN { \
    echo "expose_php = Off"; \
    echo "display_errors = Off"; \
    echo "log_errors = On"; \
    echo "error_log = /proc/self/fd/2"; \
    echo "allow_url_fopen = Off"; \
    echo "allow_url_include = Off"; \
    echo "file_uploads = Off"; \
    echo "max_execution_time = 30"; \
    echo "memory_limit = 128M"; \
    echo "post_max_size = 8M"; \
    echo "upload_max_filesize = 2M"; \
    echo "session.cookie_httponly = 1"; \
    echo "session.cookie_secure = 1"; \
    echo "session.use_strict_mode = 1"; \
    } >> /usr/local/etc/php/conf.d/security.ini

# Set working directory
WORKDIR /var/www/html

# Change ownership
RUN chown -R app:app /var/www/html

# Switch to non-root user
USER app

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php -r "if (mysqli_connect('db', 'app_user', getenv('DB_APP_PASSWORD'), 'flights', 3306)) { exit(0); } exit(1);"