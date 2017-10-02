FROM php:7.1-fpm

ENV LANG C.UTF-8

RUN echo "Upgrading distro..." && \
    apt-get update > /dev/null && \
    apt-get upgrade -y > /dev/null && \

    echo "Installing dependencies..." && \
    apt-get install -y build-essential locales libmcrypt-dev > /dev/null && \
    apt-get install -y git curl libcurl4-gnutls-dev > /dev/null && \
    apt-get install -y sqlite3 libsqlite3-dev mysql-client > /dev/null && \
    # Set default locale
    locale-gen en_GB.UTF-8 > /dev/null && \

    echo "Installing PHP extensions..." && \
    docker-php-ext-install -j$(nproc) curl opcache > /dev/null && \
    docker-php-ext-enable curl opcache > /dev/null && \

    echo "Optimizing space..." && \
    apt-get remove --purge -y software-properties-common > /dev/null && \
    apt-get autoremove -y > /dev/null && \
    apt-get clean > /dev/null && \
    apt-get autoclean > /dev/null && \
    echo -n > /var/lib/apt/extended_states && \
    rm -rf /var/lib/apt/lists/* && \
    rm -rf /usr/share/man/?? && \
    rm -rf /usr/share/man/??_*

COPY ./docker-entrypoint.sh /docker-entrypoint
RUN chmod +x /docker-entrypoint

EXPOSE 9000

ENTRYPOINT ["/docker-entrypoint"]
CMD ["php-fpm"]