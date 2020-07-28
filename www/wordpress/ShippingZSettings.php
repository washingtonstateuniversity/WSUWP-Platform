<?php
# (c) 2009-2016 Z-Firm LLC  ALL RIGHTS RESERVED
# FULL COPYRIGHT NOTICE AND TERMS OF USE ARE AT THE BOTTOM OF THIS DOCUMENT.

define("SHIPPINGZSETTINGS_VERSION","4.0.13.7456");

############################################# Please Read These Instructions #######################################
#
#   Your Attention Please !
#
#
#   Please check SHIPPING_ACCESS_TOKEN, below. If it reads CHANGE THIS, please follow these steps.
#     (If it is set to a random value, it has been automatically set by ShipRush. Change it if
#      you understand it needs to be the same here and in the Web Store settings in ShipRush.)
#
#   Step 1: Create & configure a random SHIPPING_ACCESS_TOKEN  Please take these steps:
#     1) Go to http://www.pctools.com/guides/password/     (you can use another random password generator if you like)
#     2) Check ALL the boxes EXCEPT the punctuation box
#     3) Set the LENGTH to 31
#     4) Press the Generate Password button
#     5) Copy the generated password value to the clipboard
#     6) Now, in THIS file (ShippingZSettings.php): 
#        Go to the SHIPPING_ACCESS_TOKEN line below. Paste the random password from step 5 above in over the "CHANGE THIS" -- Note: keep the "quotes"
#        Example: define("SHIPPING_ACCESS_TOKEN","phe6uth3VEch3crutep2unepabupHa2");
#	  7) Save the this token, you will need it later on during the set up process.
#     8) Upload the full kit of files to the root directory of your ecommerce system
#        (This is the root directory of the web store containing 'index.php'). 
#        (Yes, it is OK to omit the files for other ecommerce systems. E.g. a Magento user 
#         can remove the Zencart and Oscommerce files.)
#     8) Continue through the ShipRush wizard. 
#     9) When the ShipRush wizard prompts for the Access Token, enter the token you used in step 4 above.
#     10) Scroll down through the sections below. You will a see a section marked 'Only for <your cart> users'. 
#		You will need to follow the steps in that section.
#
#   NOTE: Some systems require the file permissions of all the ShippingZ files to be 0444. This is read only for everyone.
#		  
# 
############################################## All Users Settings ###################################################

define("SHIPPING_ACCESS_TOKEN","862abf0ea7024f82893e72633c8a1251");  // See steps above to set this -- REQUIRED !

define("OZ_ROUND_UP_THRESHHOLD",".1"); // Weight threshold for shipping


################################################ Only for WooCommerce Users ############################################
#
#  To retrieve statuses in addition to those here, see the section below "Adding New Order Statuses"
#
# Set whether to use Woocommerce REST API or Direct DB Method
define("UseDirectDBMode",'1');// Default is 1, For Woocommerce REST API (WooCommerce 2.1.x) set it to "0" and in that case below Consumer Key & Secret should be set properly
# For Direct DB Method, Consumer Key and Consumer Secret settings are not required

define("WOO_RETRIEVE_ORDER_STATUS_0_ON_HOLD",0);	// Default is 0
define("WOO_RETRIEVE_ORDER_STATUS_1_PENDING",1); 	// Default is 1
define("WOO_RETRIEVE_ORDER_STATUS_2_PROCESSING",1);	// Default is 1
define("WOO_RETRIEVE_ORDER_STATUS_3_COMPLETE",0);	// Default is 0
define("WOO_RETRIEVE_ORDER_STATUS_4_CANCELLED",0);	// Default is 0; set to 1 to retrieve cancelled orders
#
# Short Explanation of below status setting:
# If above status is set to 1, then, when shipped, orders of
# STATUS 1 (Pending), STATUS 2 (Processed) will be
# set to and STATUS 3 (Complete)
define("WOO_SHIPPED_STATUS_SET_TO_STATUS_3_COMPLETE",1);	// Default is 1
#
define("SHIPMENT_TRACKING_MODULE","0"); // Default: 0.  Set to 1 if the Woothemes shipment tracking module is in use (http://www.woothemes.com/products/shipment-tracking/)
#
define("WOO_TRACKING_NOTES_UPDATE_ONLY","0"); // Default: 0.  Set to 1 if you wish to update tracking info or notes only and keep the Order Status as it was
#
#  Begin obsolete Woo section ****** The following is deprecated as of Woo kit versions 95250 and up *******
#
#  Woo Setup Note: For WooCommerce 2.1.x you need to set the Consumer Key and Consumer Secret below.
#  To get these values from your Woocommerce system, please follow these steps:
#  -- Log in to your WP-Admin and enable the REST API from Woocommerce Settings. 
#  -- Then go to your user profile to generate your API keys 
#  -- Copy those values to the settings below, 
#
define("WOO_CONSUMER_KEY",'ck_f7ce003c5186835738b53b663de4010b2df5a13e');
define("WOO_CONSUMER_SECRET",'cs_6d0ba021e4a57cee7487b491eb91eed7c7fe9809');
#
#  End obsolete Woo section ******
#
############################################## END WooCommerce Section ##################################################

