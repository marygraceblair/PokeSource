FROM php:7.1-cli

ENV LANG C.UTF-8

RUN echo "Upgrading distro..." && \
    apt-get update > /dev/null && \
    apt-get upgrade -y > /dev/null && \

    echo "Installing dependencies..." && \
    apt-get install -y build-essential locales libmcrypt-dev > /dev/null && \
    apt-get install -y git curl libcurl4-gnutls-dev libtar libtar-dev zip libzip-dev unzip > /dev/null && \
    apt-get install -y sqlite3 libsqlite3-dev mysql-client > /dev/null && \
    # Set default locale
    locale-gen en_GB.UTF-8 > /dev/null && \

    echo "Installing ImageMagick..." && \
    apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng12-dev grads > /dev/null && \
    apt-get install -y libmagickwand-6.q16-dev --no-install-recommends > /dev/null && \
    ln -s /usr/lib/x86_64-linux-gnu/ImageMagick-6.8.9/bin-Q16/MagickWand-config /usr/bin > /dev/null && \
    pecl install imagick > /dev/null && \
    echo "extension=imagick.so" > /usr/local/etc/php/conf.d/ext-imagick.ini > /dev/null && \

    echo "Installing Python and csvkit..." && \
    apt-get install -y python-dev python-pip python-setuptools build-essential > /dev/null && \
    pip install virtualenv setuptools csvkit > /dev/null && \
    pip install --upgrade setuptools > /dev/null && \
    pip install --upgrade csvkit > /dev/null && \

    echo "Installing PHP extensions..." && \
    docker-php-ext-install -j$(nproc) gd zip curl opcache > /dev/null && \
    docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ > /dev/null && \

    echo "Installing Composer..." && \
    curl -o composer-setup.php https://getcomposer.org/installer > /dev/null && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer > /dev/null && \
    rm composer-setup.php && \

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

ENTRYPOINT ["/docker-entrypoint"]
CMD ["help"]