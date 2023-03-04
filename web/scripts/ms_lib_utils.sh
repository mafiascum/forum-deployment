#!/bin/bash
#
# Shamelessly copied from bitnami.

# Section: Logging 

# Constants
RESET='\033[0m'
RED='\033[38;5;1m'
GREEN='\033[38;5;2m'
YELLOW='\033[38;5;3m'
MAGENTA='\033[38;5;5m'
CYAN='\033[38;5;6m'

# Functions

########################
# Print to STDERR
# Arguments:
#   Message to print
# Returns:
#   None
#########################
stderr_print() {
    # 'is_boolean_yes' is defined in libvalidations.sh, but depends on this file so we cannot source it
    local bool="${BITNAMI_QUIET:-false}"
    # comparison is performed without regard to the case of alphabetic characters
    shopt -s nocasematch
    if ! [[ "$bool" = 1 || "$bool" =~ ^(yes|true)$ ]]; then
        printf "%b\\n" "${*}" >&2
    fi
}

########################
# Log message
# Arguments:
#   Message to log
# Returns:
#   None
#########################
log() {
    stderr_print "${CYAN}${MODULE:-} ${MAGENTA}$(date "+%T.%2N ")${RESET}${*}"
}
########################
# Log an 'info' message
# Arguments:
#   Message to log
# Returns:
#   None
#########################
info() {
    log "${GREEN}INFO ${RESET} ==> ${*}"
}
########################
# Log message
# Arguments:
#   Message to log
# Returns:
#   None
#########################
warn() {
    log "${YELLOW}WARN ${RESET} ==> ${*}"
}
########################
# Log an 'error' message
# Arguments:
#   Message to log
# Returns:
#   None
#########################
error() {
    log "${RED}ERROR${RESET} ==> ${*}"
}
########################
# Log a 'debug' message
# Globals:
#   MS_DEBUG
# Arguments:
#   None
# Returns:
#   None
#########################
debug() {
    # 'is_boolean_yes' is defined in libvalidations.sh, but depends on this file so we cannot source it
    local bool="${MS_DEBUG:-false}"
    # comparison is performed without regard to the case of alphabetic characters
    shopt -s nocasematch
    if [[ "$bool" = 1 || "$bool" =~ ^(yes|true)$ ]]; then
        log "${MAGENTA}DEBUG${RESET} ==> ${*}"
    fi
}

########################
# Indent a string
# Arguments:
#   $1 - string
#   $2 - number of indentation characters (default: 4)
#   $3 - indentation character (default: " ")
# Returns:
#   None
#########################
indent() {
    local string="${1:-}"
    local num="${2:?missing num}"
    local char="${3:-" "}"
    # Build the indentation unit string
    local indent_unit=""
    for ((i = 0; i < num; i++)); do
        indent_unit="${indent_unit}${char}"
    done
    # shellcheck disable=SC2001
    # Complex regex, see https://github.com/koalaman/shellcheck/wiki/SC2001#exceptions
    echo "$string" | sed "s/^/${indent_unit}/"
}

# Section: Validations

########################
# Check if the provided argument is an empty string or not defined
# Arguments:
#   $1 - Value to check
# Returns:
#   Boolean
#########################
is_empty_value() {
    local -r val="${1:-}"
    if [[ -z "$val" ]]; then
        true
    else
        false
    fi
}

########################
# Check if the provided argument is a boolean or is the string 'yes/true'
# Arguments:
#   $1 - Value to check
# Returns:
#   Boolean
#########################
is_boolean_yes() {
    local -r bool="${1:-}"
    # comparison is performed without regard to the case of alphabetic characters
    shopt -s nocasematch
    if [[ "$bool" = 1 || "$bool" =~ ^(yes|true)$ ]]; then
        true
    else
        false
    fi
}

# Section: OS

