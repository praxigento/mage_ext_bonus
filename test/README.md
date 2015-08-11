# Development and Testing

Development and testing environment is deployed into this location.



## Installation

### Clone repo
Clone repo from GitHub:

    $ git clone git@github.com:praxigento/mage_ext_bonus.git
   

### Create configuration JSON
Create configuration file for current instance:

    $ cd ./mage_ext_bonus/test/
    $ cp templates.json.init templates.json 
    $ nano templates.json
    {
      "vars": {
        "LOCAL_ROOT": "/home/alex/work/github/mage_ext_bonus/test",
        "LOCAL_OWNER": "user",
        "LOCAL_GROUP": "group",
        "CFG_DB_HOST": "localhost",
        "CFG_DB_NAME": "mage_bonus_local",
        "CFG_DB_USER": "mage_bonus_local",
        "CFG_DB_PASS": "JvP2gZDrGkBKVESvSjXe",
        "CFG_DB_PREFIX": "",
        "CFG_LICENSE_AGREEMENT_ACCEPTED": "yes",
        "CFG_LOCALE": "en_US",
        "CFG_TIMEZONE": "America/Los_Angeles",
        "CFG_DEFAULT_CURRENCY": "USD",
        "CFG_URL": "http://bonus.mage.local.prxgt.com:50080/",
        "CFG_USE_REWRITES": "yes",
        "CFG_USE_SECURE": "no",
        "CFG_SECURE_BASE_URL": "",
        "CFG_ADMIN_FRONTNAME": "admin",
        "CFG_USE_SECURE_ADMIN": "no",
        "CFG_ADMIN_LASTNAME": "Admin",
        "CFG_ADMIN_FIRSTNAME": "Store",
        "CFG_ADMIN_EMAIL": "admin@store.com",
        "CFG_ADMIN_USERNAME": "admin",
        "CFG_ADMIN_PASSWORD": "eE5nmsSX0FfVNQG1v5ld",
        "CFG_SKIP_URL_VALIDATION": "yes"
      }
    }


### Create sample data
#### Customers tree
Default downline tree is created on install from file `./mage/shell/Praxigento/Bonus/data_customers.csv`.
Format:

    id,sponsor_id,name_first,name_last,email,group_id
    
Sample:

    1,,Root,User,user1_bonus_test@prxgt.com,1
    2,1,Customer2,User,user2_bonus_test@prxgt.com,1

#### Sales orders
Sales orders are created on install from file `./mage/shell/Praxigento/Bonus/data_orders.csv`.
Format:

    customerMlmId,orderDate,amountTotal,pvTotal
    
Sample:

    100000003,2015/06/01 14:00,10.10,60
    100000006,2015/06/01 14:00,10.10,60

#### PV transfer
PV transfers are created on install from file `./mage/shell/Praxigento/Bonus/data_pv_transfers.csv`.
Format:

    transferDate,fromMlmId,toMlmId,pvTransferred
    
Sample:

    2015/06/01 14:00,100000003,100000006,20


### Install Magento and modules
Startup composer and create module files links into installed Magento file structure (sample data will be created): 
    
    $ composer install
    $ sh ./bin/deploy/post_install.sh
    ...
    Post installation setup is done.


### Web server setup
Setup web server with root directory pointing to `./mage_ext_bonus/test/mage/`. Open new web instance with
browser and compete Magento installation worklfow.
