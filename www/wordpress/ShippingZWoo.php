<?php

define("SHIPPINGZWOO_VERSION","4.0.13.7456");

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

// Last mod to this file: $Change: 136072 $

// Function for checking Include Files
function Check_Include_File($filename)
{
	if(file_exists($filename))
	{
		return true;
	}
	else
	{
		echo "\"$filename\" is not accessible.";
		exit;
	}

}

// Check for ShippingZ integration files
if(Check_Include_File("ShippingZSettings.php"))
include("ShippingZSettings.php");
if(Check_Include_File("ShippingZClasses.php"))
include("ShippingZClasses.php");
if(Check_Include_File("ShippingZMessages.php"))
include("ShippingZMessages.php");
// Check Request method, script ONLY executes if the desired http verbs are used
if($_SERVER['REQUEST_METHOD']!="GET" && $_SERVER['REQUEST_METHOD']!="POST")
exit;


// TEST that all the files are same version
if(!(SHIPPINGZCLASSES_VERSION==SHIPPINGZWOO_VERSION && SHIPPINGZWOO_VERSION==SHIPPINGZMESSAGES_VERSION))
{
	echo "Connection to ShipRush plugin successful!<br><br>
	Issue identified: File version mismatch.<br><br>";
	echo "ShippingZClasses.php [".SHIPPINGZCLASSES_VERSION."]<br>";
	echo "ShippingZWoo.php [".SHIPPINGZWOO_VERSION."]<br>";
	echo "ShippingZMessages.php [".SHIPPINGZMESSAGES_VERSION."]<br><br>";
	echo "Please update the kit on this server so the file versions are all the same.<br><br>Thank you!";
	exit;
}

############################################## Always Enable Exception Handler ###############################################
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_error_handler("ShippingZ_Exception_Error_Handler");

//include required wp file
if(Check_Include_File("wp-load.php"))
include("wp-load.php");

//Check Permalink structure
$port_number="";
$updated_host="";
if(strpos(DB_HOST, ':') !== false) {
  $split_host=explode(":",DB_HOST);
  $updated_host=$split_host[0];
  $port_number=$split_host[1];
} 

if($port_number!="")
$db_pdo=new PDO("mysql:host=".$updated_host.";port=".$port_number.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
else
$db_pdo=new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);

global $wpdb,$product_weight_unit;
											


try
  {	
		$sql_weight_unit= "Select option_value from ".$wpdb->prefix."options where option_name = :option_name";
		$weight_unit_res = $db_pdo->prepare($sql_weight_unit);
		$data=array(':option_name' => "woocommerce_weight_unit");
		$weight_unit_res->execute($data);
		foreach($weight_unit_res as $weight_unit_attr)
		{
			$product_weight_unit=$weight_unit_attr['option_value'];																		
		}
  }
  
catch(Exception $e)
   {
 		echo "Error #ZF41618 : Weight unit is missing";
		exit;
   }

