FROM romeoz/docker-sphinxsearch

RUN apt-get update && apt-get install -y wget cron gettext-base

# add scripts
RUN mkdir /opt/mafiascum
COPY scripts /opt/mafiascum/scripts
RUN chmod +x /opt/mafiascum/scripts/*

ENTRYPOINT ["/opt/mafiascum/scripts/entrypoint.sh"]
