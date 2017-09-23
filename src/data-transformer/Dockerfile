FROM php:7.1-cli

ENV LANG C.UTF-8

RUN echo "Upgrading distro..." && \
    apt-get update > /dev/null && \
    apt-get upgrade -y > /dev/null && \

    echo "Installing dependencies..." && \
    apt-get install -y build-essential git curl libcurl4-gnutls-dev locales > /dev/null && \
    apt-get install -y sqlite3 libsqlite3-dev libtar libtar-dev zip libzip-dev unzip > /dev/null && \
    # Set default locale
    locale-gen en_GB.UTF-8 > /dev/null && \

    echo "Installing Python and csvkit..." && \
    apt-get install -y python-dev python-pip python-setuptools build-essential && \
    pip install virtualenv setuptools csvkit && \
    pip install --upgrade setuptools && \
    pip install --upgrade csvkit && \

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