################################################ Function convert_dim_unit #######################
//converts dim unit to desired units
function convert_dim_unit($from_unit)
{
	
	$from_unit=trim($from_unit);
	$converted_val_unit=strtoupper($from_unit);
	
	if($from_unit=='cm' || $from_unit=='in')
	{
		
		$converted_val_unit=strtoupper($from_unit);
	}
	else if($from_unit=='m' )
	{
		$converted_val_unit="CM";
	}
	else 
	{
	
		$converted_val_unit="Unknown";
	}

	return $converted_val_unit;
}
################################################ Function convert_dim_value #######################
//Converts dim value to desired units
function convert_dim_value($val,$from_unit)
{
	
	$from_unit=trim($from_unit);
	$converted_val=$val;
	if($val>0)
	{	
		if($from_unit=='cm' || $from_unit=='in')
		{
			
			$converted_val=$val;
		}
		else if($from_unit=='m' )
		{
			$converted_val=$val*100;
		}
		else 
		{
		
			$converted_val=$val;
		}
	}
	return $converted_val;
}
########################################### Function get_sequential_order_id ##############################
function get_sequential_order_id($OrderNumber)
{
	global $wpdb;
	
	//Check if sequential order number plugin is installed
	 $meta_response = $wpdb->get_results( 
	  $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta where  post_id= %s and meta_key='_order_number'", $OrderNumber)
	 );
	 if( count($meta_response) > 0 )
	 {
		  foreach ($meta_response as $meta)
		  {
			$OrderNumber=$meta->meta_value;
			return $OrderNumber;
		  }
	 }
	 else
	 return $OrderNumber;

}
########################################### Function get_actual_order_id ##############################
function get_actual_order_id($OrderNumber)
{
	global $wpdb;
	
	//Check if sequential order number plugin is installed
	 $meta_response = $wpdb->get_results( 
	  $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta where meta_value = %s and meta_key='_order_number'", $OrderNumber)
	 );
	 if( count($meta_response) > 0 )
	 {
		  foreach ($meta_response as $meta)
		  {
			$OrderNumber=$meta->post_id;
			return $OrderNumber;
		  }
	 }
	 else
	 return $OrderNumber;

}
########################################### Function get_actual_order_id ##############################
function get_actual_post_id($OrderNumber)
{
	global $wpdb;
	
	//Check if sequential order number plugin is installed
	 $meta_response = $wpdb->get_results( 
	  $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta where post_id = %s and meta_key='_order_number'", $OrderNumber)
	 );
	
	 if( count($meta_response) > 0 )
	 {
		  foreach ($meta_response as $meta)
		  {
			$OrderNumber=$meta->meta_value;
			return $OrderNumber;
		  }
	 }
	 else
	 return $OrderNumber;

}
################################################ Function check_woo_version ###########################
//checks if woocommerce version is 2.1 or higher or lower
#######################################################################################################
function check_woo_version() 
{
	
	global $woocommerce;
	
	if(!isset($woocommerce))
	$woocommerce=new Woocommerce;
	
	if(version_compare( $woocommerce->version, '2.1', ">=" ) ) {
	  return true;
	}
	return false;
}
########################################################################################################
//check which mode to use
if(!defined("UseDirectDBMode"))
define("UseDirectDBMode", "1");
########################################################################################################
if(check_woo_version() && !UseDirectDBMode)
{
		//Use API methods
		define("MaxCount",50);
		
		global $wpdb;
		
		$sql= "Select * from ".$wpdb->prefix."options where option_name = :option_name and option_value=:option_value";
		$permalink_res = $db_pdo->prepare($sql);
		$data=array(':option_name' => "permalink_structure",':option_value'=>"");
		$permalink_res->execute($data);																		
		if($permalink_res->rowCount()==1 && check_woo_version())
		{
			echo "Error #ZF41105 : It seems you are using default WP Permalink Settings...Please, set this to Post name and save.";
			exit;
		}
		############################################## Class ShippingZWoo ######################################
		class ShippingZWoo extends ShippingZGenericShoppingCart
		{
			
			//cart specific functions goes here
			######################################## Function EXECUTE_CURL ######################################
			function EXECUTE_API_COMMAND($method,$api_params=array(),$force_POST=0)
			{
				global $woocommerce;
				
				$ch = curl_init();
				
				if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!="off") 
				{
				  curl_setopt( $ch, CURLOPT_USERPWD, WOO_CONSUMER_KEY . ":" . WOO_CONSUMER_SECRET);
				  $api_params['consumer_key'] = WOO_CONSUMER_KEY;
				  $api_params['consumer_secret'] = WOO_CONSUMER_SECRET;
				}
				else
				{
					//Parameters related to Authentication
					$api_params['oauth_consumer_key'] = WOO_CONSUMER_KEY;
					$api_params['oauth_timestamp'] = time();	
					$api_params['oauth_nonce'] = sha1(microtime(true).mt_rand(10000,90000));
					$api_params['oauth_signature_method'] = 'HMAC-SHA256';
					
					/******************** generate auth signature ******************************/
					//build query string as expected by woocommerce
					$query_params_for_sign = array();
					
					//Create a salt	following woocommerce steps
					if(version_compare( $woocommerce->version, '2.4.4', ">=" ) )
					{
					
						$api_params_salt=$api_params;						
						
						foreach($api_params_salt as $key_check=>$val_check)
						{
							if($key_check=="filter[updated_at_max]")
							{
								$api_params_salt['filter[updated_at_mtx]']=$val_check;
								unset($api_params_salt['filter[updated_at_max]']);
							}
						
						}
						
						$api_params_salt=$this->normalize_woo_values( $api_params_salt );
						uksort( $api_params_salt, 'strcmp' );
						
						foreach ( $api_params_salt as $woo_key => $woo_value ) 
						{
							$query_params_for_sign[] = $woo_key . '%3D' . $woo_value; 
						}
						
						
						$woo_base_url=rawurlencode(untrailingslashit(get_woocommerce_api_url( '' )));	
										
					    $api_query_string = implode( '%26', $query_params_for_sign );
						
						if ($force_POST) 
						{
							$woo_salt = 'POST&' .$woo_base_url.rawurlencode("/".$method). '&' . $api_query_string;
						}
						else
						{
							$woo_salt = 'GET&' .$woo_base_url.rawurlencode("/".$method). '&' . $api_query_string;
						}
						$woo_salt=str_replace("updated_at_mtx","updated_at_max",$woo_salt);
					   
						// Generate signature 
						$woo_auth_signature = base64_encode( hash_hmac( 'SHA256', $woo_salt, WOO_CONSUMER_SECRET."&", true ) );
					}
					else
					{
						 if(version_compare( $woocommerce->version, '2.1.7', ">=" ) )
						{
							$api_params_salt=$api_params;
							
							$api_params_salt=$this->normalize_woo_values( $api_params_salt );
							uksort( $api_params_salt, 'strcmp' );
							
							foreach ( $api_params_salt as $woo_key => $woo_value ) 
							{
								$query_params_for_sign[] = $woo_key . '%3D' . $woo_value; 
							}
						}
						else
						{
							$api_params=$this->normalize_woo_values( $api_params );
							uksort( $api_params, 'strcmp' );
							

							foreach ( $api_params as $woo_key => $woo_value ) 
							{
								$query_params_for_sign[] = $woo_key . '%3D' . $woo_value; 
							}
						
						}
					
						$api_query_string = implode( '%26', $query_params_for_sign ); 
						
						if ($force_POST) 
						{
							$woo_salt = 'POST&' .rawurlencode(get_woocommerce_api_url( '' ) .$method). '&' . $api_query_string;
						}
						else
						{
							$woo_salt = 'GET&' .rawurlencode(get_woocommerce_api_url( '' ) .$method). '&' . $api_query_string;
						}
							 
						// Generate signature 
						$woo_signature = hash_hmac('SHA256', $woo_salt, WOO_CONSUMER_SECRET, true);
	
						 
						$woo_auth_signature = base64_encode($woo_signature);
					
					}
					$api_params['oauth_signature'] = $woo_auth_signature;
				  
				}
			
				$api_params_formatted = null;
				if ( is_array( $api_params ) && isset( $api_params )) {
					$api_params_formatted = '?' . http_build_query( $api_params );
				} 
				
				curl_setopt($ch, CURLOPT_URL, get_woocommerce_api_url( '' ).$method. $api_params_formatted);
				curl_setopt($ch, CURLOPT_TIMEOUT, 120);
				curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 120 );
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
				
				if ($force_POST) //Required for update order command
				{
					curl_setopt( $ch, CURLOPT_POST, true );
					curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $api_params ) );
				} 
				 
				$response = curl_exec($ch); 
				
				if($response === false)
				{
					$http_code =curl_getinfo($ch, CURLINFO_HTTP_CODE);
					
					$this->CheckAndOverrideErrorMessage('Curl error: ' . curl_error($ch).'<br>Response Code:'.$http_code);
					
					curl_close($ch);
					
				}
				else
				{
					curl_close($ch);
					
					$response = json_decode(trim($response));
					return $response;
				}
				
			
			}
			###########################  Function to test uri parameter restriction #################################
			function TEST_URL_PARAM($api_params)
			{			
			
				$ch = curl_init();				
				$api_params_formatted = null;
				
				
				if ( is_array( $api_params ) && isset( $api_params )) 
				{
					$param_string=http_build_query($api_params);
				} 
				
				
				curl_setopt($ch, CURLOPT_URL, get_woocommerce_api_url()."?".$param_string);
				curl_setopt($ch, CURLOPT_TIMEOUT, 120);
				curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 120 );
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1090.0 Safari/536.6');
				
				$response = curl_exec($ch); 
				$http_code =curl_getinfo($ch, CURLINFO_HTTP_CODE);
				
				if($http_code!="200")
				{
					if($http_code=="414")
					{	
						$this->SetXmlError(1,"URI parameters restriction observed. HTTP Response Code ".$http_code.".");
					
					}
					else
					{
						$this->SetXmlError(1,"Could not access woocommerce API"." HTTP Response Code ".$http_code.".");
					}
				}
				
			
			}
				
			######################################### Function normalize_parameters() ########################
			//Normalize 
			function normalize_woo_values( $parameters ) 
			{
					global $woocommerce;
					
					$normalized_parameters = array();
			
					foreach ( $parameters as $key => $value ) {
			
						// percent symbols (%) must be double-encoded for higher woocommerce versions
						if(version_compare( $woocommerce->version, '2.1.7', ">=" ) )
						{
						    $key   = str_replace( '%', '%25', rawurlencode( rawurldecode( $key ) ) );
						    $value = str_replace( '%', '%25', rawurlencode( rawurldecode( $value ) ) );
						}
						else
						{
							$key   = rawurlencode( rawurldecode( $key ) ) ;
							$value = rawurlencode( rawurldecode( $value ) ) ;
						}
			
						$normalized_parameters[ $key ] = $value;
					}
			
					return $normalized_parameters;
			}
			############################################ API methods ###########################################
			function get_woo_orders_count() 
			{
				return $this->EXECUTE_API_COMMAND( 'orders/count' );
			}
			function get_woo_orders( $api_params = array() ) 
			{
				return $this->EXECUTE_API_COMMAND( 'orders', $api_params );
			}
			function get_woo_product( $product_id ,$api_params = array()) 
			{
				return $this->EXECUTE_API_COMMAND( 'products/' . $product_id,$api_params );
			}
			function get_woo_order( $order_id,$api_params = array() ) 
			{
				return $this->EXECUTE_API_COMMAND( 'orders/' . $order_id , $api_params); 
			}
			function update_woo_order( $order_id, $api_params = array()) 
			{
				return $this->EXECUTE_API_COMMAND( 'orders/' . $order_id, $api_params,1 );
			}
			############################################## Function Check_DB_Access #################################
			//Check Database access(for Woocommerce everything will be done using API so, we don't need database access.But need to check if API credentials are set properly)
			#######################################################################################################
			
			function Check_DB_Access()
			{
				//Test Uri parameters length
				$params=array();
				for($i=1;$i<41;$i++)
				{
					$index="a".$i;		
					$params[$index]="xy";
					if($i>35)
					{
						$this->TEST_URL_PARAM($params);	
					}
				}
				//Test API access
				$res=$this->get_woo_orders_count();
				if(isset($res->errors))
				{
					$this->SetXmlError(1, $res->errors[0]->message);
				}
				else
				{
					$this->display_msg=DB_SUCCESS_MSG;
				}
				
			}
			
			############################################## Function GetOrderCountByDate #################################
			//Get order count
			#######################################################################################################
			function GetOrderCountByDate($datefrom,$dateto)
			{
					
				//Get order count based on data range
				$order_array_onhold=array();
				$order_array_pending=array();
				$order_array_processing=array();
				$order_array_complete=array();
				$order_array_cancelled=array();
				$onhold_count=0;
				$pending_count=0;
				$processing_count=0;
				$completed_count=0;
				$cancelled_count=0;
				
				if(WOO_RETRIEVE_ORDER_STATUS_0_ON_HOLD==1)
				{
					$order_array_onhold=$this->get_woo_orders( array( 'fields' => 'id','status' => 'on-hold','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					
					if(isset($order_array_onhold->orders))
					$onhold_count=count($order_array_onhold->orders);
					unset($order_array_onhold);
							   
				}
				if(WOO_RETRIEVE_ORDER_STATUS_1_PENDING==1)
				{
					$order_array_pending=$this->get_woo_orders( array( 'fields' => 'id','status' => 'pending','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					if(isset($order_array_pending->orders))
					$pending_count=count($order_array_pending->orders);
					unset($order_array_pending);
							   
				}
				if(WOO_RETRIEVE_ORDER_STATUS_2_PROCESSING==1)
				{
					$order_array_processing=$this->get_woo_orders( array( 'fields' => 'id','status' => 'processing','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					if(isset($order_array_processing->orders))
					$processing_count=count($order_array_processing->orders);
					unset($order_array_processing);
							   
				}
				if(WOO_RETRIEVE_ORDER_STATUS_3_COMPLETE==1)
				{
					$order_array_complete=$this->get_woo_orders( array( 'fields' => 'id','status' => 'completed','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					if(isset($order_array_complete->orders))
					$completed_count=count($order_array_complete->orders);
					unset($order_array_complete);
							   
				}
				if(WOO_RETRIEVE_ORDER_STATUS_4_CANCELLED==1)
				{
					$order_array_cancelled=$this->get_woo_orders( array( 'fields' => 'id','status' => 'cancelled','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					if(isset($order_array_cancelled->orders))
					$cancelled_count=count($order_array_cancelled->orders);
					unset($order_array_cancelled);
							   
				}
				return  ($onhold_count+$pending_count+$processing_count+$completed_count+$cancelled_count);
			
			}
			############################################## Function UpdateShippingInfo #################################
			//Update order status (At this point REST API only allows to update order status)
			#######################################################################################################
			function UpdateShippingInfo($OrderNumber,$TrackingNumber='',$ShipDate='',$ShipmentType='',$Notes='',$Carrier='',$Service='',$ShippingCost='')
			{
					
				global $wpdb;
				
				$OrderNumber=get_actual_order_id($OrderNumber);
				
				$res=$this->get_woo_order($OrderNumber,array( 'fields' => 'status'));
				
				if(isset($res->errors))
				{
					$this->SetXmlError(1, $res->errors[0]->message);
				}
				else
				{
					//update order status and comments using direct method
					$current_order_status=$res->order->status;
					
					if(WOO_SHIPPED_STATUS_SET_TO_STATUS_3_COMPLETE==1)
					{
						$change_order_status="completed";
					}
					else
					{   
						if(strtolower($current_order_status)=="on-hold")
							$change_order_status="pending";
						else if(strtolower($current_order_status)=="pending")
							$change_order_status="processing";
						else if(strtolower($current_order_status)=="processing")
							$change_order_status="completed";
						else
						$change_order_status=$current_order_status;
					}
										
					 if($ShipDate!="")
						$shipped_on=$ShipDate;
					else
						$shipped_on=date("m/d/Y");
						
					if($Carrier!="")
					{
						$original_carrier=$Carrier;
						$Carrier=" via ".$Carrier;
					}
					
					if($Service!="")
					$Service=" [".$Service."]";
					
					$TrackingNumberString="";
					if($TrackingNumber!="")
					$TrackingNumberString=", Tracking number $TrackingNumber";
										
					//prepare $comments & save it
					$comments="Shipped on $shipped_on".$Carrier.$Service.$TrackingNumberString;
					
					$woo_order_data = new WC_Order($OrderNumber);
						
					
					if(!defined("SHIPMENT_TRACKING_MODULE"))
					define("SHIPMENT_TRACKING_MODULE","0");
					
					// Update tracking information
					if(!defined("WC_SHIPMENT_TRACKING_VERSION"))
					define("WC_SHIPMENT_TRACKING_VERSION","1.0");
					if(SHIPMENT_TRACKING_MODULE)
					{
						if ( version_compare( WC_SHIPMENT_TRACKING_VERSION, '1.6.6', '<' ) ) 
						{
							update_post_meta( $OrderNumber, '_tracking_provider', strtolower($original_carrier));
							update_post_meta( $OrderNumber, '_tracking_number', $TrackingNumber );
							update_post_meta( $OrderNumber, '_date_shipped', time() );
						}
						else
						{
							$tracking_items=array();
							
							if(get_post_meta( $OrderNumber, '_wc_shipment_tracking_items', true )!="")								
							$tracking_items = get_post_meta( $OrderNumber, '_wc_shipment_tracking_items', true );
							
							$tracking_item=array();							
							$tracking_item['tracking_provider']        = wc_clean(strtolower($original_carrier));
							$tracking_item['tracking_number']          = wc_clean($TrackingNumber );
							$tracking_item['date_shipped']             = time();
							
							$tracking_items[]=$tracking_item;
							
							delete_post_meta( $OrderNumber, '_wc_shipment_tracking_items' );
							update_post_meta( $OrderNumber, '_wc_shipment_tracking_items', $tracking_items );
																		     
						}
						
					}
									
					if(WOO_TRACKING_NOTES_UPDATE_ONLY)
					{
						$woo_order_data->add_order_note($comments);
					}
					else
					{
						$woo_order_data->update_status($change_order_status, $comments );
					}
					
										
					$this->SetXmlMessageResponse($this->wrap_to_xml('UpdateMessage',"Success")); 
				}
			}
			############################################## Function Fetch_DB_Orders #################################
			//Perform Database query & fetch orders based on date range
			#######################################################################################################
			
			function Fetch_DB_Orders($datefrom,$dateto)
			{
				global $product_weight_unit;
				//Get order count based on data range
				$order_array_onhold=array();
				$order_array_pending=array();
				$order_array_processing=array();
				$order_array_complete=array();
				$order_array_cancelled=array();
				$order_arrays=array();
				
				if(WOO_RETRIEVE_ORDER_STATUS_0_ON_HOLD==1)
				{
					$order_array_onhold_temp=$this->get_woo_orders( array('status' => 'on-hold','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					if(isset($order_array_onhold_temp->orders))
					$order_array_onhold=$order_array_onhold_temp->orders;
							   
				}
				if(WOO_RETRIEVE_ORDER_STATUS_1_PENDING==1)
				{
					$order_array_pending_temp=$this->get_woo_orders( array('status' => 'pending','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					if(isset($order_array_pending_temp->orders))
					$order_array_pending=$order_array_pending_temp->orders;
							   
				}
				if(WOO_RETRIEVE_ORDER_STATUS_2_PROCESSING==1)
				{
					$order_array_processing_temp=$this->get_woo_orders( array('status' => 'processing','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					if(isset($order_array_processing_temp->orders))
					$order_array_processing=$order_array_processing_temp->orders;
					 
				}
				if(WOO_RETRIEVE_ORDER_STATUS_3_COMPLETE==1)
				{
					$order_array_complete_temp=$this->get_woo_orders( array('status' => 'completed','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					if(isset($order_array_complete_temp->orders))
					$order_array_complete=$order_array_complete_temp->orders;
										   
				}
				if(WOO_RETRIEVE_ORDER_STATUS_4_CANCELLED==1)
				{
					$order_array_cancelled_temp=$this->get_woo_orders( array('status' => 'cancelled','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					if(isset($order_array_cancelled_temp->orders))
					$order_array_cancelled=$order_array_cancelled_temp->orders;
												   
				}
				
				$order_arrays=array_merge($order_array_onhold,$order_array_pending,$order_array_processing,$order_array_complete,$order_array_cancelled);
				unset($order_array_onhold);	
				unset($order_array_pending);	
				unset($order_array_processing);	
				unset($order_array_complete);
				unset($order_array_cancelled);
				
				$counter=0;
				$uom_weight="";
				
				foreach($order_arrays as $key=>$orders)
				{
							
						if(isset($orders->order_number))			
						$order_id=trim(str_replace('#', '', $orders->order_number));
													
						//prepare order array
						$this->woo_orders[$counter]=new stdClass();
						$this->woo_orders[$counter]->orderid=$order_id;
						$this->woo_orders[$counter]->order_info['PkgLength']="";
						$this->woo_orders[$counter]->order_info['PkgWidth']="";
						$this->woo_orders[$counter]->order_info['PkgHeight']="";
																																	
						//billing details
						$billing_address=array();
						$billing_address_arr=$orders->billing_address;
										
						$this->woo_orders[$counter]->order_billing["FirstName"]=$this->Check_Field($billing_address_arr,'first_name');
						$this->woo_orders[$counter]->order_billing["LastName"]=$this->Check_Field($billing_address_arr,'last_name');
						$this->woo_orders[$counter]->order_billing["Company"]=$this->Check_Field($billing_address_arr,'company');
						$this->woo_orders[$counter]->order_billing["Address1"]=$this->Check_Field($billing_address_arr,'address_1');
						$this->woo_orders[$counter]->order_billing["Address2"]=$this->Check_Field($billing_address_arr,'address_2');
						$this->woo_orders[$counter]->order_billing["City"]=$this->Check_Field($billing_address_arr,'city');
						$this->woo_orders[$counter]->order_billing["State"]=$this->Check_Field($billing_address_arr,'state');
						$this->woo_orders[$counter]->order_billing["PostalCode"]=$this->Check_Field($billing_address_arr,'postcode');
						$this->woo_orders[$counter]->order_billing["Country"]=$this->Check_Field($billing_address_arr,'country');
						$this->woo_orders[$counter]->order_billing["Phone"]=$this->Check_Field($billing_address_arr,'phone');
						
						//shipping details
						$shipping_address=array();
						$shipping_address_arr=$orders->shipping_address;
									
						$this->woo_orders[$counter]->order_shipping["FirstName"]=$this->Check_Field($shipping_address_arr,'first_name');
						$this->woo_orders[$counter]->order_shipping["LastName"]=$this->Check_Field($shipping_address_arr,'last_name');
						$this->woo_orders[$counter]->order_shipping["Company"]=$this->Check_Field($shipping_address_arr,'company');
						$this->woo_orders[$counter]->order_shipping["Address1"]=$this->Check_Field($shipping_address_arr,'address_1');
						$this->woo_orders[$counter]->order_shipping["Address2"]=$this->Check_Field($shipping_address_arr,'address_2');
						$this->woo_orders[$counter]->order_shipping["City"]=$this->Check_Field($shipping_address_arr,'city');
						$this->woo_orders[$counter]->order_shipping["State"]=$this->Check_Field($shipping_address_arr,'state');
						$this->woo_orders[$counter]->order_shipping["PostalCode"]=$this->Check_Field($shipping_address_arr,'postcode');
						$this->woo_orders[$counter]->order_shipping["Country"]=	$this->Check_Field($shipping_address_arr,'country');
						$this->woo_orders[$counter]->order_shipping["Phone"]=$this->Check_Field($billing_address_arr,'phone');
						$this->woo_orders[$counter]->order_shipping["EMail"]="";
						if(isset($orders->customer->email))
						$this->woo_orders[$counter]->order_shipping["EMail"]=$orders->customer->email;
										
						//order info
						$order_date_actual = new DateTime($orders->created_at);
						$this->woo_orders[$counter]->order_info["OrderDate"]= $order_date_actual->format('Y-m-d\TH:i:00\Z');
						
						
									
						$this->woo_orders[$counter]->order_info["ItemsTotal"]=number_format($orders->subtotal,2,'.','');
						$this->woo_orders[$counter]->order_info["Total"]=number_format($orders->total,2,'.','');
						$this->woo_orders[$counter]->order_info["ItemsTax"]=number_format($orders->total_tax,2,'.','');
							
							
						$this->woo_orders[$counter]->order_info["OrderNumber"]=$order_id;
						$this->woo_orders[$counter]->order_info["PaymentType"]=$this->ConvertPaymentType($orders->payment_details->method_title);
						$this->woo_orders[$counter]->order_info["ShippingChargesPaid"]=number_format($orders->total_shipping,2,'.','');
						$this->woo_orders[$counter]->order_info["ShipMethod"]=$orders->shipping_methods;
						$this->woo_orders[$counter]->order_info["Comments"]=$orders->note;
												
						//get customer account details if available
						$order_data=new WC_Order($order_id);
						$customer_id=$order_data->get_user_id();
						if($customer_id>0)
						{
							$customer = get_userdata($customer_id);
							$this->woo_orders[$counter]->order_info["ExternalID"]=$customer->display_name." ".$this->woo_orders[$counter]->order_info["OrderNumber"] . '-' . $this->woo_orders[$counter]->order_info["OrderDate"];
						}	
						else
						$this->woo_orders[$counter]->order_info["ExternalID"]=$this->woo_orders[$counter]->order_info["OrderNumber"] . '-' . $this->woo_orders[$counter]->order_info["OrderDate"];
			
						if($orders->status!="on-hold" && $orders->status!="pending")
							$this->woo_orders[$counter]->order_info["PaymentStatus"]=2;
						else
							$this->woo_orders[$counter]->order_info["PaymentStatus"]=0;
						
						//Show Order status
						if($orders->status=="completed")
							$this->woo_orders[$counter]->order_info["IsShipped"]=1;
						else
							$this->woo_orders[$counter]->order_info["IsShipped"]=0;
							
						//show if cancelled
						if($orders->status=="cancelled")
							$this->woo_orders[$counter]->order_info["IsCancelled"]=1;
						else
							$this->woo_orders[$counter]->order_info["IsCancelled"]=0;
							
										
						$actual_number_of_products=0;
						$dim_unit="";
						for($i=0;$i<count($orders->line_items);$i++)
						{
						
						$additional_product_arr_temp=$this->get_woo_product( $orders->line_items[$i]->product_id,"");
						$attributes_string="";
						if(isset($additional_product_arr_temp->product))
						{
							$additional_product_arr=$additional_product_arr_temp->product;
							$attributes_arr=$additional_product_arr->attributes;
							
							
												
							foreach($attributes_arr as $key=>$attributes)
							{
								if(isset($attributes->option))
								{
										global $port_number, $updated_host;		
										if($port_number!="")
										$db_pdo=new PDO("mysql:host=".$updated_host.";port=".$port_number.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
										else
										$db_pdo=new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
	
										global $wpdb;
										
										$attribute_name_formatted=str_replace(" ",'-',$attributes->name);
												
										$sql= "Select * from ".$wpdb->prefix."woocommerce_order_itemmeta where order_item_id = :order_item_id and (meta_key=:meta_key || meta_key=:meta_key_formatted)";
										
										$order_attr_res = $db_pdo->prepare($sql);
										
										$data=array(':order_item_id' => $orders->line_items[$i]->id,':meta_key'=>$attributes->name,':meta_key_formatted'=>$attribute_name_formatted);
										
										$order_attr_res->execute($data);
										
										$earlier_version=1;
										
										if($order_attr_res->rowCount()==0)
										{
												$sql= "Select * from ".$wpdb->prefix."woocommerce_order_itemmeta where order_item_id = :order_item_id and meta_key like 'pa_%'";
												$order_attr_res = $db_pdo->prepare($sql);
												$data=array(':order_item_id' => $orders->line_items[$i]->id);
												$order_attr_res->execute($data);
												$earlier_version=0;
										}
										
										foreach( $order_attr_res as $order_attr )
										{
											$attr_label="";
											if($earlier_version==0 && strstr($order_attr['meta_key'],'pa_'))
											{
												$attr_label=substr($order_attr['meta_key'],3);
											}
											else
											$attr_label=$order_attr['meta_key'];
											
											$attr_label=str_replace("-"," ",$attr_label);
											
											if($attributes_string!="")
											$attributes_string=$attributes_string.",".$attr_label.":".$order_attr['meta_value'];
											else
											$attributes_string="~".$attr_label.":".$order_attr['meta_value'];
										}
								
									if($earlier_version==0)
									break;
								}
							  }
						}
						
						if($orders->line_items[$actual_number_of_products]->name!="")
						{	
							if(isset($additional_product_arr->dimensions))
							{
								if($additional_product_arr->dimensions->length!="")
								{
								    $dim_unit=convert_dim_unit($additional_product_arr->dimensions->unit);
									
									if($additional_product_arr->dimensions->length>0)
									{
									 							  
									    $this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemLength']=number_format($additional_product_arr->dimensions->length,2,'.','');
									    if($this->woo_orders[$counter]->order_info['PkgLength']=="")
								        $this->woo_orders[$counter]->order_info['PkgLength']=number_format($additional_product_arr->dimensions->length,2,'.','');
									}
									if($additional_product_arr->dimensions->width>0)
									{
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemWidth']=number_format($additional_product_arr->dimensions->width,2,'.','');						if($this->woo_orders[$counter]->order_info['PkgWidth']=="")
								        $this->woo_orders[$counter]->order_info['PkgWidth']=number_format($additional_product_arr->dimensions->width,2,'.','');	
									}
									if($additional_product_arr->dimensions->height>0)
									{
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemHeight']=number_format($additional_product_arr->dimensions->height,2,'.','');
										if($this->woo_orders[$counter]->order_info['PkgHeight']=="")
								        $this->woo_orders[$counter]->order_info['PkgHeight']=number_format($additional_product_arr->dimensions->height,2,'.','');	
									}
								}
							}	
											
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Name"]=$orders->line_items[$i]->name.$attributes_string;
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Price"]=number_format($orders->line_items[$i]->price,2,'.','');
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["ExternalID"]=$orders->line_items[$i]->sku;
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Quantity"]=number_format($orders->line_items[$i]->quantity,2,'.','');
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total"]=number_format(($orders->line_items[$i]->price*$orders->line_items[$i]->quantity),2,'.','');
							
							$product_weight="";
							if(isset($additional_product_arr->weight))
							$product_weight=$additional_product_arr->weight;
							
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["WeightUOM"]=$this->ConvertWeightUOM($product_weight_unit);
										
							$total_weight_with_unit=$this->ConvertToAcceptedUnitWeight(($product_weight*$orders->line_items[$i]->quantity),strtolower($product_weight_unit));
							$total_weight_with_unit=explode("~",$total_weight_with_unit);
							if($product_weight!="")
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["IndividualProductWeight"]=number_format($product_weight*$orders->line_items[$i]->quantity,2,'.','');
							
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total_Product_Weight"]=0;
							if(is_numeric($total_weight_with_unit[0]))
							{
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total_Product_Weight"]=number_format($total_weight_with_unit[0],2,'.','');					
								$uom_weight=$total_weight_with_unit[1];
							}
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Notes"]="";
							
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["SequenceNumberWithinOrder"]=$actual_number_of_products+1;
							$actual_number_of_products++;						
						}
						
					  }
					  
					$this->woo_orders[$counter]->num_of_products=$actual_number_of_products;
					$this->woo_orders[$counter]->order_info["UnitsOfMeasureWeight"]=$uom_weight;
					if($dim_unit!="")
					$this->woo_orders[$counter]->order_info["UnitsOfMeasureLinear"]=$dim_unit;
					
					$counter++;
				}
				
			}
			
			################################### Function GetOrdersByDate($datefrom,$dateto) ######################
			//Get orders based on date range
			#######################################################################################################
			function GetOrdersByDate($datefrom,$dateto)
			{
					
					$this->Fetch_DB_Orders($this->DateFrom,$this->DateTo);
					
		
					if (isset($this->woo_orders))
						return $this->woo_orders;
					else
									return array();  
					
			}
			############################################## Function GetOrderByNumber #################################
			//Perform Database query & fetch sigle order by order number
			##########################################################################################################			
			function GetOrderByNumber($OrderNumber)
			{
					global $product_weight_unit;
												
					$counter=0;
					$uom_weight="";
					
					$order_arrays=array();
					$OrderNumber=get_actual_order_id($OrderNumber);
					$order_arrays=$this->get_woo_order($OrderNumber);					
					
					if(isset($order_arrays->errors))
					{
						$this->display_msg=INVAID_ORDER_NUMBER_ERROR_MSG;
						$this->SetXmlError(1,$this->display_msg);
						exit;
					}
					foreach($order_arrays as $key=>$orders)
					{
						if(isset($orders->order_number))			
						$order_id=trim(str_replace('#', '', $orders->order_number));
													
						//prepare order array
						$this->woo_orders[$counter]=new stdClass();
						$this->woo_orders[$counter]->orderid=$order_id;
						$this->woo_orders[$counter]->order_info['PkgLength']="";
						$this->woo_orders[$counter]->order_info['PkgWidth']="";
						$this->woo_orders[$counter]->order_info['PkgHeight']="";
																																	
						//billing details
						$billing_address=array();
						$billing_address_arr=$orders->billing_address;
										
						$this->woo_orders[$counter]->order_billing["FirstName"]=$this->Check_Field($billing_address_arr,'first_name');
						$this->woo_orders[$counter]->order_billing["LastName"]=$this->Check_Field($billing_address_arr,'last_name');
						$this->woo_orders[$counter]->order_billing["Company"]=$this->Check_Field($billing_address_arr,'company');
						$this->woo_orders[$counter]->order_billing["Address1"]=$this->Check_Field($billing_address_arr,'address_1');
						$this->woo_orders[$counter]->order_billing["Address2"]=$this->Check_Field($billing_address_arr,'address_2');
						$this->woo_orders[$counter]->order_billing["City"]=$this->Check_Field($billing_address_arr,'city');
						$this->woo_orders[$counter]->order_billing["State"]=$this->Check_Field($billing_address_arr,'state');
						$this->woo_orders[$counter]->order_billing["PostalCode"]=$this->Check_Field($billing_address_arr,'postcode');
						$this->woo_orders[$counter]->order_billing["Country"]=$this->Check_Field($billing_address_arr,'country');
						$this->woo_orders[$counter]->order_billing["Phone"]=$this->Check_Field($billing_address_arr,'phone');
						
						//shipping details
						$shipping_address=array();
						$shipping_address_arr=$orders->shipping_address;
									
						$this->woo_orders[$counter]->order_shipping["FirstName"]=$this->Check_Field($shipping_address_arr,'first_name');
						$this->woo_orders[$counter]->order_shipping["LastName"]=$this->Check_Field($shipping_address_arr,'last_name');
						$this->woo_orders[$counter]->order_shipping["Company"]=$this->Check_Field($shipping_address_arr,'company');
						$this->woo_orders[$counter]->order_shipping["Address1"]=$this->Check_Field($shipping_address_arr,'address_1');
						$this->woo_orders[$counter]->order_shipping["Address2"]=$this->Check_Field($shipping_address_arr,'address_2');
						$this->woo_orders[$counter]->order_shipping["City"]=$this->Check_Field($shipping_address_arr,'city');
						$this->woo_orders[$counter]->order_shipping["State"]=$this->Check_Field($shipping_address_arr,'state');
						$this->woo_orders[$counter]->order_shipping["PostalCode"]=$this->Check_Field($shipping_address_arr,'postcode');
						$this->woo_orders[$counter]->order_shipping["Country"]=	$this->Check_Field($shipping_address_arr,'country');
						$this->woo_orders[$counter]->order_shipping["Phone"]=$this->Check_Field($billing_address_arr,'phone');
						$this->woo_orders[$counter]->order_shipping["EMail"]="";
						if(isset($orders->customer->email))
						$this->woo_orders[$counter]->order_shipping["EMail"]=$orders->customer->email;
										
						//order info
						$order_date_actual = new DateTime($orders->created_at);
						$this->woo_orders[$counter]->order_info["OrderDate"]= $order_date_actual->format('Y-m-d\TH:i:00\Z');
						
						
									
						$this->woo_orders[$counter]->order_info["ItemsTotal"]=number_format($orders->subtotal,2,'.','');
						$this->woo_orders[$counter]->order_info["Total"]=number_format($orders->total,2,'.','');
						$this->woo_orders[$counter]->order_info["ItemsTax"]=number_format($orders->total_tax,2,'.','');
							
						$this->woo_orders[$counter]->order_info["OrderNumber"]=$order_id;
						$this->woo_orders[$counter]->order_info["PaymentType"]=$this->ConvertPaymentType($orders->payment_details->method_title);
						$this->woo_orders[$counter]->order_info["ShippingChargesPaid"]=number_format($orders->total_shipping,2,'.','');
						$this->woo_orders[$counter]->order_info["ShipMethod"]=$orders->shipping_methods;
						$this->woo_orders[$counter]->order_info["Comments"]=$orders->note;
												
						//get customer account details if available
						$order_data=new WC_Order($order_id);
						$customer_id=$order_data->get_user_id();
						if($customer_id>0)
						{
							$customer = get_userdata($customer_id);
							$this->woo_orders[$counter]->order_info["ExternalID"]=$customer->display_name." ".$this->woo_orders[$counter]->order_info["OrderNumber"] . '-' . $this->woo_orders[$counter]->order_info["OrderDate"];
						}	
						else
						$this->woo_orders[$counter]->order_info["ExternalID"]=$this->woo_orders[$counter]->order_info["OrderNumber"] . '-' . $this->woo_orders[$counter]->order_info["OrderDate"];
			
						if($orders->status!="on-hold" && $orders->status!="pending")
							$this->woo_orders[$counter]->order_info["PaymentStatus"]=2;
						else
							$this->woo_orders[$counter]->order_info["PaymentStatus"]=0;
						
						//Show Order status
						if($orders->status=="completed")
							$this->woo_orders[$counter]->order_info["IsShipped"]=1;
						else
							$this->woo_orders[$counter]->order_info["IsShipped"]=0;
							
						//show if cancelled
						if($orders->status=="cancelled")
							$this->woo_orders[$counter]->order_info["IsCancelled"]=1;
						else
							$this->woo_orders[$counter]->order_info["IsCancelled"]=0;
							
										
						$actual_number_of_products=0;
						$dim_unit="";
						for($i=0;$i<count($orders->line_items);$i++)
						{
						
						$additional_product_arr_temp=$this->get_woo_product( $orders->line_items[$i]->product_id,"");
						$attributes_string="";
						if(isset($additional_product_arr_temp->product))
						{
							$additional_product_arr=$additional_product_arr_temp->product;
							$attributes_arr=$additional_product_arr->attributes;
							
							
												
							foreach($attributes_arr as $key=>$attributes)
							{
								if(isset($attributes->option))
								{
										global $port_number, $updated_host;		
										if($port_number!="")
										$db_pdo=new PDO("mysql:host=".$updated_host.";port=".$port_number.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
										else
										$db_pdo=new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
	
										global $wpdb;
										
										$attribute_name_formatted=str_replace(" ",'-',$attributes->name);
												
										$sql= "Select * from ".$wpdb->prefix."woocommerce_order_itemmeta where order_item_id = :order_item_id and (meta_key=:meta_key || meta_key=:meta_key_formatted)";
										
										$order_attr_res = $db_pdo->prepare($sql);
										
										$data=array(':order_item_id' => $orders->line_items[$i]->id,':meta_key'=>$attributes->name,':meta_key_formatted'=>$attribute_name_formatted);
										
										$order_attr_res->execute($data);
										
										$earlier_version=1;
										
										if($order_attr_res->rowCount()==0)
										{
												$sql= "Select * from ".$wpdb->prefix."woocommerce_order_itemmeta where order_item_id = :order_item_id and meta_key like 'pa_%'";
												$order_attr_res = $db_pdo->prepare($sql);
												$data=array(':order_item_id' => $orders->line_items[$i]->id);
												$order_attr_res->execute($data);
												$earlier_version=0;
										}
										
										foreach( $order_attr_res as $order_attr )
										{
											$attr_label="";
											if($earlier_version==0 && strstr($order_attr['meta_key'],'pa_'))
											{
												$attr_label=substr($order_attr['meta_key'],3);
											}
											else
											$attr_label=$order_attr['meta_key'];
											
											$attr_label=str_replace("-"," ",$attr_label);
											
											if($attributes_string!="")
											$attributes_string=$attributes_string.",".$attr_label.":".$order_attr['meta_value'];
											else
											$attributes_string="~".$attr_label.":".$order_attr['meta_value'];
										}
								
									if($earlier_version==0)
									break;
								}
							  }
						}
						
						if($orders->line_items[$actual_number_of_products]->name!="")
						{	
							if(isset($additional_product_arr->dimensions))
							{
								if($additional_product_arr->dimensions->length!="")
								{
														
								$dim_unit=convert_dim_unit($additional_product_arr->dimensions->unit);
								
								if($additional_product_arr->dimensions->length>0)
									{
									 							  
									    $this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemLength']=number_format($additional_product_arr->dimensions->length,2,'.','');
									    if($this->woo_orders[$counter]->order_info['PkgLength']=="")
								        $this->woo_orders[$counter]->order_info['PkgLength']=number_format($additional_product_arr->dimensions->length,2,'.','');
									}
									if($additional_product_arr->dimensions->width>0)
									{
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemWidth']=number_format($additional_product_arr->dimensions->width,2,'.','');						if($this->woo_orders[$counter]->order_info['PkgWidth']=="")
								        $this->woo_orders[$counter]->order_info['PkgWidth']=number_format($additional_product_arr->dimensions->width,2,'.','');	
									}
									if($additional_product_arr->dimensions->height>0)
									{
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemHeight']=number_format($additional_product_arr->dimensions->height,2,'.','');
										if($this->woo_orders[$counter]->order_info['PkgHeight']=="")
								        $this->woo_orders[$counter]->order_info['PkgHeight']=number_format($additional_product_arr->dimensions->height,2,'.','');	
									}
								}
							}	
											
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Name"]=$orders->line_items[$i]->name.$attributes_string;
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Price"]=number_format($orders->line_items[$i]->price,2,'.','');
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["ExternalID"]=$orders->line_items[$i]->sku;
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Quantity"]=number_format($orders->line_items[$i]->quantity,2,'.','');
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total"]=number_format(($orders->line_items[$i]->price*$orders->line_items[$i]->quantity),2,'.','');
							
							$product_weight="";
							if(isset($additional_product_arr->weight))
							$product_weight=$additional_product_arr->weight;
							
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["WeightUOM"]=$this->ConvertWeightUOM($product_weight_unit);
							
							$total_weight_with_unit=$this->ConvertToAcceptedUnitWeight(($product_weight*$orders->line_items[$i]->quantity),strtolower($product_weight_unit));
							$total_weight_with_unit=explode("~",$total_weight_with_unit);
							if($product_weight!="")
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["IndividualProductWeight"]=number_format($product_weight*$orders->line_items[$i]->quantity,2,'.','');
							
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total_Product_Weight"]=0;
							if(is_numeric($total_weight_with_unit[0]))
							{
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total_Product_Weight"]=number_format($total_weight_with_unit[0],2,'.','');
								$uom_weight=$total_weight_with_unit[1];
							}
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Notes"]="";
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["SequenceNumberWithinOrder"]=$actual_number_of_products+1;
							$actual_number_of_products++;						
						}
						
					  }
					  
					$this->woo_orders[$counter]->num_of_products=$actual_number_of_products;
					$this->woo_orders[$counter]->order_info["UnitsOfMeasureWeight"]=$uom_weight;
					if($dim_unit!="")
					$this->woo_orders[$counter]->order_info["UnitsOfMeasureLinear"]=$dim_unit;
				}
				
				if (isset($this->woo_orders))
						return $this->woo_orders;
					else
						return array();  
	         }		
			############################################## Function GetHighestOrderNo #################################
			//Get highest order number from woocommerce
			##########################################################################################################			
			function GetHighestOrderNo()
			{
				global $wpdb;
	
				 $post_response = $wpdb->get_results( 
				  $wpdb->prepare("SELECT ID FROM $wpdb->posts where post_type='shop_order' order by ID desc limit 0,1")
				 );
				 
				 if(count($post_response)>0)
				 {
					  foreach ($post_response as $post)
					  {
					  	$post_id=$post->ID;
						$OrderNumber=get_actual_post_id($post_id);
						$this->ReturnHighestOrder($OrderNumber); 
					  }
				 }
				 else
				 $this->SetXmlError(1,"No order found");
			}

			############################################## Function Check_Field #################################
			//Check & Return field value if available
			#######################################################################################################
			function Check_Field($obj,$field)
			{
				if(is_object($obj))
				{
					
					if(null !==($obj->{$field}))
					{
								
						return $obj->{$field};
					}
					else
				   {
						return "";
					}
								
				}
				else
				{
					return "";
				}
				
			}
			function TestStorePlatform($action,$datefrom,$dateto)
			{
					
				global $wpdb,$db_pdo,$woocommerce;
				
				if($action==NULL || strlen($action)<1)
				$action="check_api_setting"; //deafult action, check woo api key/secret/permission
				
				if($action=="check_api_setting")
				{				
					echo "Checking Woocommerce API Settings...<br>";
					$sql = "SELECT * from " . $wpdb->prefix . "woocommerce_api_keys where truncated_key=:truncated_key and consumer_secret=:consumer_secret";
						
					$woo_api_result = $db_pdo->prepare($sql);
					
					$data=array(':truncated_key' => substr(WOO_CONSUMER_KEY,-7) , ':consumer_secret' => WOO_CONSUMER_SECRET);
					
					$woo_api_result->execute($data);
					
					if($woo_api_result->rowCount()>0)
					{
						echo "Consumer key and Consumer Secret are set properly<br>";
						
						foreach( $woo_api_result as $row )
						{
							$permissions=$row['permissions'];
							if($permissions=="read_write")
							{
								echo "API user has proper permissions<br>";
							}
							else 
							echo "API user does not have proper permissions<br> Please, select 'Read/Write' permissions from dropdown of API key details page";
						}
					
					}
					else
					{
						echo "Consumer key and Consumer Secret are not set properly. <br>Please, enable woocommerce REST API, create consumer keys and consumer secrets and enter these values in ShippingZSettings.php";
					}
					
			   }
			   else if($action=="order_statuses") //get available order statuses
			   {
					echo "Fetching woocommerce order statuses...<br>";
					$array = wc_get_order_statuses();
					print_r($array);
			   }
			    else if($action=="simple_call") //perform few simple calls & note execution time
			   {
			   		$ApiCallStartTime = microtime(true);
					
					$pending_count=0;
					
					echo "Retrieve count of processing orders using Woocommerec API...<br>";
					$order_array_pending=array();
$order_array_pending=$this->get_woo_orders( array( 'fields' => 'id','status' => 'processing','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					if(isset($order_array_pending->orders))
					$pending_count=count($order_array_pending->orders);
					unset($order_array_pending);
					echo "Processing order count is ".$pending_count."<br><br>";
					
					echo "Retrieve count of completed orders using Woocommerec API...<br>";
					$order_array_pending=array();
$order_array_pending=$this->get_woo_orders( array( 'fields' => 'id','status' => 'completed','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					if(isset($order_array_pending->orders))
					$pending_count=count($order_array_pending->orders);
					unset($order_array_pending);
					echo "Completed order count is ".$pending_count;
					
					$ApiCallEndTime = microtime(true);
					
					$seconds = $ApiCallEndTime - $ApiCallStartTime;
					
					echo "<br><br>API calls took $seconds seconds to execute.";
			   }
			   else if($action=="complex_call") //perform few complex calls & note execution time
			   {
			   		$order_array_processing=array();
				    $order_array_complete=array();
					
					$ApiCallStartTime = microtime(true);
			   	
					echo "Retrieve count of processing orders using Woocommerec API...<br>";
					$order_array_processing_temp=$this->get_woo_orders( array('status' => 'processing','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					if(isset($order_array_processing_temp->orders))
					$order_array_processing=$order_array_processing_temp->orders;
					print_r($order_array_processing);echo "<br><br>";
				
				    echo "Retrieve completed orders using Woocommerec API...<br>";
					$order_array_complete_temp=$this->get_woo_orders( array('status' => 'completed','filter[limit]'=>MaxCount,'filter[updated_at_min]'=>$datefrom,'filter[updated_at_max]'=>$dateto) );
					if(isset($order_array_complete_temp->orders))
					$order_array_complete=$order_array_complete_temp->orders;
					print_r($order_array_complete);		
					
					$ApiCallEndTime = microtime(true);
					
					$seconds = $ApiCallEndTime - $ApiCallStartTime;
					
					echo "<br><br>API calls took $seconds seconds to execute.";			   
					
			   }
			   else if($action=="get_info") //get wordpres,woocommerce,php version & other information
			   {
							echo 'PHP version: ' . phpversion()."<br>";
							echo "Other Debugging Information:<br>";
							echo "Woocommerce Version:".$woocommerce->version."<br>";
							echo "Wordpress Version: ".get_bloginfo( 'version' )."<br>";
							echo "HOST: ".$_SERVER['HTTP_HOST']."<br>";
							echo "PHP INFO of Server:<br>";
							phpinfo();
							exit;
			   }
			}		
			
		}
}
else
{	

			
			//Use DB integration				
			if($port_number!="")
			$db_pdo=new PDO("mysql:host=".$updated_host.";port=".$port_number.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
			else
			$db_pdo=new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);

		############################################## Class ShippingZWoo ######################################
		class ShippingZWoo extends ShippingZGenericShoppingCart
		{
			
			//cart specific functions goes here
			############################################## Function Check_DB_Access #################################
			//Check Database access
			#######################################################################################################
			function Check_DB_Access()
			{
				global $db_pdo,$wpdb;
						
				$sql= "Select ID from ".$wpdb->prefix."posts order by ID limit 0,1";	
							
				$order_status = $db_pdo->prepare($sql);										
				$order_status->execute();
				
				if ($order_status->rowCount()) 
				{
					$this->display_msg=DB_SUCCESS_MSG;
					
				}
				else
				{
					$this->display_msg=$this->display_msg='Error occurred:'.implode(":",$order_status->errorInfo());
				}
				
			}
			
			############################################## Function GetOrderCountByDate #################################
			//Get order count
			#######################################################################################################
			function GetOrderCountByDate($datefrom,$dateto)
			{
				
				global $db_pdo,$wpdb;
						
				//Get order count based on data range
				$order_status_filter=$this->PrepareWooOrderStatusFilter();
				if(check_woo_version())
				{
					$sql = "SELECT DISTINCT " . $wpdb->prefix . "posts.ID AS orders FROM " . $wpdb->prefix . "posts where ". $wpdb->prefix."posts.post_type='shop_order' AND " . $order_status_filter."  DATE_FORMAT(" . $wpdb->prefix . "posts.post_modified, '%Y-%m-%d %T') >= :datefrom  AND DATE_FORMAT(" . $wpdb->prefix . "posts.post_modified, '%Y-%m-%d %T') <= :dateto";
				}
				else
				{
					$sql = "SELECT DISTINCT " . $wpdb->prefix . "postmeta.post_id AS orders FROM " . $wpdb->prefix . "postmeta LEFT JOIN " . $wpdb->prefix . "posts ON (" . $wpdb->prefix . "postmeta.post_id = " . $wpdb->prefix . "posts.ID) LEFT JOIN " . $wpdb->prefix . "term_relationships ON (" . $wpdb->prefix . "posts.ID = " . $wpdb->prefix . "term_relationships.object_id) LEFT JOIN " . $wpdb->prefix . "term_taxonomy ON (" . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $wpdb->prefix . "term_taxonomy.term_taxonomy_id) LEFT JOIN " . $wpdb->prefix . "terms ON (" . $wpdb->prefix . "term_taxonomy.term_id = " . $wpdb->prefix . "terms.term_id) WHERE " . $wpdb->prefix . "posts.post_status = 'publish' AND " . $wpdb->prefix . "term_taxonomy.taxonomy = 'shop_order_status' AND " . $order_status_filter."  DATE_FORMAT(" . $wpdb->prefix . "posts.post_modified, '%Y-%m-%d %T') >= :datefrom  AND DATE_FORMAT(" . $wpdb->prefix . "posts.post_modified, '%Y-%m-%d %T') <= :dateto";
				}
						
				
				$orders = $db_pdo->prepare($sql);
				
				$data=array(':datefrom' => $this->ConvertDateToDbFormat($datefrom) , ':dateto' => $this->ConvertDateToDbFormat($dateto));
				
				$orders->execute($data);
				
				return $orders->rowCount();
			
			}
			############################################## Function UpdateShippingInfo #################################
			//Update order status 
			#######################################################################################################
			function UpdateShippingInfo($OrderNumber,$TrackingNumber='',$ShipDate='',$ShipmentType='',$Notes='',$Carrier='',$Service='',$ShippingCost='')
			{
				global $db_pdo,$wpdb;
					
				$OrderNumber=get_actual_order_id($OrderNumber);
				if(check_woo_version())
				{
					$sql = "SELECT  " . $wpdb->prefix . "posts.post_status AS order_status FROM " . $wpdb->prefix . "posts where ". $wpdb->prefix."posts.post_type='shop_order' and ".$wpdb->prefix . "posts.ID=:order_id";
				}
				else
				{
					$sql = "SELECT *," . $wpdb->prefix . "terms.name as order_status, ".$wpdb->prefix . "terms.term_id as order_status_id FROM " . $wpdb->prefix . "postmeta LEFT JOIN " . $wpdb->prefix . "posts ON (" . $wpdb->prefix . "postmeta.post_id = " . $wpdb->prefix . "posts.ID) LEFT JOIN " . $wpdb->prefix . "term_relationships ON (" . $wpdb->prefix . "posts.ID = " . $wpdb->prefix . "term_relationships.object_id) LEFT JOIN " . $wpdb->prefix . "term_taxonomy ON (" . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $wpdb->prefix . "term_taxonomy.term_taxonomy_id) LEFT JOIN " . $wpdb->prefix . "terms ON (" . $wpdb->prefix . "term_taxonomy.term_id = " . $wpdb->prefix . "terms.term_id) WHERE " . $wpdb->prefix . "posts.post_status = 'publish' AND " . $wpdb->prefix . "term_taxonomy.taxonomy = 'shop_order_status' and ".$wpdb->prefix . "postmeta.post_id=:order_id";
				}
				
				$result = $db_pdo->prepare($sql);
				$result->execute(array(':order_id' => $OrderNumber));
				
				//check if order number is valid
				if($result->rowCount()>0)
				{
				
					if($ShipDate!="")
						$shipped_on=$ShipDate;
					else
						$shipped_on=date("m/d/Y");
						
					if($Carrier!="")
					{
						$original_carrier=$Carrier;
						$Carrier=" via ".$Carrier;
					}
					
					if($Service!="")
					$Service=" [".$Service."]";
					
					$TrackingNumberString="";
					if($TrackingNumber!="")
					$TrackingNumberString=", Tracking number $TrackingNumber";
					
						
					foreach( $result as $order )
					{
						$current_order_status=$order['order_status'];
					}
					
					//prepare $comments & save it
					$comments="Shipped on $shipped_on".$Carrier.$Service.$TrackingNumberString;
					
					$woo_order_data = new WC_Order($OrderNumber);
					
					if(WOO_SHIPPED_STATUS_SET_TO_STATUS_3_COMPLETE==1)
					{
									
						$change_order_status="completed";
					}
					else
					{
						 if(strtolower($current_order_status)=="on-hold")
							$change_order_status="pending";
						else if(strtolower($current_order_status)=="pending")
							$change_order_status="processing";
						else if(strtolower($current_order_status)=="processing")
							$change_order_status="completed";
						else
						$change_order_status=$current_order_status;
											
					}
					
					if(!defined("SHIPMENT_TRACKING_MODULE"))
					define("SHIPMENT_TRACKING_MODULE","0");
					
					// Update tracking information
					if(!defined("WC_SHIPMENT_TRACKING_VERSION"))
					define("WC_SHIPMENT_TRACKING_VERSION","1.0");
					
					
					
					if(SHIPMENT_TRACKING_MODULE)
					{
						if (version_compare( WC_SHIPMENT_TRACKING_VERSION, '1.6.6', '<' ) ) 
						{
							update_post_meta( $OrderNumber, '_tracking_provider', strtolower($original_carrier));
							update_post_meta( $OrderNumber, '_tracking_number', $TrackingNumber );
							update_post_meta( $OrderNumber, '_date_shipped', time() );
						}
						else
						{
						
							$tracking_items=array();
							
							if(get_post_meta( $OrderNumber, '_wc_shipment_tracking_items', true )!="")								
							$tracking_items = get_post_meta( $OrderNumber, '_wc_shipment_tracking_items', true );
							
							$tracking_item=array();														
							$tracking_item['tracking_provider']        = wc_clean(strtolower($original_carrier));
							$tracking_item['tracking_number']          = wc_clean($TrackingNumber );
							$tracking_item['date_shipped']             = time();
							
							$tracking_items[]=$tracking_item;
							
							delete_post_meta( $OrderNumber, '_wc_shipment_tracking_items' );
							update_post_meta( $OrderNumber, '_wc_shipment_tracking_items', $tracking_items );
																		     
						}
						
					}
					
					if(WOO_TRACKING_NOTES_UPDATE_ONLY)
					{
						$woo_order_data->add_order_note($comments);
					}
					else
					{
						
						$woo_order_data->update_status($change_order_status, $comments );
					}
					
					
					
					$this->SetXmlMessageResponse($this->wrap_to_xml('UpdateMessage',"Success"));
				}
				else
				{
					//display error message
					$this->display_msg=INVAID_ORDER_NUMBER_ERROR_MSG;
					$this->SetXmlError(1,$this->display_msg);
				
				} 	
				
			}
			############################################## Function Fetch_DB_Orders #################################
			//Perform Database query & fetch orders based on date range
			#######################################################################################################
			
			function Fetch_DB_Orders($datefrom,$dateto)
			{
				//Get orders based on data range
				global $db_pdo,$wpdb,$product_weight_unit;				
								
				//Get order count based on data range
				$order_status_filter=$this->PrepareWooOrderStatusFilter();
				
				if(check_woo_version())
				{
					$sql = "SELECT DISTINCT " . $wpdb->prefix . "posts.ID AS orders FROM " . $wpdb->prefix . "posts where ". $wpdb->prefix."posts.post_type='shop_order' AND " . $order_status_filter."  DATE_FORMAT(" . $wpdb->prefix . "posts.post_modified, '%Y-%m-%d %T') >= :datefrom  AND DATE_FORMAT(" . $wpdb->prefix . "posts.post_modified, '%Y-%m-%d %T') <= :dateto";
				}
				else
				{	
					$sql = "SELECT DISTINCT " . $wpdb->prefix . "postmeta.post_id AS orders FROM " . $wpdb->prefix . "postmeta LEFT JOIN " . $wpdb->prefix . "posts ON (" . $wpdb->prefix . "postmeta.post_id = " . $wpdb->prefix . "posts.ID) LEFT JOIN " . $wpdb->prefix . "term_relationships ON (" . $wpdb->prefix . "posts.ID = " . $wpdb->prefix . "term_relationships.object_id) LEFT JOIN " . $wpdb->prefix . "term_taxonomy ON (" . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $wpdb->prefix . "term_taxonomy.term_taxonomy_id) LEFT JOIN " . $wpdb->prefix . "terms ON (" . $wpdb->prefix . "term_taxonomy.term_id = " . $wpdb->prefix . "terms.term_id) WHERE " . $wpdb->prefix . "posts.post_status = 'publish' AND " . $wpdb->prefix . "term_taxonomy.taxonomy = 'shop_order_status' AND " . $order_status_filter."  DATE_FORMAT(" . $wpdb->prefix . "posts.post_modified, '%Y-%m-%d %T') >= :datefrom  AND DATE_FORMAT(" . $wpdb->prefix . "posts.post_modified, '%Y-%m-%d %T') <= :dateto";
				}
				
				$orders_result = $db_pdo->prepare($sql);
				
				$data=array(':datefrom' => $this->ConvertDateToDbFormat($datefrom) , ':dateto' => $this->ConvertDateToDbFormat($dateto));

				
				$orders_result->execute($data);
				
				$counter=0;
				$uom_weight="";
				$dim_unit="";
				foreach( $orders_result as $order )
				{
						
						$woo_order_data = new WC_Order($order['orders']);
						if(WC()->version < '2.7.0')
						$order_id=trim(str_replace('#', '', $woo_order_data->get_order_number()));
						else
						$order_id=$this->Check_Field($woo_order_data,'id');
						
						$order_id=get_sequential_order_id($order_id);
						
						$woo_order_products_temp=$woo_order_data->get_items();
											
						if(count($woo_order_products_temp)==0)
						continue;
													
						//prepare order array
						$this->woo_orders[$counter]=new stdClass();
						$this->woo_orders[$counter]->orderid=$order_id;
						$this->woo_orders[$counter]->order_info['PkgLength']="";
						$this->woo_orders[$counter]->order_info['PkgWidth']="";
						$this->woo_orders[$counter]->order_info['PkgHeight']="";
																		
						//billing details
						$this->woo_orders[$counter]->order_billing["FirstName"]=$this->Check_Field($woo_order_data,'billing_first_name');
						$this->woo_orders[$counter]->order_billing["LastName"]=$this->Check_Field($woo_order_data,'billing_last_name');
						$this->woo_orders[$counter]->order_billing["Company"]=$this->Check_Field($woo_order_data,'billing_company');
						$this->woo_orders[$counter]->order_billing["Address1"]=$this->Check_Field($woo_order_data,'billing_address_1');
						$this->woo_orders[$counter]->order_billing["Address2"]=$this->Check_Field($woo_order_data,'billing_address_2');
						$this->woo_orders[$counter]->order_billing["City"]=$this->Check_Field($woo_order_data,'billing_city');
						$this->woo_orders[$counter]->order_billing["State"]=$this->Check_Field($woo_order_data,'billing_state');
						$this->woo_orders[$counter]->order_billing["PostalCode"]=$this->Check_Field($woo_order_data,'billing_postcode');
						$this->woo_orders[$counter]->order_billing["Country"]=$this->Check_Field($woo_order_data,'billing_country');
						$this->woo_orders[$counter]->order_billing["Phone"]=$this->Check_Field($woo_order_data,'billing_phone');
						
						//shipping details
						$this->woo_orders[$counter]->order_shipping["FirstName"]=$this->Check_Field($woo_order_data,'shipping_first_name');
						$this->woo_orders[$counter]->order_shipping["LastName"]=$this->Check_Field($woo_order_data,'shipping_last_name');
						$this->woo_orders[$counter]->order_shipping["Company"]=$this->Check_Field($woo_order_data,'shipping_company');
						$this->woo_orders[$counter]->order_shipping["Address1"]=$this->Check_Field($woo_order_data,'shipping_address_1');
						$this->woo_orders[$counter]->order_shipping["Address2"]=$this->Check_Field($woo_order_data,'shipping_address_2');
						$this->woo_orders[$counter]->order_shipping["City"]=$this->Check_Field($woo_order_data,'shipping_city');
						$this->woo_orders[$counter]->order_shipping["State"]=$this->Check_Field($woo_order_data,'shipping_state');
						$this->woo_orders[$counter]->order_shipping["PostalCode"]=$this->Check_Field($woo_order_data,'shipping_postcode');
						$this->woo_orders[$counter]->order_shipping["Country"]=	$this->Check_Field($woo_order_data,'shipping_country');
						$this->woo_orders[$counter]->order_shipping["Phone"]=$this->Check_Field($woo_order_data,'billing_phone');
						$this->woo_orders[$counter]->order_shipping["EMail"]=$this->Check_Field($woo_order_data,'billing_email');
							
												
						//order info
						$order_date_actual = new DateTime($this->Check_Field($woo_order_data,'order_date'));
						$this->woo_orders[$counter]->order_info["OrderDate"]= $order_date_actual->format('Y-m-d\TH:i:00\Z');
						
						$this->woo_orders[$counter]->order_info["Total"]=number_format($this->Check_Field($woo_order_data,'order_total'),2,'.','');
						$this->woo_orders[$counter]->order_info["ItemsTax"]=number_format($this->Check_Field($woo_order_data,'order_tax'),2,'.','');
							
							
						$this->woo_orders[$counter]->order_info["OrderNumber"]=$order_id;
						$this->woo_orders[$counter]->order_info["PaymentType"]=$this->ConvertPaymentType($this->Check_Field($woo_order_data,'payment_method_title'));
						
						if(check_woo_version())
						{
							$this->woo_orders[$counter]->order_info["ShippingChargesPaid"]=number_format($woo_order_data->calculate_shipping(),2,'.','');
							$this->woo_orders[$counter]->order_info["ShipMethod"]=$woo_order_data->get_shipping_method();
						}
						else
						{
							$this->woo_orders[$counter]->order_info["ShippingChargesPaid"]=number_format($this->Check_Field($woo_order_data,'order_shipping'),2,'.','');
							$this->woo_orders[$counter]->order_info["ShipMethod"]=$this->Check_Field($woo_order_data,'shipping_method_title');
						}
						
						$this->woo_orders[$counter]->order_info["Comments"]=$this->Check_Field($woo_order_data,'customer_note');	
						
						if(check_woo_version())
						$customer_id=$woo_order_data->get_user_id();
						else
						{	$customer_id=0;
							if(isset($woo_order_data->user_id))
							$customer_id=$woo_order_data->user_id;
						}
						if($customer_id>0)
						{
							$customer = get_userdata($customer_id);
							$this->woo_orders[$counter]->order_info["ExternalID"]=$customer->display_name." ".$this->woo_orders[$counter]->order_info["OrderNumber"] . '-' . $this->woo_orders[$counter]->order_info["OrderDate"];
						}	
						else
						$this->woo_orders[$counter]->order_info["ExternalID"]=$this->woo_orders[$counter]->order_info["OrderNumber"] . '-' . $this->woo_orders[$counter]->order_info["OrderDate"];		
			
						$order_status=$this->Check_Field($woo_order_data,'status');
						
						if($order_status!="on-hold" && $order_status!="pending" && $order_status!="wc-on-hold" && $order_status!="wc-pending")
							$this->woo_orders[$counter]->order_info["PaymentStatus"]=2;
						else
							$this->woo_orders[$counter]->order_info["PaymentStatus"]=0;
						
						//Show Order status
						if($order_status=="completed" || $order_status=="wc-completed")
							$this->woo_orders[$counter]->order_info["IsShipped"]=1;
						else
							$this->woo_orders[$counter]->order_info["IsShipped"]=0;
							
						//show if cancelled
						if($order_status=="cancelled" || $order_status=="wc-cancelled")
							$this->woo_orders[$counter]->order_info["IsCancelled"]=1;
						else
							$this->woo_orders[$counter]->order_info["IsCancelled"]=0;
							
								
						$actual_number_of_products=0;
						
						$this->woo_orders[$counter]->order_info["ItemsTotal"]=0;				
						foreach($woo_order_products_temp as $key=>$woo_order_products)
						{
						
						
						if($woo_order_products['name']!="")
						{					
							if(check_woo_version())
							$product_additional_details=$woo_order_data->get_product_from_item($woo_order_products);
							else
							$product_additional_details=get_product($woo_order_products['product_id']);
								
							if(WC()->version < '2.7.0' )
							{
								$item_meta = new WC_Order_Item_Meta( $woo_order_products['item_meta'] );	
								if($item_meta->display( true, true )!="")
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Name"]=$woo_order_products['name']."~". $item_meta->display( true, true );
								else
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Name"]=$woo_order_products['name'];
							}
							else
							{
								$meta_description = wc_display_item_meta( $woo_order_products, array( 'before' => '', 'after' => '', 
'separator' => ', ', 'echo' => false, 'autop' => false ) );
								 if($meta_description!="")
								 $meta_description="~".strip_tags($meta_description);
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Name"]=$woo_order_products['name'].$meta_description;
							}
							
							
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Price"]=0;
							if($woo_order_products['qty']!=0)
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Price"]=number_format((float)$woo_order_products['line_total']/$woo_order_products['qty'],2,'.','');
							
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["ExternalID"]=$product_additional_details->get_sku();
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Quantity"]=number_format($woo_order_products['qty'],2,'.','');
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total"]=number_format((float)$woo_order_products['line_total'],2,'.','');
							
							$this->woo_orders[$counter]->order_info["ItemsTotal"]+=$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total"];
							$product_weight=$product_additional_details->get_weight();
							$dimensions = $product_additional_details->get_dimensions(false);
														
							$length=0;
							$width=0;
							$height=0;
							$unit="";
							
							if(is_array($dimensions))
							{
							   $length=$dimensions['length'];
							   $width=$dimensions['width'];
							   $height=$dimensions['height'];
							   
							   if($length>0)
								{
									$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemLength']=number_format($length,2,'.','');
									if($this->woo_orders[$counter]->order_info['PkgLength']=="")
									$this->woo_orders[$counter]->order_info['PkgLength']=number_format($length,2,'.','');
								}
								if($width>0)
								{
									$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemWidth']=number_format($width,2,'.','');
									if($this->woo_orders[$counter]->order_info['PkgWidth']=="")
									$this->woo_orders[$counter]->order_info['PkgWidth']=number_format($width,2,'.','');
								}
								if($height>0)
								{
									$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemHeight']=number_format($height,2,'.','');
									if($this->woo_orders[$counter]->order_info['PkgHeight']=="")
									$this->woo_orders[$counter]->order_info['PkgHeight']=number_format($height,2,'.','');
								}
								$dim_unit=convert_dim_unit(get_option( 'woocommerce_dimension_unit' ));
							}
							else
							{
								$dimensions=str_replace("&times;","x",$dimensions);
								if($dimensions!="")
								{
									
									$dim_temp=explode(" x ",$dimensions);
									if(count($dim_temp)>1)
									{
										$length=trim($dim_temp[0]);									
										if(isset($dim_temp[2]))
										{
											$width=trim($dim_temp[1]);
											$last_part=trim($dim_temp[2]);
											$last_part_temp=explode(" ",$last_part);
											$height=$last_part_temp[0];
											$unit=$last_part_temp[1];
										}
										else if(isset($dim_temp[1]))
										{
											$last_part=trim($dim_temp[1]);
											$last_part_temp=explode(" ",$last_part);
											$width=$last_part_temp[0];
											$unit=$last_part_temp[1];
										}
									}
									else
									{
										$dim_temp=explode(" ",$dimensions);
										if(count($dim_temp)>1)
										{
											$length=$dim_temp[0];
											if(isset($dim_temp[1]))
											$unit=$dim_temp[1];
										}
									}
									
									if($length>0)
									{
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemLength']=number_format($length,2,'.','');
										if($this->woo_orders[$counter]->order_info['PkgLength']=="")
										$this->woo_orders[$counter]->order_info['PkgLength']=number_format($length,2,'.','');
									}
									if($width>0)
									{
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemWidth']=number_format($width,2,'.','');
										if($this->woo_orders[$counter]->order_info['PkgWidth']=="")
										$this->woo_orders[$counter]->order_info['PkgWidth']=number_format($width,2,'.','');
									}
									if($height>0)
									{
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemHeight']=number_format($height,2,'.','');
										if($this->woo_orders[$counter]->order_info['PkgHeight']=="")
										$this->woo_orders[$counter]->order_info['PkgHeight']=number_format($height,2,'.','');
									}
									
									$dim_unit=convert_dim_unit($unit);
								}
							}	
							if($product_weight!="")
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["IndividualProductWeight"]=number_format($product_weight*$woo_order_products['qty'],2,'.','');
							
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["WeightUOM"]=$this->ConvertWeightUOM($product_weight_unit);
							
							$total_weight_with_unit=$this->ConvertToAcceptedUnitWeight(((float)$product_weight*$woo_order_products['qty']),strtolower($product_weight_unit));
							$total_weight_with_unit=explode("~",$total_weight_with_unit);
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total_Product_Weight"]=0;
							if(is_numeric($total_weight_with_unit[0]))
							{
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total_Product_Weight"]=number_format($total_weight_with_unit[0],2,'.','');					
								$uom_weight=$total_weight_with_unit[1];
							}
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Notes"]="";
							$this->woo_orders[$counter]->order_product[$actual_number_of_products]["SequenceNumberWithinOrder"]=$actual_number_of_products+1;
							$actual_number_of_products++;						
						}
					  }
					  
						// check for checkout module and additional line items for woocommerce version 2.6 or above
					  global $woocommerce;
					  if(version_compare( $woocommerce->version, '2.6', ">=" ) )
					  {
						  $add_ons = get_option( 'wc_checkout_add_ons', array() );
							if(isset($add_ons))
							{
								$order_add_ons = array();
						
								foreach ( $woo_order_data->get_items( 'fee' ) as $fee_id => $fee ) {
						
									if ( empty( $fee['wc_checkout_add_on_id'] ) || ! isset( $add_ons[ $fee['wc_checkout_add_on_id'] ] ) ) {
										continue;
									}
						
									// Check if WC_Checkout_Add_On class exits i.e. lower version of WC-Checkout-Add-Ons
									if (class_exists('WC_Checkout_Add_On')) {
										$add_on = new WC_Checkout_Add_On( $fee['wc_checkout_add_on_id'], $add_ons[ $fee['wc_checkout_add_on_id'] ] );
										$order_add_ons[ $fee['wc_checkout_add_on_id'] ] = array(
											'name'             => $add_on->name,
											'checkout_label'   => $add_on->label,
											'value'            => $fee['wc_checkout_add_on_value'],
											'normalized_value' => maybe_unserialize( $fee['wc_checkout_add_on_label'] ),
											'total'            => $fee['line_total'],
											'total_tax'        => $fee['line_tax'],
											'fee_id'           => $fee_id,
										);
									}
									else
									{
										// WC-Checkout-Add-Ons version 2.0
										$add_on =  SkyVerge\WooCommerce\Checkout_Add_Ons\Add_Ons\Add_On_Factory::get_add_on($fee['wc_checkout_add_on_id'] );
									
										$order_add_ons[ $fee['wc_checkout_add_on_id'] ] = array(
											'name'             => $add_on->get_name(),
											'checkout_label'   => $add_on->get_label(),
											'value'            => $fee['wc_checkout_add_on_value'],
											'normalized_value' => maybe_unserialize( $fee['wc_checkout_add_on_label'] ),
											'total'            => $fee['line_total'],
											'total_tax'        => $fee['line_tax'],
											'fee_id'           => $fee_id,
										);
									}
								}
								if(isset($order_add_ons))
								{
									foreach($order_add_ons as $addon_key=>$addon_val)
									{
									
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Name"]=$addon_val['name'];
								
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Price"]=number_format($addon_val['total'],2,'.','');
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]["ExternalID"]=$addon_val['fee_id'];
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Quantity"]=1;
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total"]=number_format($addon_val['total'],2,'.','');
										$this->woo_orders[$counter]->order_info["ItemsTotal"]+=$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total"];
										$actual_number_of_products++;
									}
								}
							}
						}
					  //end of checkout module
					  
					  //consider discount
					  if(is_numeric($woo_order_data->discount_total))
					  $this->woo_orders[$counter]->order_info["Discount"]=$woo_order_data->discount_total;
					  
					$this->woo_orders[$counter]->num_of_products=$actual_number_of_products;
					$this->woo_orders[$counter]->order_info["UnitsOfMeasureWeight"]=$uom_weight;
					if($dim_unit!="")
					$this->woo_orders[$counter]->order_info["UnitsOfMeasureLinear"]=$dim_unit;	
					$counter++;
				}
			}
			############################################## Function RetrieveCatalogCount #################################
			// Get catalog count
			#######################################################################################################
			function RetrieveCatalogCount()
			{
				global $db_pdo,$wpdb;
				
				$sql = "SELECT * FROM " . $wpdb->prefix ."posts where post_status='publish' && (post_type='product' || post_type='product_variation') ";
				$result = $db_pdo->prepare($sql);
				$result->execute();
			
				return  $result->rowCount();
			}
			############################################## Function Fetch_DB_Catalog #################################
			// Fetch catalog
			#######################################################################################################
			function Fetch_DB_Catalog($limit,$offset)
			{
				global $db_pdo,$wpdb, $product_weight_unit;
				
				$page=($offset/$limit)+1;
				
				$sql = "SELECT * FROM " . $wpdb->prefix ."posts where post_status='publish' && (post_type='product' || post_type='product_variation') limit $offset,$limit";
				$catalog_data = $db_pdo->prepare($sql);
				$catalog_data->execute();
			
				//Extract Path 
				$folder_path=$_SERVER['SCRIPT_NAME'];
				$folder_path_temp=explode("/",$folder_path);
				$actual_file_name=$folder_path_temp[count($folder_path_temp)-1]; 
				
				$isHttps = false;
				if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
					$isHttps = true;
				}
				elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
					$isHttps = true;
				}
				$SITE_PROTOCOL = $isHttps ? 'https' : 'http';
				
				$folder_path=$SITE_PROTOCOL."://".$_SERVER['HTTP_HOST'].str_replace($actual_file_name,"",$folder_path);
				$counter=0; 
				
				foreach ($catalog_data as $catalog) 
				{
					$catalog_product = wc_get_product( $this->GetFieldNumber($catalog,"ID") );
					$this->woo_commodities[$counter]->catalog_commodity["Currency"]=get_woocommerce_currency();
					$this->woo_commodities[$counter]->catalog_commodity["Price"]=$this->FormatNumber($catalog_product->get_price());
					$this->woo_commodities[$counter]->catalog_commodity["CountryOfOrigin"]="";
					$this->woo_commodities[$counter]->catalog_commodity["UnitsOfMeasureLinear"]=convert_dim_unit(get_option( 'woocommerce_dimension_unit' ));
					
					$this->woo_commodities[$counter]->catalog_commodity["UnitsOfMeasureWeight"]="";
					
					$uom_weight="";
					$product_weight_with_unit=$this->ConvertToAcceptedUnitWeight($catalog_product->get_weight(),strtolower($product_weight_unit));
					$product_weight_with_unit=explode("~",$product_weight_with_unit);
					$this->woo_commodities[$counter]->catalog_commodity["Weight"]=0;
					if(is_numeric($product_weight_with_unit[0]))
					{
						$this->woo_commodities[$counter]->catalog_commodity["Weight"]=number_format($product_weight_with_unit[0],2,'.','');					
						$uom_weight=$product_weight_with_unit[1];
					}
					if($uom_weight!="")
					$this->woo_commodities[$counter]->catalog_commodity["UnitsOfMeasureWeight"]=$uom_weight;
					
					$this->woo_commodities[$counter]->catalog_commodity["Quantity"]=$catalog_product->get_stock_quantity();
					$this->woo_commodities[$counter]->catalog_commodity["Volume"]="";
					$this->woo_commodities[$counter]->catalog_commodity["LengthPackaged"]="";
					$this->woo_commodities[$counter]->catalog_commodity["WidthPackaged"]="";
					$this->woo_commodities[$counter]->catalog_commodity["HeightPackaged"]="";
					$this->woo_commodities[$counter]->catalog_commodity["WeightPackaged"]="";
					$this->woo_commodities[$counter]->catalog_commodity["RowIndex"]="";
					$this->woo_commodities[$counter]->catalog_commodity["SKU"]=$catalog_product->get_sku();
					
					$this->woo_commodities[$counter]->catalog_commodity["UPC"]="";
					$this->woo_commodities[$counter]->catalog_commodity["VolumePackaged"]="";
					$this->woo_commodities[$counter]->catalog_commodity["Condition"]="";
					
					$this->woo_commodities[$counter]->catalog_commodity["Length"]=convert_dim_value($catalog_product->get_length(),get_option( 'woocommerce_dimension_unit' ));
					$this->woo_commodities[$counter]->catalog_commodity["Width"]=convert_dim_value($catalog_product->get_width(),get_option( 'woocommerce_dimension_unit' ));
					$this->woo_commodities[$counter]->catalog_commodity["Height"]=convert_dim_value($catalog_product->get_height(),get_option( 'woocommerce_dimension_unit' ));
					
					$this->woo_commodities[$counter]->catalog_commodity["Manufacturer"]="";
					
					$this->woo_commodities[$counter]->catalog_commodity["ImageURL"]=wp_get_attachment_url( $catalog_product->get_image_id() ); 
					$variants="";
					$this->woo_commodities[$counter]->catalog_commodity["ParentPBCommodityId"]="";
					$parent_id="";
					if(WC()->version < '2.7.0' )
					{
						$this->woo_commodities[$counter]->catalog_commodity["Name"]=$catalog_product->get_title();
						$this->woo_commodities[$counter]->catalog_commodity["Description"]=get_post_field('post_content', $this->GetFieldNumber($catalog,"ID"));
						$this->woo_commodities[$counter]->catalog_commodity["ExternalID"]=$this->GetFieldNumber($catalog,"ID");
						$this->woo_commodities[$counter]->catalog_commodity["URL"]=get_permalink( $this->GetFieldNumber($catalog,"ID") );
						$parent_id=get_post_field('post_parent', $this->GetFieldNumber($catalog,"ID"));
						if($parent_id!="")
						{
							$this->woo_commodities[$counter]->catalog_commodity["ParentPBCommodityId"]=$parent_id;
							$attributes = $catalog_product->get_attributes();
							
							foreach ( $attributes as $label_1=>$val_1 )
							{  
							   if(get_post_meta( $this->GetFieldNumber($catalog,"ID"), 'attribute_'.$label_1, true )!="")
							   {	
									if($variants!="") $variants.=",";
									$variants.=$val_1['name'].":".get_post_meta( $this->GetFieldNumber($catalog,"ID"), 'attribute_'.$label_1, true );
								}
							}
						}
					}
					else
					{
						$this->woo_commodities[$counter]->catalog_commodity["Name"]=$catalog_product->get_name();
						$this->woo_commodities[$counter]->catalog_commodity["Description"]=$catalog_product->get_description();
						$this->woo_commodities[$counter]->catalog_commodity["ExternalID"]=$catalog_product->get_id();
						$this->woo_commodities[$counter]->catalog_commodity["URL"]=get_permalink( $catalog_product->get_id() );
						$parent_id=$catalog_product->get_parent_id();
						if($parent_id!="")
						{
							$this->woo_commodities[$counter]->catalog_commodity["ParentPBCommodityId"]=$parent_id;
							$attributes = $catalog_product->get_attributes();
							foreach ( $attributes as $label=>$val )
							{ 
							    if($variants!="") $variants.=",";
								$variants.=$label.":".$val;
							}
						}
					}
					
					$this->woo_commodities[$counter]->catalog_commodity["VariationsNameValues"]=$variants;
					
					$this->woo_commodities[$counter]->catalog_commodity["ManagedByWebStore"]=0;
					$sql_manage_stock = "SELECT * FROM " . $wpdb->prefix ."postmeta WHERE post_id=:externalId and meta_key='_manage_stock' and meta_value='yes'";
					$data_manage_stock=array(':externalId' => $this->GetFieldNumber($catalog,"ID"));
					$result_manage_stock = $db_pdo->prepare($sql_manage_stock);
					$result_manage_stock->execute($data_manage_stock);
					if($result_manage_stock->rowCount()>0)// manage quanity is enabled
					$this->woo_commodities[$counter]->catalog_commodity["ManagedByWebStore"]=1;
					
					$counter++;
					
				}
			}
			################################### Function GetOrdersByDate($datefrom,$dateto) ######################
			//Get orders based on date range
			#######################################################################################################
			function GetOrdersByDate($datefrom,$dateto)
			{
					
					$this->Fetch_DB_Orders($this->DateFrom,$this->DateTo);
					
		
					if (isset($this->woo_orders))
						return $this->woo_orders;
					else
									return array();  
					
			}
			################################################## Function GetCatalog() #############################
		    // Get catalog based on paging
		   #######################################################################################################
		   function GetCatalog()
		   {
				$this->Fetch_DB_Catalog($this->Limit,$this->Offset);
				
				if (isset($this->woo_commodities))
					return $this->woo_commodities;
				else
					return array();  
		   }
		   ############################################### Function UpdateCatalog #################################
			// Update catalog
			#######################################################################################################
			function UpdateCatalog($ExternalID ,$Quantity )
			{
				global $db_pdo, $wpdb;
				
				$sql = "SELECT * FROM " . $wpdb->prefix ."posts WHERE ID=:externalId";
				$data=array(':externalId' => $ExternalID);
				
				$result = $db_pdo->prepare($sql);
				$result->execute($data);
				// check if commodity id is valid
				
				if($result->rowCount()>0)
				{
				
					$sql_manage_stock = "SELECT * FROM " . $wpdb->prefix ."postmeta WHERE post_id=:externalId and meta_key='_manage_stock' and meta_value='yes'";
					$data_manage_stock=array(':externalId' => $ExternalID);
					
					$result_manage_stock = $db_pdo->prepare($sql_manage_stock);
					$result_manage_stock->execute($data_manage_stock);
					
					// check if manage quanity is enabled
					if($result_manage_stock->rowCount()>0)
				    {
						$sql_upd="update " . $wpdb->prefix ."postmeta set meta_value=:quantity where post_id=:externalId and meta_key='_stock'";														
						$data_upd=array( ':quantity' => $Quantity,':externalId' => $ExternalID);
						$result_upd = $db_pdo->prepare($sql_upd);
						$result_upd->execute($data_upd);
						
						if($Quantity>0)
						$stock_status="instock";
						else
						$stock_status="outofstock";
						
						$sql_upd_status="update " . $wpdb->prefix ."postmeta set meta_value=:stock_status where post_id=:externalId and meta_key='_stock_status'";														
						$data_upd_status=array( ':stock_status' => $stock_status,':externalId' => $ExternalID);
						$result_upd_status = $db_pdo->prepare($sql_upd_status);
						$result_upd_status->execute($data_upd_status);
						
						$this->SetXmlMessageResponse($this->wrap_to_xml('UpdateMessage',"Success"));
					}
					else
					{
						//display error message
						$this->display_msg=STOCK_UPDATE_NOT_SUPPPORTED_MSG;
						$this->SetXmlError(1,$this->display_msg);
					}	
					
				}
				else
				{
					//display error message
					$this->display_msg=INVAID_COMMODITY_NUMBER_ERROR_MSG;
					$this->SetXmlError(1,$this->display_msg);
				
				}
			  
			}
			############################################## Function GetOrderByNumber #################################
			//Perform Database query & fetch sigle order by order number
			#######################################################################################################
			function GetOrderByNumber($OrderNumber)
			{
				global $db_pdo,$wpdb,$product_weight_unit;				
								
				$OrderNumber=get_actual_order_id($OrderNumber);
				$counter=0;
				$uom_weight="";
				$dim_unit="";
				//check for order number
				if(check_woo_version())
				{
					$sql = "SELECT  " . $wpdb->prefix . "posts.post_status AS order_status FROM " . $wpdb->prefix . "posts where ". $wpdb->prefix."posts.post_type='shop_order' and ".$wpdb->prefix . "posts.ID=:order_id";
				}
				else
				{
					$sql = "SELECT *," . $wpdb->prefix . "terms.name as order_status, ".$wpdb->prefix . "terms.term_id as order_status_id FROM " . $wpdb->prefix . "postmeta LEFT JOIN " . $wpdb->prefix . "posts ON (" . $wpdb->prefix . "postmeta.post_id = " . $wpdb->prefix . "posts.ID) LEFT JOIN " . $wpdb->prefix . "term_relationships ON (" . $wpdb->prefix . "posts.ID = " . $wpdb->prefix . "term_relationships.object_id) LEFT JOIN " . $wpdb->prefix . "term_taxonomy ON (" . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $wpdb->prefix . "term_taxonomy.term_taxonomy_id) LEFT JOIN " . $wpdb->prefix . "terms ON (" . $wpdb->prefix . "term_taxonomy.term_id = " . $wpdb->prefix . "terms.term_id) WHERE " . $wpdb->prefix . "posts.post_status = 'publish' AND " . $wpdb->prefix . "term_taxonomy.taxonomy = 'shop_order_status' and ".$wpdb->prefix . "postmeta.post_id=:order_id";
				}
				
				$result = $db_pdo->prepare($sql);
				$result->execute(array(':order_id' => $OrderNumber));
				
				//check if order number is valid, oherwise produce order not found error
				if($result->rowCount()>0)
				{ 
	
						try
						{
							$woo_order_data = new WC_Order($OrderNumber);
						}
						catch(Exception $e)
						{
							$this->display_msg=INVAID_ORDER_NUMBER_ERROR_MSG;
							$this->SetXmlError(1,$this->display_msg);
							exit;
						}
						
						if(WC()->version < '2.7.0')
						$order_id=trim(str_replace('#', '', $woo_order_data->get_order_number()));
						else
						$order_id=$this->Check_Field($woo_order_data,'id');
						
						$order_id=get_sequential_order_id($order_id);
						
						$woo_order_products_temp=$woo_order_data->get_items();
											
						if(count($woo_order_products_temp)>0)
						{
													
							//prepare order array
							$this->woo_orders[$counter]=new stdClass();
							$this->woo_orders[$counter]->orderid=$order_id;
							$this->woo_orders[$counter]->order_info['PkgLength']="";
							$this->woo_orders[$counter]->order_info['PkgWidth']="";
							$this->woo_orders[$counter]->order_info['PkgHeight']="";
																			
							//billing details
							$this->woo_orders[$counter]->order_billing["FirstName"]=$this->Check_Field($woo_order_data,'billing_first_name');
							$this->woo_orders[$counter]->order_billing["LastName"]=$this->Check_Field($woo_order_data,'billing_last_name');
							$this->woo_orders[$counter]->order_billing["Company"]=$this->Check_Field($woo_order_data,'billing_company');
							$this->woo_orders[$counter]->order_billing["Address1"]=$this->Check_Field($woo_order_data,'billing_address_1');
							$this->woo_orders[$counter]->order_billing["Address2"]=$this->Check_Field($woo_order_data,'billing_address_2');
							$this->woo_orders[$counter]->order_billing["City"]=$this->Check_Field($woo_order_data,'billing_city');
							$this->woo_orders[$counter]->order_billing["State"]=$this->Check_Field($woo_order_data,'billing_state');
							$this->woo_orders[$counter]->order_billing["PostalCode"]=$this->Check_Field($woo_order_data,'billing_postcode');
							$this->woo_orders[$counter]->order_billing["Country"]=$this->Check_Field($woo_order_data,'billing_country');
							$this->woo_orders[$counter]->order_billing["Phone"]=$this->Check_Field($woo_order_data,'billing_phone');
							
							//shipping details
							$this->woo_orders[$counter]->order_shipping["FirstName"]=$this->Check_Field($woo_order_data,'shipping_first_name');
							$this->woo_orders[$counter]->order_shipping["LastName"]=$this->Check_Field($woo_order_data,'shipping_last_name');
							$this->woo_orders[$counter]->order_shipping["Company"]=$this->Check_Field($woo_order_data,'shipping_company');
							$this->woo_orders[$counter]->order_shipping["Address1"]=$this->Check_Field($woo_order_data,'shipping_address_1');
							$this->woo_orders[$counter]->order_shipping["Address2"]=$this->Check_Field($woo_order_data,'shipping_address_2');
							$this->woo_orders[$counter]->order_shipping["City"]=$this->Check_Field($woo_order_data,'shipping_city');
							$this->woo_orders[$counter]->order_shipping["State"]=$this->Check_Field($woo_order_data,'shipping_state');
							$this->woo_orders[$counter]->order_shipping["PostalCode"]=$this->Check_Field($woo_order_data,'shipping_postcode');
							$this->woo_orders[$counter]->order_shipping["Country"]=	$this->Check_Field($woo_order_data,'shipping_country');
							$this->woo_orders[$counter]->order_shipping["Phone"]=$this->Check_Field($woo_order_data,'billing_phone');
							$this->woo_orders[$counter]->order_shipping["EMail"]=$this->Check_Field($woo_order_data,'billing_email');
								
													
							//order info
							$order_date_actual = new DateTime($this->Check_Field($woo_order_data,'order_date'));
							$this->woo_orders[$counter]->order_info["OrderDate"]= $order_date_actual->format('Y-m-d\TH:i:00\Z');
							
										
							$this->woo_orders[$counter]->order_info["Total"]=number_format($this->Check_Field($woo_order_data,'order_total'),2,'.','');
							$this->woo_orders[$counter]->order_info["ItemsTax"]=number_format($this->Check_Field($woo_order_data,'order_tax'),2,'.','');
								
								
							$this->woo_orders[$counter]->order_info["OrderNumber"]=$order_id;
							$this->woo_orders[$counter]->order_info["PaymentType"]=$this->ConvertPaymentType($this->Check_Field($woo_order_data,'payment_method_title'));
							
							if(check_woo_version())
							{
								$this->woo_orders[$counter]->order_info["ShippingChargesPaid"]=number_format($woo_order_data->calculate_shipping(),2,'.','');
								$this->woo_orders[$counter]->order_info["ShipMethod"]=$woo_order_data->get_shipping_method();
							}
							else
							{
								$this->woo_orders[$counter]->order_info["ShippingChargesPaid"]=number_format($this->Check_Field($woo_order_data,'order_shipping'),2,'.','');
								$this->woo_orders[$counter]->order_info["ShipMethod"]=$this->Check_Field($woo_order_data,'shipping_method_title');
							}
							
							$this->woo_orders[$counter]->order_info["Comments"]=$this->Check_Field($woo_order_data,'customer_note');	
							
							if(check_woo_version())
							$customer_id=$woo_order_data->get_user_id();
							else
							{	$customer_id=0;
								if(isset($woo_order_data->user_id))
								$customer_id=$woo_order_data->user_id;
							}
							if($customer_id>0)
							{
								$customer = get_userdata($customer_id);
								$this->woo_orders[$counter]->order_info["ExternalID"]=$customer->display_name." ".$this->woo_orders[$counter]->order_info["OrderNumber"] . '-' . $this->woo_orders[$counter]->order_info["OrderDate"];
							}	
							else
							$this->woo_orders[$counter]->order_info["ExternalID"]=$this->woo_orders[$counter]->order_info["OrderNumber"] . '-' . $this->woo_orders[$counter]->order_info["OrderDate"];		
						
							$order_status=$this->Check_Field($woo_order_data,'status');
							
							if($order_status!="on-hold" && $order_status!="pending" && $order_status!="wc-on-hold" && $order_status!="wc-pending")
								$this->woo_orders[$counter]->order_info["PaymentStatus"]=2;
							else
								$this->woo_orders[$counter]->order_info["PaymentStatus"]=0;
							
							//Show Order status
							if($order_status=="completed" || $order_status=="wc-completed")
								$this->woo_orders[$counter]->order_info["IsShipped"]=1;
							else
								$this->woo_orders[$counter]->order_info["IsShipped"]=0;
								
							//show if cancelled
							if($order_status=="cancelled" || $order_status=="wc-cancelled")
								$this->woo_orders[$counter]->order_info["IsCancelled"]=1;
							else
								$this->woo_orders[$counter]->order_info["IsCancelled"]=0;
								
									
							$actual_number_of_products=0;
							
							$this->woo_orders[$counter]->order_info["ItemsTotal"]=0;				
							foreach($woo_order_products_temp as $key=>$woo_order_products)
							{
							
							
							if($woo_order_products['name']!="")
							{					
								if(check_woo_version())
								$product_additional_details=$woo_order_data->get_product_from_item($woo_order_products);
								else
								$product_additional_details=get_product($woo_order_products['product_id']);
													
								if(WC()->version < '2.7.0' )
								{
									$item_meta = new WC_Order_Item_Meta( $woo_order_products['item_meta'] );
									if($item_meta->display( true, true )!="")
									$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Name"]=$woo_order_products['name']."~". $item_meta->display( true, true );
									else
									$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Name"]=$woo_order_products['name'];
								}
								else
								{
									$meta_description = wc_display_item_meta( $woo_order_products, array( 'before' => '', 'after' => '', 
'separator' => ', ', 'echo' => false, 'autop' => false ) );
								 if($meta_description!="")
								 $meta_description="~".strip_tags($meta_description);
									$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Name"]=$woo_order_products['name'].$meta_description;
								}
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Price"]=0;
								if($woo_order_products['qty']!=0)
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Price"]=number_format((float)$woo_order_products['line_total']/$woo_order_products['qty'],2,'.','');
								
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["ExternalID"]=$product_additional_details->get_sku();
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Quantity"]=number_format($woo_order_products['qty'],2,'.','');
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total"]=number_format((float)$woo_order_products['line_total'],2,'.','');
								
								$this->woo_orders[$counter]->order_info["ItemsTotal"]+=$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total"];
								$product_weight=$product_additional_details->get_weight();
								
								$dimensions = $product_additional_details->get_dimensions(false);
														
							$length=0;
							$width=0;
							$height=0;
							$unit="";
							
							if(is_array($dimensions))
							{
							   $length=$dimensions['length'];
							   $width=$dimensions['width'];
							   $height=$dimensions['height'];
							   
							   if($length>0)
								{
									$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemLength']=number_format($length,2,'.','');
									if($this->woo_orders[$counter]->order_info['PkgLength']=="")
									$this->woo_orders[$counter]->order_info['PkgLength']=number_format($length,2,'.','');
								}
								if($width>0)
								{
									$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemWidth']=number_format($width,2,'.','');
									if($this->woo_orders[$counter]->order_info['PkgWidth']=="")
									$this->woo_orders[$counter]->order_info['PkgWidth']=number_format($width,2,'.','');
								}
								if($height>0)
								{
									$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemHeight']=number_format($height,2,'.','');
									if($this->woo_orders[$counter]->order_info['PkgHeight']=="")
									$this->woo_orders[$counter]->order_info['PkgHeight']=number_format($height,2,'.','');
								}
								$dim_unit=convert_dim_unit(get_option( 'woocommerce_dimension_unit' ));
							}
							else
							{
								$dimensions=str_replace("&times;","x",$dimensions);
								if($dimensions!="")
								{
									
									$dim_temp=explode(" x ",$dimensions);
									if(count($dim_temp)>1)
									{
										$length=trim($dim_temp[0]);									
										if(isset($dim_temp[2]))
										{
											$width=trim($dim_temp[1]);
											$last_part=trim($dim_temp[2]);
											$last_part_temp=explode(" ",$last_part);
											$height=$last_part_temp[0];
											$unit=$last_part_temp[1];
										}
										else if(isset($dim_temp[1]))
										{
											$last_part=trim($dim_temp[1]);
											$last_part_temp=explode(" ",$last_part);
											$width=$last_part_temp[0];
											$unit=$last_part_temp[1];
										}
									}
									else
									{
										$dim_temp=explode(" ",$dimensions);
										if(count($dim_temp)>1)
										{
											$length=$dim_temp[0];
											if(isset($dim_temp[1]))
											$unit=$dim_temp[1];
										}
									}
									
									if($length>0)
									{
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemLength']=number_format($length,2,'.','');
										if($this->woo_orders[$counter]->order_info['PkgLength']=="")
										$this->woo_orders[$counter]->order_info['PkgLength']=number_format($length,2,'.','');
									}
									if($width>0)
									{
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemWidth']=number_format($width,2,'.','');
										if($this->woo_orders[$counter]->order_info['PkgWidth']=="")
										$this->woo_orders[$counter]->order_info['PkgWidth']=number_format($width,2,'.','');
									}
									if($height>0)
									{
										$this->woo_orders[$counter]->order_product[$actual_number_of_products]['ItemHeight']=number_format($height,2,'.','');
										if($this->woo_orders[$counter]->order_info['PkgHeight']=="")
										$this->woo_orders[$counter]->order_info['PkgHeight']=number_format($height,2,'.','');
									}
									
									$dim_unit=convert_dim_unit($unit);
								}
							}
									
								if($product_weight!="")
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["IndividualProductWeight"]=number_format($product_weight*$woo_order_products['qty'],2,'.','');
								
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["WeightUOM"]=$this->ConvertWeightUOM($product_weight_unit);
								
								$total_weight_with_unit=$this->ConvertToAcceptedUnitWeight(($product_weight*$woo_order_products['qty']),strtolower($product_weight_unit));
								$total_weight_with_unit=explode("~",$total_weight_with_unit);
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total_Product_Weight"]=0;
								if(is_numeric($total_weight_with_unit[0]))
								{
									$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total_Product_Weight"]=number_format($total_weight_with_unit[0],2,'.','');					
									$uom_weight=$total_weight_with_unit[1];
								}
								$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Notes"]="";
								$actual_number_of_products++;	
							  }
						  }
						  
							// check for checkout module and additional line items for woocommerce version 2.6 or above
						  global $woocommerce;
						  if(version_compare( $woocommerce->version, '2.6', ">=" ) )
						  {
							  $add_ons = get_option( 'wc_checkout_add_ons', array() );
								if(isset($add_ons))
								{
									$order_add_ons = array();
							
									foreach ( $woo_order_data->get_items( 'fee' ) as $fee_id => $fee ) {
							
										if ( empty( $fee['wc_checkout_add_on_id'] ) || ! isset( $add_ons[ $fee['wc_checkout_add_on_id'] ] ) ) {
											continue;
										}
							
										// Check if WC_Checkout_Add_On class exits i.e. lower version of WC-Checkout-Add-Ons
										if (class_exists('WC_Checkout_Add_On')) {
											$add_on = new WC_Checkout_Add_On( $fee['wc_checkout_add_on_id'], $add_ons[ $fee['wc_checkout_add_on_id'] ] );
											$order_add_ons[ $fee['wc_checkout_add_on_id'] ] = array(
												'name'             => $add_on->name,
												'checkout_label'   => $add_on->label,
												'value'            => $fee['wc_checkout_add_on_value'],
												'normalized_value' => maybe_unserialize( $fee['wc_checkout_add_on_label'] ),
												'total'            => $fee['line_total'],
												'total_tax'        => $fee['line_tax'],
												'fee_id'           => $fee_id,
											);
										}
										else
										{
											// WC-Checkout-Add-Ons version 2.0
											$add_on =  SkyVerge\WooCommerce\Checkout_Add_Ons\Add_Ons\Add_On_Factory::get_add_on($fee['wc_checkout_add_on_id'] );
										
											$order_add_ons[ $fee['wc_checkout_add_on_id'] ] = array(
												'name'             => $add_on->get_name(),
												'checkout_label'   => $add_on->get_label(),
												'value'            => $fee['wc_checkout_add_on_value'],
												'normalized_value' => maybe_unserialize( $fee['wc_checkout_add_on_label'] ),
												'total'            => $fee['line_total'],
												'total_tax'        => $fee['line_tax'],
												'fee_id'           => $fee_id,
											);
										}
									}
									if(isset($order_add_ons))
									{
										foreach($order_add_ons as $addon_key=>$addon_val)
										{
										
											$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Name"]=$addon_val['name'];
									
											$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Price"]=number_format($addon_val['total'],2,'.','');
											$this->woo_orders[$counter]->order_product[$actual_number_of_products]["ExternalID"]=$addon_val['fee_id'];
											$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Quantity"]=1;
											$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total"]=number_format($addon_val['total'],2,'.','');
											$this->woo_orders[$counter]->order_info["ItemsTotal"]+=$this->woo_orders[$counter]->order_product[$actual_number_of_products]["Total"];
											$actual_number_of_products++;
										}
									}
								}
							}
						  //end of checkout module
						
						if(is_numeric($woo_order_data->discount_total))
					    $this->woo_orders[$counter]->order_info["Discount"]=$woo_order_data->discount_total;
						
						$this->woo_orders[$counter]->num_of_products=$actual_number_of_products;
						$this->woo_orders[$counter]->order_info["UnitsOfMeasureWeight"]=$uom_weight;
						if($dim_unit!="")
						$this->woo_orders[$counter]->order_info["UnitsOfMeasureLinear"]=$dim_unit;	
					}
			  }
		    else
			{
				//display error message
				$this->display_msg=INVAID_ORDER_NUMBER_ERROR_MSG;
				$this->SetXmlError(1,$this->display_msg);
			
			} 
			if (isset($this->woo_orders))
						return $this->woo_orders;
					else
						return array();  
				
			}
			############################################## Function GetHighestOrderNo #################################
			//Get highest order number from woocommerce
			##########################################################################################################			
			function GetHighestOrderNo()
			{
				global $wpdb;
	
				 $post_response = $wpdb->get_results( 
				  $wpdb->prepare("SELECT ID FROM $wpdb->posts where post_type='shop_order' order by ID desc limit 0,1")
				 );
				 
				 if( count($post_response) > 0 )
				 {
					  foreach ($post_response as $post)
					  {
					  	$post_id=$post->ID;
						$OrderNumber=get_actual_post_id($post_id);
						$this->ReturnHighestOrder($OrderNumber); 
					  }
				 }
				 else
				 $this->SetXmlError(1,"No order found");
			}
			############################################## Function Check_Field #################################
			//Check & Return field value if available
			#######################################################################################################
			function Check_Field($obj,$field)
			{
				if(is_object($obj))
				{
					if(WC()->version < '2.7.0' )
					{
						if(null !==($obj->{$field}))
						{
									
							return $obj->{$field};
						}
						else
					   {
							return "";
						}
					}
					else
					{
						if($field=="order_date")
						$field="date_created";
						else if($field=="order_total")
						$field="total";
						else if($field=="order_tax")
						$field="total_tax";
						 
						if(null !==($obj->{"get_".$field}()))
						{
									
							$field_val=$obj->{"get_".$field}();
							$field_val=str_replace("","",$field_val);	
							return $field_val;
						}
						else
					   {
							return "";
						}
					
					}			
				}
				else
				{
					return "";
				}
				
			}
			############################################## Function ConvertDateToDbFormat #################################

			//"T" & "Z" remove from UTC format(in ISO 8601) 
			#######################################################################################################
			function ConvertDateToDbFormat($server_date_utc)  
			{
				if(strpos($server_date_utc,"Z"))
				{
					$utc_fotmat_temp=str_replace("Z","",$server_date_utc);
					$server_date_utc=str_replace("T","",$utc_fotmat_temp);
				   
				}  
				return $server_date_utc;
			}
			
			################################################ Function PrepareWooOrderStatusFilter #######################
			//Prepare order status string based on settings
			#######################################################################################################
			function PrepareWooOrderStatusFilter()
			{
					global $wpdb;
					
					$order_status_filter="";
					if(check_woo_version())
					{
					
						if(WOO_RETRIEVE_ORDER_STATUS_0_ON_HOLD==1)
						{
							$order_status_filter=$wpdb->prefix . "posts.post_status = 'wc-on-hold'";
						
						}
						if(WOO_RETRIEVE_ORDER_STATUS_1_PENDING==1)
						{
							if($order_status_filter=="")
							{
								$order_status_filter.=$wpdb->prefix ."posts.post_status = 'wc-pending'";
							}
							else
							{
								$order_status_filter.=" OR ".$wpdb->prefix ."posts.post_status = 'wc-pending'";
							}
						
						}
						if(WOO_RETRIEVE_ORDER_STATUS_2_PROCESSING==1)
						{
							if($order_status_filter=="")
							{
								$order_status_filter.=$wpdb->prefix . "posts.post_status = 'wc-processing'";
							}
							else
							{
								$order_status_filter.=" OR ".$wpdb->prefix . "posts.post_status = 'wc-processing'";
							}
						
						}
						
						if(WOO_RETRIEVE_ORDER_STATUS_3_COMPLETE==1 )
						{
							if($order_status_filter=="")
							{
								$order_status_filter.=$wpdb->prefix . "posts.post_status = 'wc-completed'";
							}
							else
							{
								$order_status_filter.=" OR ".$wpdb->prefix . "posts.post_status= 'wc-completed'";
							}
						}
						if(WOO_RETRIEVE_ORDER_STATUS_4_CANCELLED==1)
						{
						
							if($order_status_filter=="")
							{
								$order_status_filter.=$wpdb->prefix . "posts.post_status = 'wc-cancelled'";
							}
							else
							{
								$order_status_filter.=" OR ".$wpdb->prefix . "posts.post_status = 'wc-cancelled'";
							}
						
						}

					}
					else
					{				
					
					
						if(WOO_RETRIEVE_ORDER_STATUS_0_ON_HOLD==1)
						{
							$order_status_filter=$wpdb->prefix . "terms.name = 'on-hold'";
						
						}
						if(WOO_RETRIEVE_ORDER_STATUS_1_PENDING==1)
						{
							if($order_status_filter=="")
							{
								$order_status_filter.=$wpdb->prefix . "terms.name = 'pending'";
							}
							else
							{
								$order_status_filter.=" OR ".$wpdb->prefix . "terms.name = 'pending'";
							}
						
						}
						if(WOO_RETRIEVE_ORDER_STATUS_2_PROCESSING==1)
						{
							if($order_status_filter=="")
							{
								$order_status_filter.=$wpdb->prefix . "terms.name = 'processing'";
							}
							else
							{
								$order_status_filter.=" OR ".$wpdb->prefix . "terms.name = 'processing'";
							}
						
						}
						
						if(WOO_RETRIEVE_ORDER_STATUS_3_COMPLETE==1 )
						{
							if($order_status_filter=="")
							{
								$order_status_filter.=$wpdb->prefix . "terms.name = 'completed'";
							}
							else
							{
								$order_status_filter.=" OR ".$wpdb->prefix . "terms.name = 'completed'";
							}
						}
						if(WOO_RETRIEVE_ORDER_STATUS_4_CANCELLED==1)
						{
						
							if($order_status_filter=="")
							{
								$order_status_filter.=$wpdb->prefix . "terms.name = 'cancelled'";
							}
							else
							{
								$order_status_filter.=" OR ".$wpdb->prefix . "terms.name = 'cancelled'";
							}
						
						}
					}
					
					if($order_status_filter!="")
					$order_status_filter="( ".$order_status_filter." ) and";
					return $order_status_filter;
					
			}
					
			
		}
}
######################################### End of class ShippingZWoo ###################################################

	// create object & perform tasks based on command

	$obj_shipping_woo=new ShippingZWoo;
	$obj_shipping_woo->ExecuteCommand();

?>