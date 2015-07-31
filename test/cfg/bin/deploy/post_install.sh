#!/bin/sh
##
#   Setup Magento instance after update with Composer.
##

# local specific environment
LOCAL_ROOT=${LOCAL_ROOT}
# Owner must be group of web server and
# web server must be in group of owner.
LOCAL_OWNER=${LOCAL_OWNER}
LOCAL_GROUP=${LOCAL_GROUP}
# DB connection params
DB_NAME=${CFG_DB_NAME}
DB_USER=${CFG_DB_USER}
DB_PASS=${CFG_DB_PASS}

# instance independend environment
BIN_ROOT=$LOCAL_ROOT/bin/deploy

##
echo "Change rights to folders and files."
##
mkdir -p $LOCAL_ROOT/mage/var/log
chown $LOCAL_OWNER:$LOCAL_GROUP -R $LOCAL_ROOT/mage/

chmod g+r -R $LOCAL_ROOT/mage/
chmod g+w -R $LOCAL_ROOT/mage/media/
chmod g+w -R $LOCAL_ROOT/mage/var/

find $LOCAL_ROOT/mage/ -type d -exec chmod g+x {} \;

##
# Additional config should be performed in the module/etc/local.xml
##
#sed -i 's/<\/config>/<nmmlm><factory><class>Praxigento_Bonus_Factory<\/class><\/factory><\/nmmlm><\/config>/g' ./mage/app/etc/local.xml


##
echo "Post installation setup for database '$DB_NAME'."
##
mysql --database=$DB_NAME --user=$DB_USER --password=$DB_PASS -e "source $BIN_ROOT/post_install.sql"


##
echo "Create sample data."
##
# php $LOCAL_ROOT/mage/shell/Praxigento/Dcp/test_data_add.php --source $LOCAL_ROOT/mage/shell/Praxigento/Dcp/dcp_customers.csv


##
echo "Post installation setup is done."
##