#!/usr/bin/env bash
# Provision WSUWP Platform

DOMAIN=`get_primary_host "${VVV_SITE_NAME}".test`
DOMAINS=`get_hosts "${DOMAIN}"`
SITE_TITLE=`get_config_value 'site_title' "${DOMAIN}"`
DB_NAME=`get_config_value 'db_name' "${VVV_SITE_NAME}"`
DB_NAME=${DB_NAME//[\\\/\.\<\>\:\"\'\|\?\!\*-]/}

# Make a database, if we don't already have one
echo -e "\nCreating database '${DB_NAME}' (if it's not already there)"
mysql -u root --password=root -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME}"
mysql -u root --password=root -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO wp@localhost IDENTIFIED BY 'wp';"
echo -e "\n DB operations done.\n\n"

# Nginx Logs
mkdir -p ${VVV_PATH_TO_SITE}/log
touch ${VVV_PATH_TO_SITE}/log/error.log
touch ${VVV_PATH_TO_SITE}/log/access.log

cp -f "${VVV_PATH_TO_SITE}/provision/vvv-nginx.conf.tmpl" "${VVV_PATH_TO_SITE}/provision/vvv-nginx.conf"
sed -i "s#{{DOMAINS_HERE}}#${DOMAINS}#" "${VVV_PATH_TO_SITE}/provision/vvv-nginx.conf"

if [[ ! -f "${VVV_PATH_TO_SITE}/www/wp-config.php" ]]; then
  echo "Copying temporary configuration..."
  noroot wp core config --path=${VVV_PATH_TO_SITE}/www/wordpress/ --dbname="${DB_NAME}" --dbuser=wp --dbpass=wp --quiet --extra-php <<PHP
define( 'WP_DEBUG', true );
PHP
fi

if ! $(noroot wp core --path=${VVV_PATH_TO_SITE}/www/wordpress is-installed); then
  echo "Installing WordPress Stable..."
  noroot wp core multisite-install --path=${VVV_PATH_TO_SITE}/www/wordpress/ --url=wp.wsu.test --title="WSUWP Platform Development" --admin_user="admin" --admin_password="password" --admin_email="admin@local.test" --quiet
fi

# After initial multisite installation, remove the default config file.
if [[ -f "${VVV_PATH_TO_SITE}/www/wordpress/wp-config.php" ]]; then
  rm "${VVV_PATH_TO_SITE}/www/wordpress/wp-config.php"
fi

# Always replace the config file from the provision directory.
cp "${VVV_PATH_TO_SITE}/provision/wp-config.php" "${VVV_PATH_TO_SITE}/www/wp-config.php"

cp -f "${VVV_PATH_TO_SITE}/provision/vvv-nginx.conf.tmpl" "${VVV_PATH_TO_SITE}/provision/vvv-nginx.conf"
sed -i "s#{{DOMAINS_HERE}}#${DOMAINS}#" "${VVV_PATH_TO_SITE}/provision/vvv-nginx.conf"
