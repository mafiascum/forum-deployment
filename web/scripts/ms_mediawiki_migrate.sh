if [[ $MAFIASCUM_ENVIRONMENT != 'development' ]] && [[ $MAFIASCUM_ENVIRONMENT != 'dev' ]] && [[ $MAFIASCUM_ENVIRONMENT != 'local' ]]; then
    (cd /opt/bitnami/wiki/maintenance/ && php update.php --quick)
fi