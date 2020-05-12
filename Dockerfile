FROM webdevops/php-nginx:7.4
ARG APP_ENV=production
ENV APP_ENV "$APP_ENV"
ENV fpm.pool.clear_env no
ENV fpm.pool.pm=ondemand
ENV fpm.pool.pm.max_children=50
ENV fpm.pool.pm.process_idle_timeout=10s
ENV fpm.pool.pm.max_requests=500
ENV COMPOSER_NO_INTERACTION 1

# Install apps and libs
RUN apt-get update && apt-get -y install procps mcedit bsdtar libaio1 musl-dev \
    gettext libpcre3-dev gzip

# Configure services ant tools
COPY .config/nginx/10-location-root.conf /opt/docker/etc/nginx/vhost.common.d/10-location-root.conf
COPY .config/mcedit/mc.keymap /etc/mc/mc.keymap
COPY .config/composer/composer_1.10.6.phar /usr/local/bin/composer

# Create additional /usr/bin/ commands
COPY .config/usr_bin/* /usr/bin/
RUN chmod +x /usr/bin/edit /usr/bin/cs /usr/bin/unit /usr/bin/symfony

# Run APP
COPY --chown=www-data:www-data app /app
WORKDIR /app
#RUN if [ "$APP_ENV" = "development" ]; then composer install; else composer install --no-dev --optimize-autoloader; fi
