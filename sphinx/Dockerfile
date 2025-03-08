FROM debian:bookworm

ENV SPHINX_VERSION=3.7.1-da9f8a4

ENV OS_LOCALE="en_US.UTF-8" \
    OS_LANGUAGE="en_US:en" \
	SPHINX_DATA_DIR=/opt/sphinx/sphinx-3.7.1/data \
	SPHINX_CONF=/opt/sphinx/sphinx-3.7.1/etc/sphinx.conf

RUN apt-get update && apt-get install -y wget cron gettext-base locales libmariadb-dev

COPY ./entrypoint.sh /sbin/entrypoint.sh

RUN mkdir -pv /opt/sphinx

ARG TARGETPLATFORM

RUN echo "$TARGETPLATFORM"

RUN if [ "$TARGETPLATFORM" = "linux/arm64" ]; then wget http://sphinxsearch.com/files/sphinx-${SPHINX_VERSION}-linux-aarch64.tar.gz -O /tmp/sphinxsearch.tar.gz; else wget http://sphinxsearch.com/files/sphinx-${SPHINX_VERSION}-linux-amd64-musl.tar.gz  -O /tmp/sphinxsearch.tar.gz; fi
RUN cd /opt/sphinx && tar -xf /tmp/sphinxsearch.tar.gz 
RUN rm /tmp/sphinxsearch.tar.gz

ENV PATH="${PATH}:/opt/sphinx/sphinx-3.7.1/bin"
RUN indexer -v

RUN rm -rf /var/lib/apt/lists/*
RUN chmod 755 /sbin/entrypoint.sh

RUN mkdir -pv ${SPHINX_DATA_DIR}/logs

RUN ln -sf /dev/stdout ${SPHINX_DATA_DIR}/logs/searchd.log
RUN ln -sf /dev/stdout ${SPHINX_DATA_DIR}/logs/query.log

EXPOSE 9312 9306
VOLUME ["${SPHINX_DATA_DIR}"]

# add scripts
RUN mkdir /opt/mafiascum
COPY scripts /opt/mafiascum/scripts
RUN chmod +x /opt/mafiascum/scripts/*

ENTRYPOINT ["/opt/mafiascum/scripts/entrypoint.sh"]