#########################
# Redirects output to /dev/null if debug mode is disabled
# Globals:
#   MS_DEBUG
# Arguments:
#   $@ - Command to execute
# Returns:
#   None
#########################
debug_execute() {
    if is_boolean_yes "${MS_DEBUG:-false}"; then
        "$@"
    else
        "$@" >/dev/null 2>&1
    fi
}

# Section: FS

########################
# Replace a regex-matching string in a file
# Arguments:
#   $1 - filename
#   $2 - match regex
#   $3 - substitute regex
#   $4 - use POSIX regex. Default: true
# Returns:
#   None
#########################
replace_in_file() {
    local filename="${1:?filename is required}"
    local match_regex="${2:?match regex is required}"
    local substitute_regex="${3:?substitute regex is required}"
    local posix_regex=${4:-true}

    local result

    # We should avoid using 'sed in-place' substitutions
    # 1) They are not compatible with files mounted from ConfigMap(s)
    # 2) We found incompatibility issues with Debian10 and "in-place" substitutions
    local -r del=$'\001' # Use a non-printable character as a 'sed' delimiter to avoid issues
    if [[ $posix_regex = true ]]; then
        result="$(sed -E "s${del}${match_regex}${del}${substitute_regex}${del}g" "$filename")"
    else
        result="$(sed "s${del}${match_regex}${del}${substitute_regex}${del}g" "$filename")"
    fi
    echo "$result" > "$filename"
}

########################
# Checks whether a file can be written to or not
# arguments:
#   $1 - file
# returns:
#   boolean
#########################
is_file_writable() {
    local file="${1:?missing file}"
    local dir
    dir="$(dirname "$file")"

    if [[ (-f "$file" && -w "$file") || (! -f "$file" && -d "$dir" && -w "$dir") ]]; then
        true
    else
        false
    fi
}

########################
# Relativize a path
# arguments:
#   $1 - path
#   $2 - base
# returns:
#   None
#########################
relativize() {
    local -r path="${1:?missing path}"
    local -r base="${2:?missing base}"
    pushd "$base" >/dev/null || exit
    realpath -q --no-symlinks --relative-base="$base" "$path" | sed -e 's|^/$|.|' -e 's|^/||'
    popd >/dev/null || exit
}


########################
# Checks whether a directory is empty or not
# arguments:
#   $1 - directory
# returns:
#   boolean
#########################
is_dir_empty() {
    local -r path="${1:?missing directory}"
    # Calculate real path in order to avoid issues with symlinks
    local -r dir="$(realpath "$path")"
    if [[ ! -e "$dir" ]] || [[ -z "$(ls -A "$dir")" ]]; then
        true
    else
        false
    fi
}

########################
# Checks whether a mounted directory is empty or not
# arguments:
#   $1 - directory
# returns:
#   boolean
#########################
is_mounted_dir_empty() {
    local dir="${1:?missing directory}"

    if is_dir_empty "$dir" || find "$dir" -mindepth 1 -maxdepth 1 -not -name ".snapshot" -not -name "lost+found" -exec false {} +; then
        true
    else
        false
    fi
}

ensure_dir_exists() {
    local dir="${1:?directory is missing}"
    local owner_user="${2:-}"
    local owner_group="${3:-}"

    mkdir -p "${dir}"
    if [[ -n $owner_user ]]; then
        owned_by "$dir" "$owner_user" "$owner_group"
    fi
}

# Section: PHP

php_conf_set() {
    local -r key="${1:?key missing}"
    local -r value="${2:?value missing}"
    local -r file="${3:-"$PHP_CONF_FILE"}"
    local pattern="^[; ]*${key}\s*=.*$"
    if [[ "$key" = "extension" || "$key" = "zend_extension" ]]; then
        # The "extension" property works a bit different for PHP, as there is one per module to be included, meaning it is additive unlike other configurations
        # Because of that, we first check if the extension was defined in the file to replace the proper entry
        pattern="^[; ]*${key}\s*=\s*[\"]?${value}(\.so)?[\"]?\s*$"
    fi
    local -r entry="${key} = ${value}"
    if is_file_writable "$file"; then
        # Not using the ini-file tool since it does not play well with php.ini
        if grep -q -E "$pattern" "$file"; then
            replace_in_file "$file" "$pattern" "$entry"
        else
            cat >> "$file" <<< "$entry"
        fi
    else
        warn "The PHP configuration file '${file}' is not writable. The '${key}' option will not be configured."
    fi
}


