FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install and enable the necessary PHP extensions
RUN docker-php-ext-install pdo_mysql zip bcmath

# Copy Composer from the official Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create a laravel user to run the application
ARG user=laravel 
ARG uid=1000 
RUN useradd -G www-data,root -u $uid -d /home/$user $user 
RUN mkdir -p /home/$user/.composer && chown -R $user:$user /home/$user 

# Set the working directory
WORKDIR /var/www

# Give ownership of the application files to the laravel user
RUN chown -R $user:$user /var/www

# Switch to the laravel user
USER $user