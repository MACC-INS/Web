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
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Create non-root user
RUN groupadd -g 1000 app && \
    useradd -u 1000 -ms /bin/bash -g app app

# Security: Disable dangerous functions
RUN echo "disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source" >> /usr/local/etc/php/conf.d/security.ini

# Security: PHP configuration
RUN echo "expose_php = Off" >> /usr/local/etc/php/conf.d/security.ini
RUN echo "display_errors = Off" >> /usr/local/etc/php/conf.d/security.ini
RUN echo "log_errors = On" >> /usr/local/etc/php/conf.d/security.ini
RUN echo "error_log = /proc/self/fd/2" >> /usr/local/etc/php/conf.d/security.ini

# Set working directory
WORKDIR /var/www/html

# Change ownership
RUN chown -R app:app /var/www/html

# Switch to non-root user
USER app

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php -r "if (mysqli_connect('db', 'app_user', 'StrongPassword123!', 'flights', 3306)) { exit(0); } exit(1);"

# FROM php:8.1-fpm

# # Install system dependencies
# RUN apt-get update && apt-get install -y \
#     libpng-dev \
#     libonig-dev \
#     libxml2-dev \
#     libzip-dev \
#     zip \
#     unzip \
#     git \
#     curl

# # Install PHP extensions
# RUN docker-php-ext-install mysqli pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# # Install Composer
# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# # Set working directory
# WORKDIR /var/www/html