########################
# Ensure PHP is initialized
# Globals:
#   PHP_*
# Arguments:
#   None
# Returns:
#   None
#########################
php_initialize() {
    # Configure PHP options based on the runtime environment
    info "Configuring PHP options"
    php_set_runtime_config "$PHP_CONF_FILE"

    # Avoid exit code of previous commands to affect the result of this function
    true
}

php_set_runtime_config() {
    local -r conf_file="${1:?missing conf file}"

    ! is_empty_value "$PHP_DATE_TIMEZONE" && info "Setting PHP date.timezone option" && php_conf_set date.timezone "$PHP_DATE_TIMEZONE" "$conf_file"
    ! is_empty_value "$PHP_ENABLE_OPCACHE" && info "Setting PHP opcache.enable option" && php_conf_set opcache.enable "$PHP_ENABLE_OPCACHE" "$conf_file"
    ! is_empty_value "$PHP_EXPOSE_PHP" && info "Setting PHP expose_php option" && php_conf_set expose_php "$PHP_EXPOSE_PHP" "$conf_file"
    ! is_empty_value "$PHP_MAX_EXECUTION_TIME" && info "Setting PHP max_execution_time option" && php_conf_set max_execution_time "$PHP_MAX_EXECUTION_TIME" "$conf_file"
    ! is_empty_value "$PHP_MAX_INPUT_TIME" && info "Setting PHP max_input_time option" && php_conf_set max_input_time "$PHP_MAX_INPUT_TIME" "$conf_file"
    ! is_empty_value "$PHP_MAX_INPUT_VARS" && info "Setting PHP max_input_vars option" && php_conf_set max_input_vars "$PHP_MAX_INPUT_VARS" "$conf_file"
    ! is_empty_value "$PHP_MEMORY_LIMIT" && info "Setting PHP memory_limit option" && php_conf_set memory_limit "$PHP_MEMORY_LIMIT" "$conf_file"
    ! is_empty_value "$PHP_POST_MAX_SIZE" && info "Setting PHP post_max_size option" && php_conf_set post_max_size "$PHP_POST_MAX_SIZE" "$conf_file"
    ! is_empty_value "$PHP_UPLOAD_MAX_FILESIZE" && info "Setting PHP upload_max_filesize option" && php_conf_set upload_max_filesize "$PHP_UPLOAD_MAX_FILESIZE" "$conf_file"

    true
}

# Section: PHPBB

phpbb_wait_for_db_connection() {
    local -r db_host="${1:?missing database host}"
    local -r db_port="${2:?missing database port}"
    local -r db_name="${3:?missing database name}"
    local -r db_user="${4:?missing database user}"
    local -r db_pass="${5:-}"
    check_mysql_connection() {
        echo "SELECT 1" | mysql_remote_execute "$db_host" "$db_port" "$db_name" "$db_user" "$db_pass"
    }
    if ! retry_while "check_mysql_connection"; then
        error "Could not connect to the database"
        return 1
    fi
}

phpbb_conf_get() {
    local -r key="${1:?key missing}"
    debug "Getting ${key} from phpBB configuration"
    # Sanitize key (sed does not support fixed string substitutions)
    local sanitized_pattern
    sanitized_pattern="^\s*(//\s*)?$(sed 's/[]\[^$.*/]/\\&/g' <<<"$key")\s*=([^;]+);"
    debug "$sanitized_pattern"
    grep -E "$sanitized_pattern" "$PHPBB_CONF_FILE" | sed -E "s|${sanitized_pattern}|\2|" | tr -d "\"' "
}

