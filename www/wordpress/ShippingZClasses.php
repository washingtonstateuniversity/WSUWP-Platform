<?php

define("SHIPPINGZCLASSES_VERSION","4.0.13.7456");

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

// PHP version setting
define("ForcePHP4Mode",false);

####################################################### Begin shipping main #################################
	class ShippingZGenericShoppingCart {
		
		var $display_msg="";
		var $complete_shipment_order_xml="";
		var $complete_catalog_commodity_xml="";
		
		################################### Treat this as an abstract class #################
		function __construct()
		{
			if(get_class($this)=='ShippingZGenericShoppingCart'||!is_subclass_of ($this,'ShippingZGenericShoppingCart'))
			{
				  trigger_error('This class is abstract. It cannot be instantiated!',E_USER_ERROR);
			}
		}
		#####################################Get command & perform required actions #################
		/* This will handle all URL parameters & validates them.Then invoke required methods*/
		##################################################################################################
		function ExecuteCommand()
		{
		
			$cmd=strtolower($this->GetValues('cmd'));
			####################### Act according to selected command ########################################
			//getordersbydate - returns list of orders by date range, no paging
			//getorderbynumber - returns order details by order number, no paging
			//getordercountbydate - returns count of orders by date range (in XML format, of course)
			//updateshippinginfo - updates orders with tracking number and shipping details. 
			//ping - checks that API configured properly (has DB access, valid token, etc.)
			//Display error message for invalid commands
			##########################################################################################
			switch($cmd)
			{
					case 'ping':
					//Invokes Ping() function checks for valid token
					$this->Ping();
					if($this->display_msg=="")
					{	
						$this->Check_DB_Access();//checks for DB access
						if($this->display_msg==DB_SUCCESS_MSG)
						{
							if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
							{
								$shiprush_xml=$this->create_xml_obj();
								$shiprush_xml->startElement("Response");
								$shiprush_xml->writeElement("Message",$this->display_msg);
								$shiprush_xml->writeElement("Version", SHIPPINGZCLASSES_VERSION);
								$shiprush_xml->endElement();
								$this->SetXmlMessageResponse($shiprush_xml);
							}
							else
							$this->SetXmlMessageResponse($this->wrap_to_xml('Message',$this->display_msg) . $this->wrap_to_xml('Version',SHIPPINGZCLASSES_VERSION));
						}
						else
						{
							$this->SetXmlError(1,$this->display_msg);
						}
					}
					else
					{
						 $this->SetXmlError(1,$this->display_msg);
					}
					break;
					
					case 'test':
					//Invokes test() function used to debugg
					$this->action=$this->GetValues('action');
					$this->DateFrom=$this->GetValues('DateFrom');
					$this->DateTo=$this->GetValues('DateTo');			
					
					//For all commands -At first check valid token & db access
					$respose_code=$this->Check_Settings();
					
					/*Response code set indicates has DB access, valid token, etc, so perform the required action.Otherwise display error mesage in XML format*/
					
					if($respose_code=="set")
					{	
						
						$this->TestStorePlatform($this->action,$this->DateFrom,$this->DateTo);
					}
					else
					{
						 $this->SetXmlError(1,$this->display_msg);
					}
					break;
					
					//checks server info
					case 'getserverinfo':
					$this->Ping();
					if($this->display_msg=="")
					{	
						//display php version & other server details
						echo 'PHP version: ' . phpversion()."<br>";
						echo "Other Debugging Information:<br>";
						echo "DOCUMENT ROOT: ".$_SERVER['DOCUMENT_ROOT']."<br>";
						echo "SERVER SOFTWARE: ".$_SERVER['SERVER_SOFTWARE']."<br>";
						echo "SCRIPT FILENAME: ".$_SERVER['SCRIPT_FILENAME']."<br>";
						echo "REQUEST URI: ".$_SERVER['REQUEST_URI']."<br>";
						echo "HOST: ".$_SERVER['HTTP_HOST']."<br>";
						echo "PHP INFO of Server:<br>";
						phpinfo();
						exit;
					}
					else
					{
						 $this->SetXmlError(1,$this->display_msg);
					}
					
					
					break;
					case 'getordersbydate':
					//Invokes GetOrdersByDate( DateFrom, DateTo ) which returns list of orders by date range, no paging
					
					$this->DateFrom=$this->GetValues('DateFrom');
					$this->DateTo=$this->GetValues('DateTo');
					
					//check for valid dates
					if($this->check_valid_date($this->DateFrom)!=1 || $this->check_valid_date($this->DateTo)!=1)
					{
						$this->SetXmlError(1,$this->display_msg);
						break;
					}
					
					//For all commands -At first check valid token & db access
					$respose_code=$this->Check_Settings();
					
					
					/*Response code set indicates has DB access, valid token, etc, so perform the required action.Otherwise display error mesage in XML format*/
					if($respose_code=="set")
					{	
						 //Get orders for specific cart
						 $cart_orders=$this->GetOrdersByDate($this->DateFrom,$this->DateTo);
						 
						 //if orders present in specified data range
						if(count($cart_orders)>0)
						{
							 //Convert cart orders to shipping order 
							 for($counter=0;$counter<count($cart_orders);$counter++)
							 {
								$shipping_orders[$counter]=$this->ConvertOrder($cart_orders[$counter]);
							 }
							  
							  //Prepare XML order
							  $this->OrdersToXML($shipping_orders);
						}
						else
						{	
							if(version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
							{
								$shiprush_xml=$this->create_xml_obj();
								$shiprush_xml->startElement("ShipmentOrders");
								$shiprush_xml->endElement();
							}
							else
							$shiprush_xml='<?xml version="1.0"?><ShipmentOrders></ShipmentOrders>';
							
							$this->Display_XML_Output($shiprush_xml);
							
						}
					}
					else
					{
						 $this->SetXmlError(1,$this->display_msg);
					}
					break;
					
					case 'getorderbynumber':
					//Invokes GetOrderByNumber( Ordernumber ) which returns order details by order number, no paging 
					
					$OrderNumber=$this->GetValues('OrderNumber');
					
					//check for valid dates
					if($OrderNumber=="")
					{
						$this->display_msg=MISSING_ORDER_NUMBER_ERROR_MSG;
						$this->SetXmlError(1,$this->display_msg);
						break;
						
					}
					else
					{
					
						//For all commands -At first check valid token & db access
						$response_code=$this->Check_Settings();
						
						/*Response code set indicates has DB access, valid token, etc, so perform the required action.Otherwise display error mesage in XML format*/
						if($response_code=="set")
						{	
							 //Get orders for specific cart
							 $cart_orders=$this->GetOrderByNumber($OrderNumber);
							 
							 //if orders present in specified data range
							if(count($cart_orders)>0)
							{
								 //Convert cart orders to shipping order 
								 for($counter=0;$counter<count($cart_orders);$counter++)
								 {
									$shipping_orders[$counter]=$this->ConvertOrder($cart_orders[$counter]);
								 }
								  
								  //Prepare XML order
								  $this->OrdersToXML($shipping_orders);
							}
							else
							{	
								if(version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
								{
									$shiprush_xml=$this->create_xml_obj();
									$shiprush_xml->startElement("ShipmentOrders");
									$shiprush_xml->endElement();
								}
								else
								$shiprush_xml='<?xml version="1.0"?><ShipmentOrders></ShipmentOrders>';
								
								$this->Display_XML_Output($shiprush_xml);
								
							}
						}
						else
						{
							 $this->SetXmlError(1,$this->display_msg);
						}
					}
					break;
					
					case 'gethighestorderno':
					//Invokes GetHighestOrderNo( ) which returns highest order number 
					
					//For all commands -At first check valid token & db access
					$response_code=$this->Check_Settings();
					
					/*Response code set indicates has DB access, valid token, etc, so perform the required action.Otherwise display error mesage in XML format*/
					if($response_code=="set")
					{	
						 $this->GetHighestOrderNo();
					}						 
					else
					{
						 $this->SetXmlError(1,$this->display_msg);
					}
					
					break;
					
					
					case 'getordercountbydate':
					//Invokes GetOrderCountByDate( DateFrom, DateTo ) which returns order count
					
					$this->DateFrom=$this->GetValues('DateFrom');
					$this->DateTo=$this->GetValues('DateTo');
					
					//check for valid dates
					if($this->check_valid_date($this->DateFrom)!=1 || $this->check_valid_date($this->DateTo)!=1)
					{
						$this->SetXmlError(1,$this->display_msg);
						break;
					}
					
					//For all commands -At first check valid token & db access
					$respose_code=$this->Check_Settings();
					
					/*Response code set indicates has DB access, valid token, etc, so perform the required action.Otherwise display error mesage in XML format*/
					
					if($respose_code=="set")
					{	
						
							if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
							{
								$shiprush_xml=$this->create_xml_obj();
								$shiprush_xml->startElement("Response");
								$shiprush_xml->writeElement("Ordercount",$this->GetOrderCountByDate($this->DateFrom,$this->DateTo));
								$shiprush_xml->endElement();
								$this->SetXmlMessageResponse($shiprush_xml);
							}
							else
							$this->SetXmlMessageResponse($this->wrap_to_xml('Ordercount',$this->GetOrderCountByDate($this->DateFrom,$this->DateTo)));
					}
					else
					{
						 $this->SetXmlError(1,$this->display_msg);
					}
					break;
					
					case 'retrievecatalogcount':
					//Invokes RetrieveCatalogCount() which returns catalog count
										
					//For all commands -At first check valid token & db access
					$respose_code=$this->Check_Settings();
					
					/*Response code set indicates has DB access, valid token, etc, so perform the required action.Otherwise display error mesage in XML format*/
					
					if($respose_code=="set")
					{	
						
							if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
							{
								$shiprush_xml=$this->create_xml_obj();
								$shiprush_xml->startElement("RetrieveCatalogResponse");
								$shiprush_xml->writeElement("Catalogcount",$this->RetrieveCatalogCount());
								$shiprush_xml->endElement();
								$this->SetXmlMessageResponse($shiprush_xml);
							}
							else
							$this->SetXmlMessageResponse($this->wrap_to_xml('RetrieveCatalogResponse',$this->RetrieveCatalogCount()));
					}
					else
					{
						 $this->SetXmlError(1,$this->display_msg);
					}
					break;
					
					case 'updateshippinginfo':
					//Invokes UpdateShippingInfo(OrderNumber) which has following parameters:
					//order number (reqd)
					//tracking number (reqd)
					//shipment date (optional)
					//shipment type (optional)
					//shipment carrier (optional)
					//shipment service (optional)
					//shipping cost (optional)
					//notes block (which would be built by the calling app, and have tracking #, date, and other details in a friendly, ready-to-read block
					
					
					$OrderNumber=$this->GetValues('OrderNumber');
					
											
					//check for ordernumber & tracking number
					if($OrderNumber=="")
					{
						$this->display_msg=MISSING_ORDER_NUMBER_ERROR_MSG;
						$this->SetXmlError(1,$this->display_msg);
					}
					else
					{
						$TrackingNumber=$this->GetValues('TrackingNumber');
						$ShipDate=$this->GetValues('ShipDate');
						$ShipmentType=$this->GetValues('ShipmentType');
						$Notes=$this->GetValues('Notes');
						$Carrier=$this->GetValues('Carrier');
						$Service=$this->GetValues('Service');
						$ShippingCost=$this->GetValues('ShippingCost');
	
						
						//For all commands -At first check valid token & db access
						$respose_code=$this->Check_Settings();
						
						/*Response code set indicates has DB access, valid token, etc, so perform the required action.Otherwise display error mesage in XML format*/
						if($respose_code=="set")
						{	
							
							$this->UpdateShippingInfo($OrderNumber,$TrackingNumber,$ShipDate,$ShipmentType,$Notes,$Carrier,$Service,$ShippingCost);
							
						}
						else
						{
							 $this->SetXmlError(1,$this->display_msg);

						}
					}
					break;
					
					case 'updatecatalog':
					//Invokes UpdateCatalog($ExternalID,$Quantity) which has following parameters:
					//ExternalID (reqd)
					//Quantity (reqd)
										
					
					$ExternalID=$this->GetValues('ExternalID');					
											
					//check for ordernumber & tracking number
					if($ExternalID=="")
					{
						$this->display_msg=MISSING_COMMODITY_ERROR_MSG;
						$this->SetXmlError(1,$this->display_msg);
					}
					else
					{
						$Quantity=$this->GetValues('Quantity');
											
						//For all commands -At first check valid token & db access
						$respose_code=$this->Check_Settings();
						
						/*Response code set indicates has DB access, valid token, etc, so perform the required action.Otherwise display error mesage in XML format*/
						if($respose_code=="set")
						{	
							
							$this->UpdateCatalog($ExternalID,$Quantity);
							
						}
						else
						{
							 $this->SetXmlError(1,$this->display_msg);

						}
					}
					break;
					
					
					case 'retrievecatalog':
					//Invokes GetCatalog( Limit , Offset ) which returns list of catalog with paging
					
					$this->Limit=$this->GetValues('Limit');
					$this->Offset=$this->GetValues('Offset');
					
					//check for valid paging options
					if($this->check_valid_paging_option($this->Limit)!=1 || $this->check_valid_paging_option($this->Offset)!=1)
					{
						$this->SetXmlError(1,$this->display_msg);
						break;
					}
					
					//For all commands -At first check valid token & db access
					$respose_code=$this->Check_Settings();
					
					
					/*Response code set indicates has DB access, valid token, etc, so perform the required action.Otherwise display error mesage in XML format*/
					if($respose_code=="set")
					{	
						 //Get catalog
						 $catalog_commodities=$this->GetCatalog($this->Limit,$this->Offset);
						 
						 //if commodity available
						if(count($catalog_commodities)>0)
						{
							 //Convert catalog commodities to shiprush comodities 
							 for($counter=0;$counter<count($catalog_commodities);$counter++)
							 {
								$shiprush_catalog[$counter]=$this->ConvertCatalog($catalog_commodities[$counter]);
							 }
							
							  //Prepare XML order
							  $this->CommoditiesToXML($shiprush_catalog);
						}
						else
						{	
							if(version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
							{
								$shiprush_xml=$this->create_xml_obj();
								$shiprush_xml->startElement("RetrieveCatalogResponse");
								$shiprush_xml->endElement();
							}
							else
							$shiprush_xml='<?xml version="1.0"?><RetrieveCatalogResponse></RetrieveCatalogResponse>';
							
							$this->Display_XML_Output($shiprush_xml);
							
						}
					}
					else
					{
						 $this->SetXmlError(1,$this->display_msg);
					}
					break;

						
					default:
					$respose_code=$this->Check_Settings();
					
					if($respose_code=="set")
					{
						$this->display_msg=INVALID_CMD;
						$this->SetXmlError(1,$this->display_msg);
					}
					else
					{
						 $this->SetXmlError(1,$respose_code);
					}
					break;
				
				
				}	
		
		
		}	
		###################################### Get offset of server time from UTC #######################
		/*Calculate offset along with direction i.e. + or - from GMT/UTC*/
		##################################################################################################
		function GetServerTimeOffsetFromUTC()
		{
			$difference_from_gmt_including_sign=date("O");
			$sign_from_gmt=substr($difference_from_gmt_including_sign,0,1);
			
			$len=strlen($difference_from_gmt_including_sign);
			
			$difference_from_gmt=substr($difference_from_gmt_including_sign,1,($len-1));
			
			return $sign_from_gmt.($difference_from_gmt); // hours from GMT
		}	
		###################################### Function CheckIfSet #######################
		/*Checks whether a variable is set or not*/
		##################################################################################################
		function CheckIfSet($array,$field)
		{
			if(isset($array[$field]))
				return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $array[$field]);
			else
				return '';
		}	
		###################################### Function GetClassProperty #######################
		/*Checks whether property is set or not & return values accordingly */
		##################################################################################################
		function GetClassProperty($classname,$propertyname,$field="",$defaultValue=0)
		{
			
			if(isset($classname->{$propertyname}))
			{
			
				if($field!="")
				{
					if(isset($classname->{$propertyname}[$field]))
						return $classname->{$propertyname}[$field];
					else
						return $defaultValue;
				}
				else
				{
					return $classname->{$propertyname};
				}
				
			}
		
		}
		############################################## Function GetClassPropertyNumber ##########################
		//calls GetClassProperty function with $defaultValue=1
		##################################################################################################
		function GetClassPropertyNumber($classname,$propertyname,$field="")
		{
			return $this->GetClassProperty($classname,$propertyname,$field,1);
		}	
		############################################## Function GetField #################################
		//Check if variables are set and return data accordingly
		#######################################################################################################
		function GetField($cart_order_temp,$field,$item_counter=-1,$defaultValueIsNumber=0)
		{
				
				 if($item_counter<-1)
				{	//for order items
				
				
					if(isset($cart_order_temp->$field))
					{
						return $cart_order_temp->$field;
					}
					else
					{
						if($defaultValueIsNumber)
							return 0;
						else
							return '';
					}
				
				}
				else if($item_counter>-1)
				{	//for order items
					if(isset($cart_order_temp[$item_counter][$field]))
					{
						return $cart_order_temp[$item_counter][$field];
					}
					else
					{
						if($defaultValueIsNumber)
							return 0;
						else
							return '';
					}
				
				}
				else
				{
					//shipping or billing array fields
					if(isset($cart_order_temp[$field]))
					{
						return $cart_order_temp[$field];
					}
					else
					{
						if($defaultValueIsNumber)
							return 0;
						else
							return '';
					}
				}
			
				
		}
		############################################## Function FormatNumber  ##########################
		//Formats number to money format
		##################################################################################################
		function FormatNumber($number)
		{
			if($number!="")
			{
				$matches="";
				$floats="";
				
				preg_match_all('!\d+(?:\.\d+)?!', $number, $matches);
				$floats = array_map('floatval', $matches[0]);
				$number=$floats[0];		
				
				return number_format($number,2,'.','');
			}
			else
				return "0.00";
		}
		############################################## Function GetFieldString ##########################
		//calls GetField function with $defaultValueIsNumber=""
		##################################################################################################
		function GetFieldString($cart_order_temp,$field,$item_counter=-1)
		{
			return $this->GetField($cart_order_temp,$field,$item_counter,"");
		}			
		############################################## Function GetFieldNumber  ##########################
		//calls GetField function with $defaultValueIsNumber=0
		##################################################################################################
		function GetFieldNumber($cart_order_temp,$field,$item_counter=-1)
		{
			return $this->GetField($cart_order_temp,$field,$item_counter,0);
		}	
		############################################## Function GetFieldMoney  ##########################
		//calls GetField function with $defaultValueIsNumber=0 and also formats number to money format
		##################################################################################################
		function GetFieldMoney($cart_order_temp,$field,$item_counter=-1)
		{
			$result=$this->GetField($cart_order_temp,$field,$item_counter,0);
			if($result!="")
			{
				return number_format($result,2,'.','');
			}
			else
			{
				return "0.00";
			}
		}
		
		################################################ Convert time ####################################
		/*Convert UTC time to server time*/
		##################################################################################################
		function GetServerTimeLocal($formatted=true,$server_date_utc)
		{
		   
			if(strpos($server_date_utc,"Z"))
			{
				$utc_fotmat_temp=str_replace("Z","",$server_date_utc);
				$server_date_utc=str_replace("T","",$utc_fotmat_temp);;//"T" & "Z" removed from UTC format(in ISO 8601)
			   
			}   
		   
			//get offset
			$offset=$this->GetServerTimeOffsetFromUTC();
		   
			$sign=substr($offset,0,1);
			
			$len=strlen($offset);
		   
			$time_difference=substr($offset,1,($len-1));
				
										
			$hours=substr($time_difference,0,2);
			$mins=substr($time_difference,2,2);
			
			
			$server_date_utc_day=substr($server_date_utc,0,10);
			$server_date_utc_time=substr($server_date_utc,10,8);
		   
			$server_date_utc_formmated=$server_date_utc_day." ".$server_date_utc_time;
		   
			$server_date_utc_timestamp = strtotime($server_date_utc_formmated);
		   
			$secs=($hours*3600)+($mins*60);
		   
						  
			if ($sign == "-")
			{
			   
				$timestamp = $server_date_utc_timestamp-($secs);
			}
			else
			{
				$timestamp = $server_date_utc_timestamp+($secs);
			}
		   
		   
			//Adjustment for daylight saving
			$daylight_saving = date('I',$timestamp); 
			if($daylight_saving)
			{
				 if ($sign == "-")
				{
				   
					$timestamp = $timestamp-3600;
				}
				else
				{
					$timestamp = $timestamp+3600;
				}
			}
			
			 $server_date = date("Y-m-d H:i:s", $timestamp); //get Server Date
		   
			if($formatted==true)
			{
				return $server_date;
			}
			else
			{
				return $timestamp;
			}
	   
		}
		##############################################################################################
		/*Convert Server time to UTC*/
		##############################################################################################
		function ConvertServerTimeToUTC($formatted=true,$server_time) 
		{
			
			//get offset
			$offset=$this->GetServerTimeOffsetFromUTC();
			$sign=substr($offset,0,1);
			
			$len=strlen($offset);
			$time_difference=substr($offset,1,($len-1));
			
			$hours=substr($time_difference,0,2);
			$mins=substr($time_difference,2,2);
			
			//Adjustment for daylight saving
			$daylight_saving = date('I',$server_time); 
			if($daylight_saving)
			{
				  if ($sign == "-")
				{
				   
					$hours = $hours-1;
				}
				else
				{
					$hours = $hours+1;
				}
			}
		
			$secs=($hours*3600)+($mins*60);
			
			if ($sign == "-")
			{ 
				$timestamp = $server_time+($secs); 
			}
			else 
			{ 
				$timestamp = $server_time-($secs); 
			}
			
			$gmdate = date("Y-m-d~H:i:s^", $timestamp); //get UTC date
			$gmdate=str_replace("~","T",$gmdate);
			$gmdate=str_replace("^","Z",$gmdate);
			if($formatted==true) 
			{
				return $gmdate;
			}
			else 
			{
				return $timestamp;
			}
		
		}
		############## Check if GET method is set or not and return parameters accordingly #################
		/*The script will support both POST & GET method depending upon settings*/
		##################################################################################################
		function GetValues($field_name)
		{
			if(HTTP_GET_ENABLED==1)
			{
				
				//make it case insensitive
				if(preg_match("/$field_name=/i",$_SERVER['QUERY_STRING'],$matches))
				{
					$case_insensitive_field_name=str_replace("=","",$matches[0]);
					return $_GET[$case_insensitive_field_name];
				}
				
			}
			else
			{
				$posted_string="";
				foreach($_POST as $key=>$val)
				{
					$posted_string.=$key."=".$val."&";
					
				}
				
				if(preg_match("/$field_name=/i",$posted_string,$matches))
				{
					$case_insensitive_field_name=str_replace("=","",$matches[0]);
					return $_POST[$case_insensitive_field_name];
				}
				
			
			}
		}
		
		############################### It will be used to output related messages to the user ###################
		//if there is an error, it will clearly state what is the issue & how it may be fixed etc
		##########################################################################################################
		function SetMessage($msg)
		{
			$this->display_msg=$msg;
							
		}
		
		############################## It will be used to generate XML error/informative messages ###################
		
		function SetXmlMessageResponse($shiprush_xml)
		{
			if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
			{
				if(is_string($shiprush_xml))
				{
					if(strstr($shiprush_xml,'UpdateMessage'))
					{
						$shiprush_xml=$this->create_xml_obj();
						$shiprush_xml->startElement("Response");
						$shiprush_xml->writeElement("UpdateMessage","Success");
						$shiprush_xml->endElement();
					}
				}
				
				$this->Display_XML_Output($shiprush_xml);
			}
			else
			{
				$shiprush_xml='<?xml version="1.0"?>' . $this->wrap_to_xml('Response',$shiprush_xml);			
				$this->Display_XML_Output($shiprush_xml);
			}
							
		}	
		
		################ It will be used to generate XML error messages(with error code & description) ###################
		
		function SetXmlError($code,$desc,$message_details="")
		{
			if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
			{
				$shiprush_xml=$this->create_xml_obj();
				$shiprush_xml->startElement("Error");
				$shiprush_xml->writeElement("Code",$code);
				$shiprush_xml->writeElement("Description",$desc);
				if($message_details!="")
				$shiprush_xml->writeElement("MessageDetails", $message_details);
				$shiprush_xml->writeElement("Version", SHIPPINGZCLASSES_VERSION);
				$shiprush_xml->endElement();
				$this->Display_XML_Output($shiprush_xml);
					
			}
			else
			{
				if($message_details!="")
				$shiprush_xml='<?xml version="1.0"?>' . $this->wrap_to_xml('Error',$this->wrap_to_xml('Code',$code). $this->wrap_to_xml('Description',$desc).$this->wrap_to_xml('MessageDetails',$message_details).$this->wrap_to_xml('Version',SHIPPINGZCLASSES_VERSION));
				else
					$shiprush_xml='<?xml version="1.0"?>' . $this->wrap_to_xml('Error',$this->wrap_to_xml('Code',$code). $this->wrap_to_xml('Description',$desc).$this->wrap_to_xml('Version',SHIPPINGZCLASSES_VERSION));			
				$this->Display_XML_Output($shiprush_xml);
			}
			
			$this->Display_XML_Output($output);
			
				
		}	
		function ReturnHighestOrder($OrderNumber)
		{
			if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
			{
				$shiprush_xml=$this->create_xml_obj();
				$shiprush_xml->startElement("HighestOrderNumber");
				$shiprush_xml->writeElement("OrderNumber",$OrderNumber);
				$shiprush_xml->endElement(); 
				$this->Display_XML_Output($shiprush_xml);
					
			}
			else
			{
				$shiprush_xml='<?xml version="1.0"?>' . $this->wrap_to_xml('HighestOrderNumber',$this->wrap_to_xml('OrderNumber',$OrderNumber));		
				$this->Display_XML_Output($shiprush_xml);
			}
			
			
				
		}	
		############################### Check for valid date range ###########################################
	
		function check_valid_date($date)
		{
			
			if((strpos($date,"T")===false) || (strpos($date,"Z")===false))
			{
			
				$this->display_msg=INVAID_DATE_ERROR_MSG;
			}
			else 
			{	
				
				
				$date=str_replace("Z","",$date);	
				$date=str_replace("T"," ",$date);			
				
				$date_temp=explode(" ",$date);
				$date=$date_temp[0];
				
				$arr=explode("-",$date); // splitting the array
				if($date=="" || count($arr)!="3")
				{
					$this->display_msg=INVAID_DATE_ERROR_MSG;
				}
				else
				{
					
					$month=$arr[1]; // first element of the array is month
					$day=$arr[2]; // second element is date
					$year=$arr[0]; // third element is year
				
					if($month=="" || $day=="" || $year=="")
					{
						$this->display_msg=INVAID_DATE_ERROR_MSG;
					}
					else if(!is_numeric($month) || !is_numeric($day) || !is_numeric($year))
					{
						$this->display_msg=INVAID_DATE_ERROR_MSG;
					}
					else if(!checkdate($month,$day,$year))
					{
						$this->display_msg=INVAID_DATE_ERROR_MSG;
					}
					else 
					{
						return 1;
					} 
				}
			}//end UTC check		
		}
		############################### Check for valid paging option ###########################################
	
		function check_valid_paging_option($value)
		{
			
			if(!is_numeric($value))
			{
				$this->display_msg=INVAID_PAGING_ERROR_MSG;
			}
			else
			{
				return 1;
			}
			//end of checking		
		}
		################################################ Ping function #####################################	
		function Ping()
		{
			
			################################################# check for valid token#############################
			//It should be more than twelve characters long, less than 36, and must contain letters and numbers. 
			#####################################################################################################
			$token_lenght=strlen(SHIPPING_ACCESS_TOKEN);
			
			if($token_lenght<12 || $token_lenght>36)
			{
				$this->SetMessage(TOKEN_ERROR_MSG);
				
				
			}
			else if(!preg_match('/^[a-z0-9]+$/i', SHIPPING_ACCESS_TOKEN))//check does not contain special chars
			{
				$this->SetMessage(TOKEN_ERROR_MSG);
				
			}
			else if(!preg_match('#[0-9]#', SHIPPING_ACCESS_TOKEN))//check that contains atleast one digit
			{
				$this->SetMessage(TOKEN_ERROR_MSG);
				
			}
			else if(!preg_match('#[A-Z]#', SHIPPING_ACCESS_TOKEN)&&!preg_match('#[a-z]#', SHIPPING_ACCESS_TOKEN))//check that contains atleast one albhabet
			{
				$this->SetMessage(TOKEN_ERROR_MSG);
				
			}
			
				
			if($this->GetValues('shipping_access_token')!=SHIPPING_ACCESS_TOKEN&&$this->GetValues('SHIPPING_ACCESS_TOKEN')!=SHIPPING_ACCESS_TOKEN)
			{
				if($this->display_msg!=TOKEN_ERROR_MSG)
				$this->SetMessage(URL_TOKEN_ERROR_MSG);
			}
			
			
			
		}
		
   ############### This will be involked for all commands except "ping" to check proper settings ##################
		function Check_Settings()
		{
			//For all commands -At first check valid token & db access
			$this->Ping();
			
			if($this->display_msg=="")
			{	
				
				##################################################### Used for debugging ##########################
				if(isset($_GET['show_settings']))
				{
					if($_GET['show_settings']==1)
					{
						$handle = fopen("ShippingZSettings.php", "r");
						$contents="";
						while (!feof($handle)) 
						{
							$contents .= fread($handle, 8192);
						}
						fclose($handle); 
						print(htmlspecialchars($contents));
						exit;
					}
				}
				###########################################################################################
				
				$this->Check_DB_Access();//checks for DB access
				if($this->display_msg==DB_SUCCESS_MSG )
				{
					return "set";
				}
			}
			else
			{
				return $this->display_msg;
			}
		
		}
		
		
		############################# Definition of GetOrdersByDate function #####################################
		 function GetOrdersByDate($datefrom,$dateto) { }
		
		############################ Definition of GetOrderCountByDate function #####################################
		function GetOrderCountByDate($datefrom,$dateto) { }
		
		############################ Definition of GetOrderByNumber function #####################################
		function GetOrderByNumber($OrderNumber) { }
		############################ Definition of GetHighestOrderNo function #####################################
		function GetHighestOrderNo() { }
		############################# Definition of UpdateShippingInfo function ##########################
		function UpdateShippingInfo($OrderNumber,$TrackingNumber,$ShipDate='',$ShipmentType='',$Notes='',$Carrier='',$Service='',$ShippingCost='') {}		
		############################# Definition of GetCatalog function ##########################
		function GetCatalog() {}
		############################# Definition of UpdateCatalog function ##########################
		function UpdateCatalog($ExternalID ,$Quantity ) {}
		
		################################################ XML Serialization #################################### 
		//Creates XML node string
		// <fieldname>value</fieldname>
		#######################################################################################################	
		
		function wrap_to_xml( $fieldname, $fieldvalue )
		{
			return "<" . $fieldname . ">" . $fieldvalue . "</" . $fieldname . ">";
			
		}
		
		################################################ XML Serialization #################################### 
		//Creates CDATA XML node string
		// <fieldname><![CDATA[value]]></fieldname>
		#######################################################################################################
			
		function wrap_to_xml_cdata( $fieldname, $fieldvalue )
		{
			return "<" . $fieldname . "><![CDATA[" . $fieldvalue . "]]></" . $fieldname . ">";
		}
		
		################################################ XML Serialization ####################################
		// Creates XML node from PHP array field
		#######################################################################################################
		function array_field_to_xml( $fieldname, $array )
		{
			if(isset($array[ $fieldname ]) && $array[ $fieldname ]!="" )
			return $this->wrap_to_xml_cdata( $fieldname, $array[ $fieldname ] );
		}
		
		
		################################################ XML Serialization #################################### 
		// Creates XML representation of the all order
		//ShipmentOrders element is added
		#######################################################################################################  
		function shipment_order_xml( $complete_shipment_order_xml )
		{
			
			return  '<?xml version="1.0"?>' . $this->wrap_to_xml( 
					'ShipmentOrders',$complete_shipment_order_xml);
			
			
		}
		
		################################################ XML Serialization #################################### 
		// Creates XML representation of the individual order
		#######################################################################################################  
		function shipment_individual_order_xml( $order, $shiprush_xml="")
		{
			
			if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
			{
				$shiprush_xml->startElement("ShipmentOrder");
				
				$shiprush_xml=$this->order_info_xml($order,$shiprush_xml);
				$shiprush_xml=$this->order_items_xml($order,$shiprush_xml);
				$shiprush_xml=$this->order_shipping_xml($order,$shiprush_xml);
				$shiprush_xml=$this->order_billing_xml($order,$shiprush_xml);
				
				$shiprush_xml->endElement();
				
				return $shiprush_xml;
			}
			else
			{
				return 
				$this->wrap_to_xml( 
				'ShipmentOrder',
				$this->order_info_xml( $order ) .
				$this->order_items_xml( $order ).
				$this->order_shipping_xml( $order ) .
				$this->order_billing_xml( $order ) );
			}
		}
		
		
		################################################ XML Serialization #################################### 
		// Order items data as XML
		#######################################################################################################    
		function order_items_xml( $order, $shiprush_xml="" )
		{
			 if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
		     {
					for($prod_count=0; $prod_count < $order->num_of_products; $prod_count++)
					{
						 $shiprush_xml->startElement("ShipmentOrderItem");
						 
						 //check if product sort order available
						 if(isset($order->order_product[$prod_count]['SequenceNumberWithinOrder'])) 
						 $shiprush_xml=$this->xml_write_shiprush_data("SequenceNumberWithinOrder",$order->order_product[$prod_count], $shiprush_xml);
						 
						 $shiprush_xml=$this->xml_write_shiprush_data("Name",$order->order_product[$prod_count], $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Price",$order->order_product[$prod_count], $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("ExternalID",$order->order_product[$prod_count], $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Quantity",$order->order_product[$prod_count], $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Weight",$order->order_product[$prod_count], $shiprush_xml);
						 if(isset($order->order_product[$prod_count]['UOMProductWeight'])) 
						 $shiprush_xml=$this->xml_write_shiprush_data("UOMProductWeight",$order->order_product[$prod_count], $shiprush_xml);
						 if(isset($order->order_product[$prod_count]['WeightUOM'])) 
						 $shiprush_xml=$this->xml_write_shiprush_data("WeightUOM",$order->order_product[$prod_count], $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("ItemLength",$order->order_product[$prod_count], $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("ItemWidth",$order->order_product[$prod_count], $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("ItemHeight",$order->order_product[$prod_count], $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Notes",$order->order_product[$prod_count], $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Total",$order->order_product[$prod_count], $shiprush_xml);
						 
						 $shiprush_xml->endElement();
					}
			
					return $shiprush_xml;
			 }
			 else
			 {
					$this->product_xml="";
					$this->all_product_xml="";
					for($prod_count=0; $prod_count < $order->num_of_products; $prod_count++)
					{
						 //check if product sort order available
						 if(isset($order->order_product[$prod_count]['SequenceNumberWithinOrder'])) 
							$sort_order_xml=$this->array_field_to_xml( 'SequenceNumberWithinOrder',$order->order_product[$prod_count] );
						else
						 $sort_order_xml="";
						 
						  $this->product_xml=$sort_order_xml.$this->array_field_to_xml( 'Name', $order->order_product[$prod_count] ) .
						  $this->array_field_to_xml( 'Price', $order->order_product[$prod_count] ) .
						  $this->array_field_to_xml( 'ExternalID', $order->order_product[$prod_count] ) .
						  $this->array_field_to_xml( 'Quantity' ,$order->order_product[$prod_count] ) . 
						  $this->array_field_to_xml( 'Weight' ,$order->order_product[$prod_count] ) .
						  $this->array_field_to_xml( 'UOMProductWeight' ,$order->order_product[$prod_count] ) .
						   $this->array_field_to_xml( 'WeightUOM' ,$order->order_product[$prod_count] ) .
						  $this->array_field_to_xml( 'ItemLength' ,$order->order_product[$prod_count] ) .
						  $this->array_field_to_xml( 'ItemWidth' ,$order->order_product[$prod_count] ) .
						  $this->array_field_to_xml( 'ItemHeight' ,$order->order_product[$prod_count] ) .
						  $this->array_field_to_xml( 'Notes' ,$order->order_product[$prod_count] ) . 
						  $this->array_field_to_xml( 'Total' , $order->order_product[$prod_count] );
						  $this->all_product_xml.=$this->wrap_to_xml( 'ShipmentOrderItem' ,$this->product_xml);
					}
					
					return $this->all_product_xml;
				}
		}    
		
		
		################################################ XML Serialization ####################################
		// Delivery-To (shipping) address data as XML
		#######################################################################################################    
		function order_shipping_xml( $order, $shiprush_xml=""  )
		{
		   if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
		    {
				$shiprush_xml->startElement("ShippingAddress");
				
				$shiprush_xml=$this->xml_write_shiprush_data("FirstName",$order->order_shipping, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("LastName",$order->order_shipping, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("Company",$order->order_shipping, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("Address1",$order->order_shipping, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("Address2",$order->order_shipping, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("City",$order->order_shipping, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("State",$order->order_shipping, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("PostalCode",$order->order_shipping, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("Country",$order->order_shipping, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("Phone",$order->order_shipping, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("EMail",$order->order_shipping, $shiprush_xml);
				$shiprush_xml->endElement();
				
				return $shiprush_xml;
			}
			else
			{
			 
				   return $this->wrap_to_xml( 'ShippingAddress' ,
				   $this->array_field_to_xml( 'FirstName',$order->order_shipping ) .
				   $this->array_field_to_xml( 'LastName', $order->order_shipping ) .
				   $this->array_field_to_xml( 'Company' , $order->order_shipping ) . 
				   $this->array_field_to_xml( 'Address1' , $order->order_shipping ) .
				   $this->array_field_to_xml( 'Address2' , $order->order_shipping ) .
				   $this->array_field_to_xml( 'City' , $order->order_shipping ) .
				   $this->array_field_to_xml( 'State'  , $order->order_shipping ) .
				   $this->array_field_to_xml( 'PostalCode' , $order->order_shipping ) .
				   $this->array_field_to_xml( 'Country'  , $order->order_shipping ) .
				   $this->array_field_to_xml( 'Phone'  ,$order->order_shipping ).
				   $this->array_field_to_xml( 'EMail'  , $order->order_shipping ) );
			}
		}    
		
		################################################ XML Serialization ####################################
		// Billing address data as XML
		#######################################################################################################   
		function order_billing_xml( $order , $shiprush_xml=""  )
		{
		      if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
			  {
			  	$shiprush_xml->startElement("BillingAddress");
				
				$shiprush_xml=$this->xml_write_shiprush_data("FirstName",$order->order_billing, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("LastName",$order->order_billing, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("Company",$order->order_billing, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("Address1",$order->order_billing, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("Address2",$order->order_billing, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("City",$order->order_billing, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("State",$order->order_billing, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("PostalCode",$order->order_billing, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("Country",$order->order_billing, $shiprush_xml);
				$shiprush_xml=$this->xml_write_shiprush_data("Phone",$order->order_billing, $shiprush_xml);
				if(isset($order->order_billing['EMail']))
				$shiprush_xml=$this->xml_write_shiprush_data("EMail",$order->order_billing, $shiprush_xml);
				
				$shiprush_xml->endElement();
				
				return $shiprush_xml;
			  }
			  else
			  {
			  	$EMail_xml="";
				if(isset($order->order_billing['EMail']))
				$EMail_xml=$this->array_field_to_xml( 'EMail',$order->order_billing );
				
				 return $this->wrap_to_xml( 'BillingAddress' ,
				   $this->array_field_to_xml( 'FirstName', $order->order_billing ) .
				   $this->array_field_to_xml( 'LastName', $order->order_billing ) .
				   $this->array_field_to_xml( 'Company' , $order->order_billing ) . 
				   $this->array_field_to_xml( 'Address1' , $order->order_billing ) .
				   $this->array_field_to_xml( 'Address2' , $order->order_billing ) .
				   $this->array_field_to_xml( 'City' , $order->order_billing ) .
				   $this->array_field_to_xml( 'State'  , $order->order_billing ) .
				   $this->array_field_to_xml( 'PostalCode' , $order->order_billing ) .
				   $this->array_field_to_xml( 'Country'  , $order->order_billing ) .
				   $this->array_field_to_xml( 'Phone'  , $order->order_billing ).$EMail_xml );
			  }
		}    
		
		################################################ XML Serialization ####################################
		// Order Info as XML
		#######################################################################################################  
		function order_info_xml( $order,$shiprush_xml="" )
		{
		       if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
		       {
					$shiprush_xml=$this->xml_write_shiprush_data("OrderDate",$order->order_info, $shiprush_xml);
					
					if(isset($order->order_info['Currency']))
					$shiprush_xml=$this->xml_write_shiprush_data("Currency",$order->order_info, $shiprush_xml);
					
					if(isset($order->order_info['ProductType']))
					$shiprush_xml=$this->xml_write_shiprush_data("ProductType",$order->order_info, $shiprush_xml);
					
					if(isset($order->order_info['ManagedByWebStore']))
					$shiprush_xml=$this->xml_write_shiprush_data("ManagedByWebStore",$order->order_info, $shiprush_xml);
					
					$shiprush_xml=$this->xml_write_shiprush_data("ItemsTotal",$order->order_info, $shiprush_xml);
					$shiprush_xml=$this->xml_write_shiprush_data("Total",$order->order_info, $shiprush_xml);
					$shiprush_xml=$this->xml_write_shiprush_data("Discount",$order->order_info, $shiprush_xml);
					$shiprush_xml=$this->xml_write_shiprush_data("ShippingChargesPaid",$order->order_info, $shiprush_xml);
					$shiprush_xml=$this->xml_write_shiprush_data("ShipMethod",$order->order_info, $shiprush_xml);
					$shiprush_xml=$this->xml_write_shiprush_data("ItemsTax",$order->order_info, $shiprush_xml);
					$shiprush_xml=$this->xml_write_shiprush_data("OrderNumber",$order->order_info, $shiprush_xml);
					
					if(isset($order->order_info['AlternateOrderNumber']))
					$shiprush_xml=$this->xml_write_shiprush_data("AlternateOrderNumber",$order->order_info, $shiprush_xml);
					
					if(!isset($order->order_info['ExternalID']))	  
					$order->order_info['ExternalID']=$order->order_info["OrderNumber"] . '-' . $order->order_info["OrderDate"];
					
					if(isset($order->order_info['ExternalID']))
					$shiprush_xml=$this->xml_write_shiprush_data("ExternalID",$order->order_info, $shiprush_xml);
					
					if(isset($order->order_info['ShippingSameAsBilling']))
					$shiprush_xml=$this->xml_write_shiprush_data("ShippingSameAsBilling",$order->order_info, $shiprush_xml);
					
					$shiprush_xml=$this->xml_write_shiprush_data("Comments",$order->order_info, $shiprush_xml);
					$shiprush_xml=$this->xml_write_shiprush_data("PaymentType",$order->order_info, $shiprush_xml);
					$shiprush_xml=$this->xml_write_shiprush_data("PaymentStatus",$order->order_info, $shiprush_xml);
					$shiprush_xml=$this->xml_write_shiprush_data("IsShipped",$order->order_info, $shiprush_xml);
					$shiprush_xml=$this->xml_write_shiprush_data("IsCancelled",$order->order_info, $shiprush_xml);
					
					if(isset($order->order_info['UnitsOfMeasureWeight']))
					$shiprush_xml=$this->xml_write_shiprush_data("UnitsOfMeasureWeight",$order->order_info, $shiprush_xml);
					
					if(isset($order->order_info['UnitsOfMeasureLinear']))
					$shiprush_xml=$this->xml_write_shiprush_data("UnitsOfMeasureLinear",$order->order_info, $shiprush_xml);
					
					if(isset($order->order_info['UOMWeight']))
					$shiprush_xml=$this->xml_write_shiprush_data("UOMWeight",$order->order_info, $shiprush_xml);
				  
					if(isset($order->order_info['PackageActualWeight']))
					$shiprush_xml=$this->xml_write_shiprush_data("PackageActualWeight",$order->order_info, $shiprush_xml);				
					
					if(isset($order->order_info['PkgLength']) && $order->order_info['PkgLength']!="")
					{
						if($order->order_info['PkgWidth']=="")
						$order->order_info['PkgWidth']=0;
						
						if($order->order_info['PkgHeight']=="")
						$order->order_info['PkgHeight']=0;
						
						$shiprush_xml=$this->xml_write_shiprush_data("PkgLength",$order->order_info, $shiprush_xml);
						$shiprush_xml=$this->xml_write_shiprush_data("PkgWidth",$order->order_info, $shiprush_xml);
						$shiprush_xml=$this->xml_write_shiprush_data("PkgHeight",$order->order_info, $shiprush_xml);
					}
					
								
					return  $shiprush_xml;
			   }
			   else
			   {
			
					if(isset($order->order_info['PackageActualWeight']))
						 $package_xml=$this->array_field_to_xml( 'PackageActualWeight',$order->order_info );//added shipping weight
					else
						 $package_xml="";
						 
					if(isset($order->order_info['UOMWeight']))
						 $uom_xml=$this->array_field_to_xml( 'UOMWeight',$order->order_info );//added unit of shipping weight
					else
						 $uom_xml="";
						 
					if(isset($order->order_info['UnitsOfMeasureWeight']))
						 $uom_accepted_xml=$this->array_field_to_xml( 'UnitsOfMeasureWeight',$order->order_info );//added unit of shipping weight
					else
						 $uom_accepted_xml="";
						 
					if(isset($order->order_info['UnitsOfMeasureLinear']))
						 $linear_accepted_xml=$this->array_field_to_xml( 'UnitsOfMeasureLinear',$order->order_info );
					else
						 $linear_accepted_xml="";
					
					if(isset($order->order_info['PkgLength']) && $order->order_info['PkgLength']!="")
					{
						if($order->order_info['PkgWidth']=="")
						$order->order_info['PkgWidth']=0;
						
						if($order->order_info['PkgHeight']=="")
						$order->order_info['PkgHeight']=0;
						
						 $dimension_xml=$this->array_field_to_xml( 'PkgLength',$order->order_info ).$this->array_field_to_xml( 'PkgWidth',$order->order_info ).$this->array_field_to_xml( 'PkgHeight',$order->order_info );
					}
					else
						 $dimension_xml="";
						  
					
					if(isset($order->order_info['ExternalID']))
						 $externalID_xml=$this->array_field_to_xml( 'ExternalID',$order->order_info );//added unit of shipping weight
					else
						 $externalID_xml=$this->wrap_to_xml('ExternalID',$order->order_info["OrderNumber"] . '-' . $order->order_info["OrderDate"]);	 
					
					$AlternateOrderNumber_xml="";
					if(isset($order->order_info['AlternateOrderNumber']))
					$AlternateOrderNumber_xml=$this->array_field_to_xml( 'AlternateOrderNumber',$order->order_info );
				
				    return 
					   $this->array_field_to_xml( 'OrderDate', $order->order_info) .
					   $this->array_field_to_xml( 'Currency', $order->order_info ) .  
					   $this->array_field_to_xml( 'ProductType', $order->order_info ) .  
					   $this->array_field_to_xml( 'ManagedByWebStore', $order->order_info ) .  
					   $this->array_field_to_xml( 'ItemsTotal', $order->order_info ) . 
					   $this->array_field_to_xml( 'Total', $order->order_info ) .
					    $this->array_field_to_xml( 'Discount', $order->order_info ) . 
					   $this->array_field_to_xml( 'ShippingChargesPaid' , $order->order_info ) . 
						$this->array_field_to_xml( 'ShipMethod' , $order->order_info ) . 
					   $this->array_field_to_xml( 'ItemsTax' , $order->order_info ) .
					   $this->array_field_to_xml( 'OrderNumber' , $order->order_info) .
					   $AlternateOrderNumber_xml.$externalID_xml .
					   $this->array_field_to_xml( 'ShippingSameAsBilling'  , $order->order_info ) .
					   $this->array_field_to_xml( 'Comments'  , $order->order_info ) .
					   $this->array_field_to_xml( 'PaymentType' , $order->order_info ) .
					   $this->array_field_to_xml( 'PaymentStatus' , $order->order_info ).
					   $this->array_field_to_xml( 'IsShipped' , $order->order_info ).
					   $this->array_field_to_xml( 'IsCancelled' , $order->order_info ).$uom_accepted_xml.$linear_accepted_xml.$uom_xml.$package_xml.$dimension_xml;
			  }
				   
		} 
		
			############################################## Function ConvertOrder #################################
			//Conver cart order to shipping_order
			#######################################################################################################
			  function ConvertOrder($cart_order_array)
			  {
				
					//prepare order array
					$shipping_order=new stdClass(); 
					$shipping_order->orderid=$cart_order_array->orderid;
					$shipping_order->num_of_products=$cart_order_array->num_of_products;
					
					//shipping details
					$shipping_order->order_shipping["FirstName"]=$this->MakeXMLSafe($cart_order_array->order_shipping["FirstName"]);
					$shipping_order->order_shipping["LastName"]=$this->MakeXMLSafe($cart_order_array->order_shipping["LastName"]);
					$shipping_order->order_shipping["Company"]=$this->MakeXMLSafe($cart_order_array->order_shipping["Company"]);
					$shipping_order->order_shipping["Address1"]=$this->MakeXMLSafe($cart_order_array->order_shipping["Address1"]);
					
					if(isset($cart_order_array->order_shipping["Address2"]))
					$shipping_order->order_shipping["Address2"]=$this->MakeXMLSafe($cart_order_array->order_shipping["Address2"]);
					
					$shipping_order->order_shipping["City"]=$this->MakeXMLSafe($cart_order_array->order_shipping["City"]);
					$shipping_order->order_shipping["State"]=$this->MakeXMLSafe($cart_order_array->order_shipping["State"]);
					$shipping_order->order_shipping["PostalCode"]=$this->MakeXMLSafe($cart_order_array->order_shipping["PostalCode"]);
					$shipping_order->order_shipping["Country"]=$this->MakeXMLSafe($cart_order_array->order_shipping["Country"]);
					$shipping_order->order_shipping["Phone"]=$this->MakeXMLSafe($cart_order_array->order_shipping["Phone"]);
					$shipping_order->order_shipping["EMail"]=$this->MakeXMLSafe($cart_order_array->order_shipping["EMail"]);
					
					//billing details
					$shipping_order->order_billing["FirstName"]=$this->MakeXMLSafe($cart_order_array->order_billing["FirstName"]);
					$shipping_order->order_billing["LastName"]=$this->MakeXMLSafe($cart_order_array->order_billing["LastName"]);
					$shipping_order->order_billing["Company"]=$this->MakeXMLSafe($cart_order_array->order_billing["Company"]);
					$shipping_order->order_billing["Address1"]=$this->MakeXMLSafe($cart_order_array->order_billing["Address1"]);
					
					if(isset($cart_order_array->order_billing["Address2"]))
					$shipping_order->order_billing["Address2"]=$this->MakeXMLSafe($cart_order_array->order_billing["Address2"]);
					
					$shipping_order->order_billing["City"]=$this->MakeXMLSafe($cart_order_array->order_billing["City"]);
					$shipping_order->order_billing["State"]=$this->MakeXMLSafe($cart_order_array->order_billing["State"]);
					$shipping_order->order_billing["PostalCode"]=$this->MakeXMLSafe($cart_order_array->order_billing["PostalCode"]);
					$shipping_order->order_billing["Country"]=$this->MakeXMLSafe($cart_order_array->order_billing["Country"]);
					$shipping_order->order_billing["Phone"]=$this->MakeXMLSafe($cart_order_array->order_billing["Phone"]);
					
					if(isset($cart_order_array->order_billing["EMail"]))
					$shipping_order->order_billing["EMail"]=$this->MakeXMLSafe($cart_order_array->order_billing["EMail"]);
					
					//order info
					$shipping_order->order_info["OrderDate"]=$this->MakeXMLSafe($cart_order_array->order_info["OrderDate"]);
					
					if(isset($cart_order_array->order_info["ExternalID"]))
					$shipping_order->order_info["ExternalID"]=$this->MakeXMLSafe($cart_order_array->order_info["ExternalID"]);
					if(isset($cart_order_array->order_info["Currency"]))
					{
						$shipping_order->order_info["Currency"]=$this->MakeXMLSafe($cart_order_array->order_info['Currency']);
					} 
					if(isset($cart_order_array->order_info["ProductType"]))
					{
						$shipping_order->order_info["ProductType"]=$this->MakeXMLSafe($cart_order_array->order_info['ProductType']);
					}
					if(isset($cart_order_array->order_info["ManagedByWebStore"]))
					{
						$shipping_order->order_info["ManagedByWebStore"]=$this->MakeXMLSafe($cart_order_array->order_info['ManagedByWebStore']);
					}
					$shipping_order->order_info["ItemsTotal"]=$this->MakeXMLSafe($this->GetFieldMoney($cart_order_array->order_info,"ItemsTotal"));
					$shipping_order->order_info["Total"]=$this->MakeXMLSafe($this->GetFieldMoney($cart_order_array->order_info,"Total"));
					$shipping_order->order_info["Discount"]=$this->MakeXMLSafe($this->GetFieldMoney($cart_order_array->order_info,"Discount"));
					$shipping_order->order_info["ShippingChargesPaid"]=$this->MakeXMLSafe($this->GetFieldMoney($cart_order_array->order_info,"ShippingChargesPaid"));
					$shipping_order->order_info["ShipMethod"]=$this->MakeXMLSafe($cart_order_array->order_info["ShipMethod"]);
					$shipping_order->order_info["ItemsTax"]=$this->MakeXMLSafe($this->GetFieldMoney($cart_order_array->order_info,"ItemsTax"));
					$shipping_order->order_info["OrderNumber"]=$this->MakeXMLSafe($cart_order_array->order_info["OrderNumber"]);
					if(isset($cart_order_array->order_info["AlternateOrderNumber"]))
					$shipping_order->order_info["AlternateOrderNumber"]=$this->MakeXMLSafe($cart_order_array->order_info["AlternateOrderNumber"]);
					$shipping_order->order_info["PaymentType"]=$this->MakeXMLSafe($cart_order_array->order_info["PaymentType"]);
					$shipping_order->order_info["Comments"]=$this->MakeXMLSafe($cart_order_array->order_info["Comments"]);
					$shipping_order->order_info["PaymentStatus"]=$this->MakeXMLSafe($cart_order_array->order_info["PaymentStatus"]);
					$shipping_order->order_info["IsShipped"]=$this->MakeXMLSafe($cart_order_array->order_info["IsShipped"]);
					$shipping_order->order_info["IsCancelled"]= $this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_info,"IsCancelled"));
					
					if(isset($cart_order_array->order_info["UOMWeight"]))
					$shipping_order->order_info["UOMWeight"]= $this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_info,"UOMWeight"));
					
					if(isset($cart_order_array->order_info["UnitsOfMeasureWeight"]))
					$shipping_order->order_info["UnitsOfMeasureWeight"]= $this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_info,"UnitsOfMeasureWeight"));
					
					if(isset($cart_order_array->order_info["UnitsOfMeasureLinear"]))
					$shipping_order->order_info["UnitsOfMeasureLinear"]= $this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_info,"UnitsOfMeasureLinear"));
					
					if(isset($cart_order_array->order_info["PkgLength"]))
					$shipping_order->order_info["PkgLength"]= $this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_info,"PkgLength"));
					
					if(isset($cart_order_array->order_info["PkgWidth"]))
					$shipping_order->order_info["PkgWidth"]= $this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_info,"PkgWidth"));
					
					if(isset($cart_order_array->order_info["PkgHeight"]))
					$shipping_order->order_info["PkgHeight"]= $this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_info,"PkgHeight"));

					$shipping_order->order_info["PackageActualWeight"]=0;	
					
					//get order products 
					if (isset($cart_order_array->order_product))
					{    
						for($j=0;$j<count($cart_order_array->order_product);$j++)
						{
							
														
							$shipping_order->order_product[$j]["Name"]=$this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_product[$j],"Name"));	
							$shipping_order->order_product[$j]["Price"]=$this->MakeXMLSafe($this->GetFieldMoney($cart_order_array->order_product[$j],"Price"));
							$shipping_order->order_product[$j]["Quantity"]=$this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_product[$j],"Quantity"));
							$shipping_order->order_product[$j]["Total"]=$this->MakeXMLSafe($this->GetFieldMoney($cart_order_array->order_product[$j],"Total"));
							$shipping_order->order_product[$j]["ExternalID"]= $this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_product[$j],"ExternalID"));
							$shipping_order->order_product[$j]["Notes"]=$this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_product[$j],"Notes"));
							
							if(isset($cart_order_array->order_product[$j]["IndividualProductWeight"]))
							{
								$shipping_order->order_product[$j]["Weight"]=$this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_product[$j],"IndividualProductWeight"));
								if(isset($cart_order_array->order_product[$j]["UOMProductWeight"]))
								{
									$shipping_order->order_product[$j]["UOMProductWeight"]=$this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_product[$j],"UOMProductWeight"));
								}
								if(isset($cart_order_array->order_product[$j]["WeightUOM"]))
								{
									$shipping_order->order_product[$j]["WeightUOM"]=$this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_product[$j],"WeightUOM"));
								}
								
							}
							
							if(isset($cart_order_array->order_product[$j]["Total_Product_Weight"]))
							{
								if($cart_order_array->order_product[$j]["Total_Product_Weight"]!="")
								{
									$shipping_order->order_product[$j]["Total_Product_Weight"]=$this->MakeXMLSafe($cart_order_array->order_product[$j]["Total_Product_Weight"]);//add product weight
									$shipping_order->order_info["PackageActualWeight"]+=$this->MakeXMLSafe((float)$shipping_order->order_product[$j]["Total_Product_Weight"]);//total shipping weight
								}
								
							}
							
							if(isset($cart_order_array->order_product[$j]["SequenceNumberWithinOrder"]))
							{
								$shipping_order->order_product[$j]["SequenceNumberWithinOrder"]=$this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_product[$j],"SequenceNumberWithinOrder"));	
							}
							if(isset($cart_order_array->order_product[$j]["ItemLength"]))
							$shipping_order->order_product[$j]["ItemLength"]=$this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_product[$j],"ItemLength"));	
							
							if(isset($cart_order_array->order_product[$j]["ItemWidth"]))
							$shipping_order->order_product[$j]["ItemWidth"]=$this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_product[$j],"ItemWidth"));	
							
							if(isset($cart_order_array->order_product[$j]["ItemHeight"]))
							$shipping_order->order_product[$j]["ItemHeight"]=$this->MakeXMLSafe($this->CheckIfSet($cart_order_array->order_product[$j],"ItemHeight"));	
							
							
							
							
					   }
					}
					if($shipping_order->order_info["PackageActualWeight"]!="")
					$shipping_order->order_info["PackageActualWeight"]=$this->MakeXMLSafe($shipping_order->order_info["PackageActualWeight"]);
					   
				  return $shipping_order;
			  }
		######################################## function MakeXMLSafe ############################################	
		//Make a string completely safe for XML-required for user comments
		##########################################################################################################
		function MakeXMLSafe ($strin) 
		{
			//removed encoding logic(will be dealt in My.SR), keep this function for future use if required
			$strout=$strin;
			
			return $strout;
		}
		######################################## function OrdersToXML ############################################	
		//Generate order XML
		##########################################################################################################
		function OrdersToXML($shipping_orders)
		{
			if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
			{
				$shiprush_xml=$this->create_xml_obj();	
				$shiprush_xml->startElement("ShipmentOrders");
			}
					
			for($i=0;$i<count ($shipping_orders);$i++)
			{
				if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
				$shiprush_xml=$this->shipment_individual_order_xml($shipping_orders[$i],$shiprush_xml);
				else
				$this->complete_shipment_order_xml.=$this->shipment_individual_order_xml($shipping_orders[$i]);
			}
			
			if(version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
			 {
			   	  $shiprush_xml->endElement(); 
			      $this->Display_XML_Output($shiprush_xml);
			}
			else
			{
				$shiprush_xml=$this->shipment_order_xml( $this->complete_shipment_order_xml );				
				$this->Display_XML_Output($shiprush_xml);
			}	
		
		}
		######################################## functions related to retrieve catalog ########################
		// Retrieve Catalog from PHP store
		#######################################################################################################
		################################################ XML Serialization #################################### 
		// Creates XML representation of the individual catalog commodity
		#######################################################################################################  
		function catalog_individual_commodity_xml( $catalog, $shiprush_xml="")
		{
			
			if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
			{
				$shiprush_xml->startElement("PBCommodity");
				$shiprush_xml=$this->commodity_xml($catalog, $shiprush_xml);		
				$shiprush_xml->endElement();
				
				return $shiprush_xml;
			}
			else
			{
				return 
				$this->wrap_to_xml( 
				'PBCommodity',
				$this->commodity_xml($catalog));
			}
		}
		################################################ XML Serialization #################################### 
		//  commodity data as XML
		#######################################################################################################    
		function commodity_xml( $catalog, $shiprush_xml="" )
		{
			 if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
		     {		
					
						 //$shiprush_xml->startElement("PBCommodity");
						 
						 $shiprush_xml=$this->xml_write_shiprush_data("Currency",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("ProductType",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("ManagedByWebStore",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Price",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("ParentPBCommodityId",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("CountryOfOrigin",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("UnitsOfMeasureLinear",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("UnitsOfMeasureWeight",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Length",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Width",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Height",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Weight",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Volume",$catalog->catalog_commodity, $shiprush_xml);						 
						 $shiprush_xml=$this->xml_write_shiprush_data("LengthPackaged",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("WidthPackaged",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("HeightPackaged",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("WeightPackaged",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("RowIndex",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("SKU",$catalog->catalog_commodity, $shiprush_xml);
						  $shiprush_xml=$this->xml_write_shiprush_data("MPN",$catalog->catalog_commodity, $shiprush_xml);						 
						 $shiprush_xml=$this->xml_write_shiprush_data("Name",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("VariationsNameValues",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("ExternalID",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Description",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("URL",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("UPC",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("VolumePackaged",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Condition",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Manufacturer",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("ImageURL",$catalog->catalog_commodity, $shiprush_xml);
						 $shiprush_xml=$this->xml_write_shiprush_data("Quantity",$catalog->catalog_commodity, $shiprush_xml);
						
						 
						// $shiprush_xml->endElement();
					
			
					return $shiprush_xml;
			 }
			 else
			 {
					  $this->commodity_xml="";
					  			
						
					  $this->commodity_xml=$this->array_field_to_xml( 'Currency', $catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'ProductType', $catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'ManagedByWebStore', $catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'Price', $catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'CountryOfOrigin', $catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'ParentPBCommodityId', $catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'UnitsOfMeasureLinear' ,$catalog->catalog_commodity ) . 
					  $this->array_field_to_xml( 'UnitsOfMeasureWeight' ,$catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'Length' ,$catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'Width' ,$catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'Height' ,$catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'Weight' ,$catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'Volume' ,$catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'LengthPackaged' ,$catalog->catalog_commodity ) . 
					  $this->array_field_to_xml( 'WidthPackaged' , $catalog->catalog_commodity ) .					  
					  $this->array_field_to_xml( 'HeightPackaged', $catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'WeightPackaged' ,$catalog->catalog_commodity ) . 
					  $this->array_field_to_xml( 'RowIndex' ,$catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'SKU' ,$catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'MPN' ,$catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'Name' ,$catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'VariationsNameValues', $catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'ExternalID' ,$catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'Description' ,$catalog->catalog_commodity ) . 
					  $this->array_field_to_xml( 'URL' , $catalog->catalog_commodity ) .						  
					  $this->array_field_to_xml( 'UPC', $catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'VolumePackaged' ,$catalog->catalog_commodity ) . 
					  $this->array_field_to_xml( 'Condition' ,$catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'Manufacturer' ,$catalog->catalog_commodity ) .
					  $this->array_field_to_xml( 'Quantity' ,$catalog->catalog_commodity ) ;
					
					
					return $this->commodity_xml;
				}
		} 
		######################################## function CommoditiesToXML ############################################	
		//Generate Catalog XML
		##########################################################################################################
		function CommoditiesToXML($catalog)
		{
			if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
			{
				$shiprush_xml=$this->create_xml_obj();	
				$shiprush_xml->startElement("RetrieveCatalogResponse");
				$shiprush_xml->startElement("Commodities");
			}
					
			for($i=0;$i<count ($catalog);$i++)
			{
				if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
				$shiprush_xml=$this->catalog_individual_commodity_xml($catalog[$i],$shiprush_xml);
				else
				$this->complete_catalog_commodity_xml.=$this->catalog_individual_commodity_xml($catalog[$i]);
			}
			
			if(version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
			 {
			 	  $shiprush_xml->endElement(); 
				  $shiprush_xml->endElement(); 
				  $this->Display_XML_Output($shiprush_xml);
			}
			else
			{
				$shiprush_xml=$this->catalog_xml( $this->complete_catalog_commodity_xml );				
				$this->Display_XML_Output($shiprush_xml);
			}	
		
		}
		################################################ XML Serialization #################################### 
		// Creates XML representation of complete catalog
		//RetrieveCatalogResponse element is added
		#######################################################################################################  
		function catalog_xml( $complete_catalog_commodity_xml )
		{
			
			return  '<?xml version="1.0"?>'.$this->wrap_to_xml('RetrieveCatalogResponse',$this->wrap_to_xml('Commodities',$complete_catalog_commodity_xml));
			
			
		}
		############################################## Function ConvertCatalog #################################
			//Conver store catalog to shiprush commodity
			#######################################################################################################
			  function ConvertCatalog($catalog_array)
			  {
				
					//prepare commodity array
					$shiprush_commodities=new stdClass(); 
										
					//commodity details
					$shiprush_commodities->catalog_commodity["Currency"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["Currency"]);
					if(isset($catalog_array->catalog_commodity["ProductType"]))
					$shiprush_commodities->catalog_commodity["ProductType"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["ProductType"]);
					if(isset($catalog_array->catalog_commodity["ManagedByWebStore"]))
					$shiprush_commodities->catalog_commodity["ManagedByWebStore"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["ManagedByWebStore"]);
					else
					$shiprush_commodities->catalog_commodity["ManagedByWebStore"]=1; //By Default, set to 1
					
					$shiprush_commodities->catalog_commodity["Price"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["Price"]);
					$shiprush_commodities->catalog_commodity["ParentPBCommodityId"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["ParentPBCommodityId"]);
					$shiprush_commodities->catalog_commodity["CountryOfOrigin"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["CountryOfOrigin"]);
					$shiprush_commodities->catalog_commodity["UnitsOfMeasureLinear"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["UnitsOfMeasureLinear"]);					
					$shiprush_commodities->catalog_commodity["UnitsOfMeasureWeight"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["UnitsOfMeasureWeight"]);
					$shiprush_commodities->catalog_commodity["Length"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["Length"]);
					$shiprush_commodities->catalog_commodity["Width"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["Width"]);
					$shiprush_commodities->catalog_commodity["Height"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["Height"]);					
					$shiprush_commodities->catalog_commodity["Weight"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["Weight"]);
					$shiprush_commodities->catalog_commodity["Volume"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["Volume"]);
					$shiprush_commodities->catalog_commodity["LengthPackaged"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["LengthPackaged"]);
					$shiprush_commodities->catalog_commodity["WidthPackaged"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["WidthPackaged"]);					
					$shiprush_commodities->catalog_commodity["HeightPackaged"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["HeightPackaged"]);
					$shiprush_commodities->catalog_commodity["WeightPackaged"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["WeightPackaged"]);
					$shiprush_commodities->catalog_commodity["RowIndex"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["RowIndex"]);					
					$shiprush_commodities->catalog_commodity["SKU"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["SKU"]);
					if(isset($catalog_array->catalog_commodity["MPN"]))
					$shiprush_commodities->catalog_commodity["MPN"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["MPN"]);
					$shiprush_commodities->catalog_commodity["Name"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["Name"]);	
					$shiprush_commodities->catalog_commodity["VariationsNameValues"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["VariationsNameValues"]);	
					$shiprush_commodities->catalog_commodity["ExternalID"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["ExternalID"]);					
					$shiprush_commodities->catalog_commodity["Description"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["Description"]);
					$shiprush_commodities->catalog_commodity["URL"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["URL"]);
					$shiprush_commodities->catalog_commodity["UPC"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["UPC"]);
					$shiprush_commodities->catalog_commodity["VolumePackaged"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["VolumePackaged"]);					
					$shiprush_commodities->catalog_commodity["Condition"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["Condition"]);
					$shiprush_commodities->catalog_commodity["Manufacturer"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["Manufacturer"]);
					$shiprush_commodities->catalog_commodity["ImageURL"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["ImageURL"]);
					$shiprush_commodities->catalog_commodity["Quantity"]=$this->MakeXMLSafe($catalog_array->catalog_commodity["Quantity"]);				
					
					
									   
				  return $shiprush_commodities;
			  }
		
		######################################## function ConvertPaymentType ####################################	
		//Convert from string to PaymentType
		##########################################################################################################			
		function ConvertPaymentType($string)
		{
				//- If matches one of our types -> return it
				$PaymentType=-1;
				$string=strtolower($string);
				
				switch($string)
				{
					case 'creditcard': 
					$PaymentType=0;
					break;
					
					case 'personalcheck':
					$PaymentType=1;
					break;
					
					case 'moneyorder':
					$PaymentType=2;
					break;
					
					case 'paypal':
					$PaymentType=3;
					break;
					
					case 'other':
					$PaymentType=4;
					break;

					case 'netterms':
					$PaymentType=7;
					break;
					
					case 'bitcoin':
					$PaymentType=8;
					break;

					case 'ethereum':
					$PaymentType=9;
					break;
				}	
				
				if($PaymentType!=-1)
				{
					return $PaymentType;
				}
				else
				{
					if( strstr($string,"check"))
					{		
						$PaymentType=1;
					}
					elseif( strstr($string,"paypal"))
					{		
						$PaymentType=3;
					}
					elseif(strstr($string,"cc" )|| strstr($string,"visa") || strstr($string,"mc")|| strstr($string,"mastercard")|| strstr($string,"amex")|| strstr($string,"discover")|| strstr($string,"credit"))
					{
						$PaymentType=0;
					}
					elseif(strstr($string,"amazon" ))
					{
						$PaymentType=5;
					}
					elseif(strstr($string,"net terms" ))
					{
						$PaymentType=7;
					}
					elseif(strstr($string,"bank wire" )|| strstr($string,"wire transfer") || strstr($string,"bank transfer")|| strstr($string,"interbank"))
					{
						$PaymentType=6;
					}
					elseif(strstr($string,"cod" )|| strstr($string,"cash on delivery") || strstr($string,"payment on delivery")|| strstr($string,"c.o.d."))
					{
						$PaymentType=10;
					}
	
					if($PaymentType==-1)
					{
						$PaymentType=4;
					
					}
					
					return $PaymentType;
				}				
			
				
		}
		######################################## function ConvertProductType ####################################	
		//Convert from string to ProductType
		##########################################################################################################			
		function ConvertProductType($string)
		{
				$ProductType=0;
				$string=strtolower($string);
				
				switch($string)
				{
					case 'simple': 
					$ProductType=1;
					break;
					
					case 'configurable':
					$ProductType=2;
					break;
					
					case 'downloadable':
					$ProductType=3;
					break;
					
					case 'grouped':
					$ProductType=2;
					break;
					
					case 'bundle':
					$ProductType=2;
					break;
					
					case 'virtual':
					$ProductType=1;
					break;
					
				}	
				return $ProductType;
			}
		

		############################### It will be used to calculate Response length ###################
		function GetResponseLength($response)
		{
			return strlen($response);
							
		}
		
		############################### It will be used to stop SQL Injectiononse ###################
		function MakeSqlSafe($value,$is_number=0)
		{
			
			$value=str_replace("%","",$value);
			
			if(ini_get("magic_quotes_gpc") )
			{
				$value=stripslashes($value);
			}
			
			if(!$is_number )
			{
				if (function_exists('mysql_connect'))
				$value = mysql_real_escape_string($value) ;
				else
				{
					global $cart_db_link;
					$value = mysqli_real_escape_string($cart_db_link, $value) ;
					
				}
				
			}
			else
			{
				$value=(int)$value;
			}
			
			return $value;
		} 
		############################################### Function ConvertWeightUOM() #######################
		//Returns Individual weight unit if available
		#######################################################################################################
		function ConvertWeightUOM($product_weight_unit)
		{
		  if(strtoupper($product_weight_unit)=="LBS" || strtoupper($product_weight_unit)=="KGS" || strtoupper($product_weight_unit)=="OZ"	)
		  {
		    return strtoupper($product_weight_unit);
		  }
		  else return "";
		} 
		############################################### Function ConvertToAcceptedUnitWeight() #######################
		//Converts weight values to desired unit
		#######################################################################################################
		function ConvertToAcceptedUnitWeight($weight,$from_unit)
		{
			$from_unit=trim($from_unit);
			$second_digit_after_decimal=0;
			
			if($from_unit=='oz' || $from_unit=='ozs')
			{
				$decimal_temp = explode('.',$weight);
				if(is_array($decimal_temp))
				{
				  if(count($decimal_temp)>1)
				  {
					$decimal_val=$decimal_temp[1];  
					if(substr( $decimal_val,0,1)> OZ_ROUND_UP_THRESHHOLD)
					$weight+=1;  
					
					$weight=(int)$weight;
				   }
				}
				$converted_weight=($weight*0.0625)."~"."LBS";
			}
			else if($from_unit=='g' || $from_unit=='gm' || $from_unit=='gms')
			{
				$decimal_temp = explode('.',$weight);
				if(is_array($decimal_temp))
				{
				  if(count($decimal_temp)>1)
				  {
					$decimal_val=$decimal_temp[1]; 
					$int_val=$decimal_temp[0];
					
					$first_digit_after_decimal=substr( $decimal_val,0,1);
					
					if(substr( $decimal_val,1,2)!="")
					$second_digit_after_decimal=substr( $decimal_val,1,2);
					
					if($first_digit_after_decimal<9)
					{
						if($second_digit_after_decimal>4)
						$first_digit_after_decimal+=1;
						
						$weight=$int_val.".".$first_digit_after_decimal;
					}
					else
					{
						if($second_digit_after_decimal>4)
						{
							$int_val+=1;
							
							$weight=$int_val;
						}
					}
					
				   }
				 }  
				$converted_weight=($weight*0.001)."~"."KGS";
			}
			else if($from_unit=='kg' || $from_unit=='kgs')
			{
			
				$converted_weight=$weight."~"."KGS";
			}
			else if($from_unit=='lb' || $from_unit=='lbs')
			{
			
				$converted_weight=$weight."~"."LBS";
			}
			else 
			{
			
				$converted_weight=$weight."~Unknown";
			}
			return $converted_weight;
		}
		################################################ Function Convert_Dimension_Unit #######################
		//converts dim unit to desired units
		function Convert_Dimension_Unit($from_unit)
		{
			
			$from_unit=trim($from_unit);
			if($from_unit=="inches")
			$from_unit="in";
			
			if($from_unit=='cm' || $from_unit=='in')
			{
				
				$converted_unit=strtoupper($from_unit);
			}
			else 
			{
			
				$converted_unit="Unknown";
			}
			
			return $converted_unit;
		}
		############################### It will be used to display XML with header ###################
		function Display_XML_Output($shiprush_xml)
		{
			header("Pragma: public");
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");   // Date in the past
			header("Content-type: text/xml");
			header("Content-Disposition: inline; filename=xml_order.xml");
			
			if (version_compare(PHP_VERSION, '5.0.0', '>=') && ForcePHP4Mode == false) 
			echo $shiprush_xml->outputMemory(true);	
			else
			{	
				 $shiprush_xml = $shiprush_xml."\r\n\r\n\r\n\r\n\r\n\r\n\r\n"; 
				header("Content-Length: ".$this->GetResponseLength($shiprush_xml));
				echo $shiprush_xml;	
			}		
			exit;
		
							
		}
		############################### Check for predefined custom errors #########################################
		//Detect low level known errors and raise human friendly version of error as specified in Settings.php file.
		##############################################################################################################
		function CheckAndOverrideErrorMessage($error_string)
		{
			$custom_error_details="";
			if(strstr(strtolower($error_string),"parse error") && strstr(strtolower($error_string),"soap.php"))
			{
				$custom_error_details=cMagento141Problem;
			}
			else if(strstr(strtolower($error_string),"access denied"))
			{
				$custom_error_details=cMagentoSOAPPermissionError;
			}
			else if(strstr(strtolower($error_string),"curl error: ssl certificate problem"))
			{
				$custom_error_details=cMagentoCurlSSLError;
			}
			
			if($custom_error_details!="")
			{
				$this->SetXmlError(1,$custom_error_details);
				exit;
			
			}
							
		}
		
		################################################ Create XML writer obj #################################### 
		//Creates XML writer obj
		###########################################################################################################				
		function create_xml_obj()
		{
			$xml_obj=new XMLWriter();
			$xml_obj->openMemory();
			$xml_obj->setIndent(true);
			$xml_obj->setIndentString(' ');
			$xml_obj->startDocument('1.0','UTF-8');
			return $xml_obj;
			
		}
		################################################ Get value from array ################################
		// Get Value
		#######################################################################################################
		function array_field_to_val( $fieldname, $array )
		{
			if(isset($array[ $fieldname ]))
			return strip_tags($array[$fieldname]);
		}
		########################################### Write XML #################################
		// Write full element tag 
		#######################################################################################################
		function xml_write_shiprush_data($field, $array, $shiprush_xml)
		{
			if($this->array_field_to_val($field,$array)!="")	
			{
				$shiprush_xml->startElement($field);
				$shiprush_xml->writeCdata($this->array_field_to_val($field,$array));
				$shiprush_xml->endElement();
			}
			 
			 return $shiprush_xml;
		 }
}

############################################## Custom Error Handling ######################################
//Function to display back Trace Messages
function ShowDebugBacktrace() 
{
$DebugTraceMsg = '';
$MAXLEN = 64;
$traceArr = debug_backtrace();
array_shift($traceArr);
$tabs = sizeof($traceArr)-1;

foreach($traceArr as $arr)
{
	for ($i=0; $i < $tabs; $i++) $DebugTraceMsg .= ' &nbsp; ';
	$tabs -= 1;
	
	if (isset($arr['class'])) $DebugTraceMsg .= $arr['class'].'.';
	
	$args = array();
	
	if(!empty($arr['args'])) 
	{
		foreach($arr['args'] as $val)
		{
			if (is_null($val)) $args[] = 'null';
			else if (is_array($val)) $args[] = 'Array['.sizeof($val).']';
			else if (is_object($val)) $args[] = 'Object:'.get_class($val);
			else if (is_bool($val)) $args[] = $val ? 'true' : 'false';
			else
			{
				$val = (string) @$val;
				$str = htmlspecialchars(substr($val,0,$MAXLEN));
				if (strlen($val) > $MAXLEN) $str .= '...';
				$args[] = "\"".$str."\"";
			}
		}
	}
	
	$DebugTraceMsg .= $arr['function'].'('.implode(', ',$args).')';
   
	$DebugTraceMsg .= "<br>";
}   

return $DebugTraceMsg;
}

//Function to display error messages along with backtrace
function ShippingZ_Exception_Error_Handler($errno, $errstr, $errfile, $errline ) 
{

if(!defined('E_STRICT')) define('E_STRICT', 2048);
//Display all types of errors including notices
//Check if error is related to ShippingZ Integration Files 
if( $errno!=E_STRICT && (strstr(strtolower($errfile),basename(strtolower($_SERVER['PHP_SELF'])))||strstr(strtolower($errfile),"shippingzsettings.php") || strstr(strtolower($errfile),"shippingzclasses.php") || strstr(strtolower($errfile),"shippingzmessages.php"))) 
{
   //Display error message
   $message="";
   $message .= "\n"."SHIPPINGZCLASSES Version: ".SHIPPINGZCLASSES_VERSION."\n";
   $message .= "Error Type: ".print_r($errno, true)."\n";
   $message .= "File: ".print_r( $errfile, true)."\n";
   $message .= "Line: ".print_r( $errline, true)."\n"."\n";
   $message .= "Message: ".print_r( $errstr, true)."\n"."\n";
   $message .= "Trace: ".ShowDebugBacktrace();
   
	$breaks = array("<br />","<br>","<br/>");  
   $message = str_replace($breaks, "\r\n", $message); 
   $message = str_replace("&nbsp;", "\t", $message);
   echo $message;
   exit; 
}


}

#########################################################################################################################

?>