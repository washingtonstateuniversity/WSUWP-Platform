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

if [[ ! -d ${VVV_PATH_TO_SITE}/www/wp-content/themes/spine ]]; then
	noroot wp theme install --path=${VVV_PATH_TO_SITE}/www/wordpress/ https://github.com/washingtonstateuniversity/WSUWP-spine-parent-theme/archive/master.zip
	mv ${VVV_PATH_TO_SITE}/www/wp-content/themes/WSUWP-spine-parent-theme ${VVV_PATH_TO_SITE}/www/wp-content/themes/spine
	noroot wp theme enable spine --network --path=${VVV_PATH_TO_SITE}/www/wordpress
	noroot wp theme activate spine --url=wp.wsu.test --path=${VVV_PATH_TO_SITE}
fi

rm -rf WSUWP-MU-Plugin-Collection-master
wget -P ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins https://github.com/washingtonstateuniversity/WSUWP-MU-Plugin-Collection/archive/master.zip
unzip ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/master.zip

rm -rf ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/bp-multi-network
rm -rf ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/cavalcade
rm -rf ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/cavalcade-runner
rm -rf ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/rest-filter

mv WSUWP-MU-Plugin-Collection-master/* ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/
rm -rf WSUWP-MU-Plugin-Collection-master
rm -rf ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/master.zip

if [[ ! -d "${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-mu-new-site-defaults/.git" ]]; then
	noroot wp plugin install --path=${VVV_PATH_TO_SITE}/www/wordpress/ https://github.com/washingtonstateuniversity/WSUWP-Plugin-MU-New-Site-Defaults/archive/master.zip
	rm -rf ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-mu-new-site-defaults
	mv ${VVV_PATH_TO_SITE}/www/wp-content/plugins/WSUWP-Plugin-MU-New-Site-Defaults ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-mu-new-site-defaults
fi

if [[ ! -d "${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-mu-simple-filters/.git" ]]; then
	noroot wp plugin install --path=${VVV_PATH_TO_SITE}/www/wordpress/ https://github.com/washingtonstateuniversity/WSUWP-Plugin-MU-Simple-Filters/archive/master.zip
	rm -rf ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-mu-simple-filters
	mv ${VVV_PATH_TO_SITE}/www/wp-content/plugins/WSUWP-Plugin-MU-Simple-Filters ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-mu-simple-filters
fi

if [[ ! -d "${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-multiple-networks/.git" ]]; then
	noroot wp plugin install --path=${VVV_PATH_TO_SITE}/www/wordpress/ https://github.com/washingtonstateuniversity/WSUWP-Plugin-Multiple-Networks/archive/master.zip
	rm -rf ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-multiple-networks
	mv ${VVV_PATH_TO_SITE}/www/wp-content/plugins/WSUWP-Plugin-Multiple-Networks ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-multiple-networks
fi

if [[ ! -d "${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-mu-extended-batcache/.git" ]]; then
	noroot wp plugin install --path=${VVV_PATH_TO_SITE}/www/wordpress/ https://github.com/washingtonstateuniversity/WSUWP-Plugin-MU-Extended-Batcache/archive/master.zip
	rm -rf ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-mu-extended-batcache
	mv ${VVV_PATH_TO_SITE}/www/wp-content/plugins/WSUWP-Plugin-MU-Extended-Batcache ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-mu-extended-batcache
fi

if [[ ! -d "${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-wordpress-dashboard/.git" ]]; then
	noroot wp plugin install --path=${VVV_PATH_TO_SITE}/www/wordpress/ https://github.com/washingtonstateuniversity/WSUWP-Plugin-WSUWP-Dashboard/archive/master.zip
	rm -rf ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-wordpress-dashboard
	mv ${VVV_PATH_TO_SITE}/www/wp-content/plugins/WSUWP-Plugin-WSUWP-Dashboard ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-wordpress-dashboard
fi

if [[ ! -d "${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-load-mu-plugins/.git" ]]; then
	noroot wp plugin install --path=${VVV_PATH_TO_SITE}/www/wordpress/ https://github.com/washingtonstateuniversity/WSUWP-Plugin-Load-MU-Plugins/archive/master.zip
	rm -rf ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-load-mu-plugins
	mv ${VVV_PATH_TO_SITE}/www/wp-content/plugins/WSUWP-Plugin-Load-MU-Plugins ${VVV_PATH_TO_SITE}/www/wp-content/mu-plugins/wsuwp-load-mu-plugins
fi