# Section: MYSQL
########################
# Execute an arbitrary query/queries against the running MySQL/MariaDB service and print to stdout
# Stdin:
#   Query/queries to execute
# Globals:
#   MS_DEBUG
#   DB_*
# Arguments:
#   $1 - Database where to run the queries
#   $2 - User to run queries
#   $3 - Password
#   $4 - Extra MySQL CLI options
# Returns:
#   None
mysql_execute_print_output() {
    local -r db="${1:-}"
    local -r user="${2:-root}"
    local -r pass="${3:-}"
    local -a opts extra_opts
    read -r -a opts <<< "${@:4}"
    read -r -a extra_opts <<< "$(mysql_client_extra_opts)"

    # Process mysql CLI arguments
    local -a args=()
    if [[ -f "$DB_CONF_FILE" ]]; then
        args+=("--defaults-file=${DB_CONF_FILE}")
    fi
    args+=("-N" "-u" "$user" "$db")
    [[ -n "$pass" ]] && args+=("-p$pass")
    [[ "${#opts[@]}" -gt 0 ]] && args+=("${opts[@]}")
    [[ "${#extra_opts[@]}" -gt 0 ]] && args+=("${extra_opts[@]}")

    # Obtain the command specified via stdin
    if [[ "${MS_DEBUG:-false}" = true ]]; then
        local mysql_cmd
        mysql_cmd="$(</dev/stdin)"
        debug "Executing SQL command:\n$mysql_cmd"
        "$DB_BIN_DIR/mysql" "${args[@]}" <<<"$mysql_cmd"
    else
        # Do not store the command(s) as a variable, to avoid issues when importing large files
        # https://github.com/bitnami/bitnami-docker-mariadb/issues/251
        "$DB_BIN_DIR/mysql" "${args[@]}"
    fi
}

########################
# Execute an arbitrary query/queries against the running MySQL/MariaDB service
# Stdin:
#   Query/queries to execute
# Globals:
#   DB_*
# Arguments:
#   $1 - Database where to run the queries
#   $2 - User to run queries
#   $3 - Password
#   $4 - Extra MySQL CLI options
# Returns:
#   None
mysql_execute() {
    debug_execute "mysql_execute_print_output" "$@"
}

########################
# Execute an arbitrary query/queries against a remote MySQL/MariaDB service and print to stdout
# Stdin:
#   Query/queries to execute
# Globals:
#   DB_*
# Arguments:
#   $1 - Remote MySQL/MariaDB service hostname
#   $2 - Remote MySQL/MariaDB service port
#   $3 - Database where to run the queries
#   $4 - User to run queries
#   $5 - Password
#   $6 - Extra MySQL CLI options
# Returns:
#   None
mysql_remote_execute_print_output() {
    local -r hostname="${1:?hostname is required}"
    local -r port="${2:?port is required}"
    local -a args=("-h" "$hostname" "-P" "$port" "--connect-timeout=5")
    # When using "localhost" it would try to connect to the socket, which will not exist for mysql-client
    [[ -n "${MYSQL_CLIENT_FLAVOR:-}" && "$hostname" = "localhost" ]] && args+=("--protocol=tcp")
    shift 2
    "mysql_execute_print_output" "$@" "${args[@]}"
}

########################
# Execute an arbitrary query/queries against a remote MySQL/MariaDB service
# Stdin:
#   Query/queries to execute
# Globals:
#   DB_*
# Arguments:
#   $1 - Remote MySQL/MariaDB service hostname
#   $2 - Remote MySQL/MariaDB service port
#   $3 - Database where to run the queries
#   $4 - User to run queries
#   $5 - Password
#   $6 - Extra MySQL CLI options
# Returns:
#   None
mysql_remote_execute() {
    debug_execute "mysql_remote_execute_print_output" "$@"
}