#
#
#
#
#
#
#
############################################## System Settings for Tech Support Only ##############################
#
define("HTTP_GET_ENABLED",1);//allow get method
define("GetUnshippedOrdersOnly",0);//set 1 to get unshipped orders only
#
############################################## Adding New Order Statuses #####################################
#
# Say you want the system to retrieve an order status in addition to what is already coded here.
# How?
#
# There are two areas to modify. This settings file, and the php file for your platform.
# Here is an example for OsCommerce (can be used for most other php based systems):
#
# Step 1: Add to this settings file (without the leading # comment symbol):
# define("OSCOMMERCE_RETRIEVE_ORDER_STATUS_4_PAID",1);
#
# Step 2: Modify ShippingZOscommerce.php
#
# Add to this section:
# //Prepare order status string based on settings
# if(OSCOMMERCE_RETRIEVE_ORDER_STATUS_4_PAID==1)   // if set to 1 in Settings
# {
#  if($order_status_filter=="")
#  {
#  $order_status_filter.="orders_status=ZZZ";
#  }
#  else
#  {
#  // The ZZZ is the actual value in the database as the order_status for Paid
#  // For the status you want to retrieve, look in the database to find the real value
#  // and use it in this code
#  $order_status_filter.=" OR orders_status=ZZZ";  
#
#
# CS-Cart: Adding Statuses:
#
# For CS-Cart, additional modification to the ShippingZCscart.php file is needed
# so that the order is marked as complete on the update.
#
# In this example, G is your new order status value. Out of the box, statuses of O, P, and C
# are handled. We will extend the system to handle a status of G for update.
#
# 1: Find this line:
#    $sql = "SELECT COUNT(*) as total_order FROM ?:orders WHERE status in('O','P','C') ?p"; 
#
# For the new status, add it to the list for the "in" clause:
#    $sql = "SELECT COUNT(*) as total_order FROM ?:orders WHERE status in('O','P','C','G') ?p"; 
#
# 2: Further down in the php file, locate this section:
#
#                if($current_order_status=='O'  )
#                    $change_order_status='P';
#                else if($current_order_status=='P')
#                    $change_order_status='C'; 
#
# For the new status, add a new "else if" block:
#
#                if($current_order_status=='O'  )
#                    $change_order_status='P';
#                else if($current_order_status=='P')
#                    $change_order_status='C';                  
#                else if($current_order_status=='G')  
#                    $change_order_status='C'; 
#
# Note: Additional control over the status value can be achieved, but involves further
# customization. Please engage a PHP developer to assist.
#############################################################################################
#
#
#
#********************************************** Shipment Tracking URLs *****************************************************************
#
# Below are for the values saved into certain ecommerce systems. Rarely need to be modified. 
#
define("USPS_URL","http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum=[TRACKING_NUMBER]");
define("UPS_URL","http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=[TRACKING_NUMBER]");
define("FEDEX_URL","http://www.fedex.com/Tracking?action=track&tracknumbers=[TRACKING_NUMBER]");
define("DHL_URL","http://www.dhl.com/content/g0/en/express/tracking.shtml?brand=DHL&AWB=[TRACKING_NUMBER]");
#
#
############################################## Legal Notices ######################################################

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

?>