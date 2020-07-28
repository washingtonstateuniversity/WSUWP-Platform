<?php

define("SHIPPINGZMESSAGES_VERSION","4.0.13.7456");

# ################################################################################
# 	
#  (c) 2010-2014 Z-Firm LLC, ALL RIGHTS RESERVED.
#
#  This file is protected by U.S. and international copyright laws. Technologies and techniques herein are
#  the proprietary methods of Z-Firm LLC. 
#         
#         IMPORTANT
#         =========
#         THIS FILE IS RESTRICTED FOR USE IN CONNECTION WITH SHIPRUSH, MY.SHIPRUSH AND OTHER SOFTWARE 
#         PRODUCTS OWNED BY Z-FIRM LLC.  UNLESS EXPRESSLY PERMITTED BY Z-FIRM, ANY USE IS STRICTLY PROHIBITED.
#
#         THIS FILE, AND ALL PARTS OF SHIPRUSH_SHOPPINGCART_INTEGRATION_KIT__SEE_README_FILE.ZIP AND 
#         THE MY.SHIPRUSH KIT, ARE GOVERNED BY THE MY.SHIPRUSH TERMS OF SERVICE & END USER LICENSE AGREEMENT.
#         
#         The ShipRush License Agreement can be read here: http://www.zfirm.com/SHIPRUSH-EULA
#         
#         If you do not agree with these terms, this file and related files must be deleted immediately.
#
#         Thank you for using ShipRush!
# 	
################################################################################

##################################### Define output messages##############################################################
//Do not change the following section,leave it as it is 
###########################################################################################################################

######################################################## Generic Section #############################################
//Required for all shopping carts
###########################################################################################################################

define("TOKEN_ERROR_MSG","Authentication failed. Please open file \"ShippingZSettings.php\" and set the SHIPPING_ACCESS_TOKEN to a random string of letters and numbers,at least 12 characters long.");
define("URL_TOKEN_ERROR_MSG","Shipping plugin connection successful! Token required for additional access.");
define("DB_ERROR_MSG","Shipping module cannot access database.");
define("DB_SUCCESS_MSG","Success. Access token database access verified.");
define("INVALID_CMD","Invalid command. Allowed commands are: ping, getserverinfo, getordersbydate, getordercountbydate, updateshippinginfo.");
define("INVAID_DATE_ERROR_MSG","Invalid date format. \"DateFrom\" and \"DateTo\" parameters should be in ISO 8601 format. YYYY-MM-DDThh:mm:ssZ.");
define("INVAID_PAGING_ERROR_MSG","Invalid limit or offset");
define("INVAID_TRACKING_NUMBER_MSG","URL parameter TrackingNumber not found.");
define("NO_ORDER_ERROR_MSG","There is no pending orders in this period.");
define("INVAID_ORDER_NUMBER_ERROR_MSG","Order not found.");
define("INVAID_COMMODITY_NUMBER_ERROR_MSG","Commodity not found.");
define("STOCK_UPDATE_NOT_SUPPPORTED_MSG","Stock update not supported.");
define("MISSING_ORDER_NUMBER_ERROR_MSG","URL parameter OrderNumber not found.");
define("MISSING_COMMODITY_ERROR_MSG","URL parameter ExternalId not found.");
define("INVAID_ITEM_NUMBER_ERROR_MSG", "Item Number not found");
######################################################## Magento Cart Section #############################################
//Required for only for Magento Cart
###########################################################################################################################
define("MAGENTO_TEMPORARY_ERROR_MSG", "Can not access Magento API. Please check the URL and access token. The URL must match the configuration of the Magento system. If this problem persists, please review the Magento setup steps.");
define("MAGENTO_API_NOT_SET_ERROR_MSG","Please open the file \"ShippingZSettings.php\" and set WebsiteUrl, Magento_Username and Magento_Password. Notes for creating API user and required Role: 1. Go to admin section of Magento

2. Go to System>Web Services>Users

   -Click on \"Add new user\"

   -Fill up required fields like User name (say ShippingUser), First name, Api key (say ShippingKey123) etc

   -Then click on \"Save user\" button

3. Go to System>Web Services>Roles

   -Click on \"Add new role\"

   -Enter Role name (say ShippingRole)

   -Then click on \"Save Role\" button

  - After Role successfully is saved, click on \"Role Resources\" (present at the left panel).

  - Select \"All\" option from the \"Resource Access\" drop down.

  -Click on \"Save Role\" button

4. Now again go to System>Web Services>Users

   -User list will appear

  -Click on the username (which you have just created, say ShippingUser)

  -Then from the left panel, click on User Role

  -Role list will appear

  -Select the Role (which you have just created, say ShippingRole)

  -Then click on \"Save user\" button.

This api user name and key should be used for shipping module.");//for magento cart only
define("MAGENTO_WRONG_STORE_URL_ERROR_MSG","Please, make sure that you use right URL. Url is case sensitive");//for magento cart only
define("MAGENTO_WRONG_API_DETAILS_ERROR_MSG","Wrong Api credentials.Please open the file \"ShippingZSettings.php\" and set Magento_Username and Magento_Password properly. Notes for creating API user and required Role: 1. Go to admin section of Magento

2. Go to System>Web Services>Users

   -Click on \"Add new user\"

   -Fill up required fields like User name (say ShippingUser), First name, Api key (say ShippingKey123) etc

   -Then click on \"Save user\" button

3. Go to System>Web Services>Roles

   -Click on \"Add new role\"

   -Enter Role name (say ShippingRole)

   -Then click on \"Save Role\" button

  - After Role successfully is saved, click on \"Role Resources\" (present at the left panel).

  - Select \"All\" option from the \"Resource Access\" drop down.

  -Click on \"Save Role\" button

4. Now again go to System>Web Services>Users

   -User list will appear

  -Click on the username (which you have just created, say ShippingUser)

  -Then from the left panel, click on User Role

  -Role list will appear

  -Select the Role (which you have just created, say ShippingRole)

  -Then click on \"Save user\" button.

This api user name and key should be used for shipping module.");//for magento cart only

define("cMagento141Problem","It appears you are running Magento 1.4.1.0. This version has a broken soap.php file. Please refer to http://www.magentocommerce.com/boards/viewthread/195876/ for details");

define("cMagentoSOAPPermissionError","It appears that Magento Api user does not have proper permissions");
define("cMagentoCurlSSLError","Curl Error: SSL Certificate Problem.Verify that the CA cert is OK.");
###########################################################################################################################
?>