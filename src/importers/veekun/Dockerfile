FROM python:2.7-slim

ENV LANG C.UTF-8

RUN echo "Upgrading distro..." && \
    apt-get update > /dev/null && \
    apt-get upgrade -y > /dev/null && \

    echo "Installing dependencies..." && \
    apt-get install -y git locales > /dev/null && \
    locale-gen en_GB.UTF-8 > /dev/null && \
    apt-get install -y python-distribute > /dev/null && \
    pip install --no-cache-dir virtualenv psycopg2 pymysql > /dev/null && \

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
CMD ["status"]