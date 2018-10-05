<?php

// Title     	: eBay Trading API
// File         : eBayTradingAPI.class.php
// Author    	: M. Langham
// Date      	: 2018/01/24
// Version   	: 1.0.0
// Purpose   	: Class of eBay Trading API functions.
// Format       : XML strings sent using cURL POST requests to the eBay API endpoint.
// Support      : https://developer.ebay.com/Devzone/XML/docs/Reference/eBay/index.html
//                These functions require an eBay developer account and supporting
//                login info and keys.
//
///////////////////////////////////////////////////////////////////////////////////////


	class eBayTradingAPI {

		/* variable init */
		    private $site_id      = 100;      // eBay site code to submit queries (100 = eBay Motors)
			private $compat_level = 967;      // mininum version of the eBay Trading API being used
    		private $app_id       = "???";    // App ID (Client ID) from your eBay developer account
    		private $dev_id       = "???";    // Dev ID from your eBay developer account
    		private $cert_id      = "???";    // Cert ID (Client Secret) from your eBay developer account
			private $auth_token   = "???";    // User Token from your eBay developer account
    		private $version      = '1.13.0'; // API version
            private $uri_trading  = "https://api.ebay.com/ws/api.dll";      // API service endpoint
        /* END init */



    	/* Sets the eBay version to the current API version on each newly-created object */
    	public function __construct(){
        	//$this->version = $this->getCurrentVersion();
    	}



    	/* cURL 
    	// Connect using client URL to run POST requests.
    	// @input  $api_endpoint  (URL string)  Target of the request
        // @input  $callname      (string)      Trading API function being used
        // @input  $postvals      (XML string)  XML structured data being submitted
        // @input  $return_format (string)      Option to format the return data in XML, JSON, or PHP
        //
        // @output (dependant)    (various)     Returns call data upon success. Default format is a PHP array.
        //                                      Call error will return eBay error data.
    	*/
    	private function cURL($api_endpoint, $callname, $postvals, $return_format = "PHP") {

    		// assemble headers
			$headers = array
			(
			'X-EBAY-API-COMPATIBILITY-LEVEL: '.$this->compat_level,
			'X-EBAY-API-DEV-NAME: '.$this->dev_id,
			'X-EBAY-API-APP-NAME: '.$this->app_id,
			'X-EBAY-API-CERT-NAME: '.$this->cert_id,
			'X-EBAY-API-CALL-NAME: '.$callname, 
			'X-EBAY-API-SITEID: '.$this->site_id
			);

    		// Send request to eBay and load response in $response
			$conn_curl = curl_init();
			curl_setopt($conn_curl, CURLOPT_URL, $api_endpoint);
			curl_setopt($conn_curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($conn_curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($conn_curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($conn_curl, CURLOPT_POST, 1);
			curl_setopt($conn_curl, CURLOPT_POSTFIELDS, $postvals);
			curl_setopt($conn_curl, CURLOPT_RETURNTRANSFER, 1);
			$curl_response = curl_exec($conn_curl);
			curl_close($conn_curl);

			// convert XML string to an XML object
			$XML = simplexml_load_string($curl_response);

			// convert the XML object to a JSON object
			$JSON = json_encode($XML);

			// convert the JSON object to a PHP associative array (key=>'value')
			$PHP = json_decode($JSON, TRUE);


            switch($return_format) {
                case "XML":
                    return $XML;    // return XML format
                break;

                case "JSON":
                    return $JSON;    // return the JSON array
                break;

                case "PHP":
                    return $PHP;    // return the PHP array
                break;

                default:
                    return $PHP;    // return the PHP array
            }

    	} /* END cURL */



        /* AddItem
            Create and publish a new item listing and list it on an eBay site
        */
        public function AddItem($eBay_AddItem = array()) {
            
            // decode description from URL format
            $DecodedDescription = urldecode($eBay_AddItem['Description']);

            // encode description as HTML using CDATA for passing with XML
            $EncodedCustomDescription = "<![CDATA[".$CustomDescription."]]>";

            // Begin assemblng XML request string
            $xml_AddItem = "
                <?xml version=\"1.0\" encoding=\"utf-8\" ?>
                <AddItemRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                    <RequesterCredentials>
                        <eBayAuthToken>".$this->auth_token."</eBayAuthToken>
                    </RequesterCredentials>
                    <ErrorLanguage>en_US</ErrorLanguage>
                    <WarningLevel>High</WarningLevel>
                    <Item>
                        <SKU>".$eBay_AddItem['SKU']."</SKU>
                        <StartPrice>".$eBay_AddItem['StartPrice']."</StartPrice>
                        <BestOfferDetails>
                            <BestOfferEnabled>".$eBay_AddItem['BestOfferEnabled']."</BestOfferEnabled>
                        </BestOfferDetails>
                        <Quantity>".$eBay_AddItem['Quantity']."</Quantity>
                        <Storefront> 
                            <StoreCategoryID>".$eBay_AddItem['StoreCategoryID']."</StoreCategoryID>
                            <StoreCategory2ID>".$eBay_AddItem['StoreCategory2ID']."</StoreCategory2ID>
                        </Storefront>
                        <Title>".$eBay_AddItem['Title']."</Title>
                        <PrimaryCategory>
                            <CategoryID>".$eBay_AddItem['CategoryID']."</CategoryID>
                        </PrimaryCategory>
                        <Description>".$EncodedDescription."</Description>
                        <ConditionID>".$eBay_AddItem['ConditionID']."</ConditionID>
                        <Country>".$eBay_AddItem['Country']."</Country>
                        <Currency>".$eBay_AddItem['Currency']."</Currency>
                        <DispatchTimeMax>".$eBay_AddItem['DispatchTimeMax']."</DispatchTimeMax>
                        <ListingDuration>".$eBay_AddItem['ListingDuration']."</ListingDuration>
                        <ListingType>".$eBay_AddItem['ListingType']."</ListingType>
                        <PaymentMethods>".$eBay_AddItem['PaymentMethods']."</PaymentMethods>
                        <PayPalEmailAddress>".$eBay_AddItem['PayPalEmailAddress']."</PayPalEmailAddress>
                        <PictureDetails>
                ";
                
            // loop in PictureURLs if they were added / exist
            for ($t=0; $t<12; $t++) {
                // check img is a JPEG
                if(substr($eBay_AddItem['PictureURL'.$t], -4) == '.JPG' || substr($eBay_AddItem['PictureURL'.$t], -4) == '.jpg') {
                    $xml_AddItem .= "<PictureURL>".$eBay_AddItem['PictureURL'.$t]."</PictureURL>";
                }
            }

            $xml_AddItem .= "
                        </PictureDetails>
                        <PostalCode>".$eBay_AddItem['PostalCode']."</PostalCode>
                        <ShippingDetails>
                            <ShippingType>FlatDomesticCalculatedInternational</ShippingType>
                            <CalculatedShippingRate>
                                <OriginatingPostalCode>".$eBay_AddItem['PostalCode']."</OriginatingPostalCode>
                                <InternationalPackagingHandlingCosts currencyID='USD'>".$eBay_AddItem['InternationalPackagingHandlingCosts']."</InternationalPackagingHandlingCosts>
                            </CalculatedShippingRate>
                            <ShippingServiceOptions>
                                <ShippingService>".$eBay_AddItem['ShippingService']."</ShippingService>
                                <ShippingServicePriority>".$eBay_AddItem['ShippingServicePriority']."</ShippingServicePriority>
                                <FreeShipping>".$eBay_AddItem['FreeShipping']."</FreeShipping>
                            </ShippingServiceOptions>";

                            if($eBay_AddItem['ship_intl'] == '1') {     // non-eBay variable, user option
                            
                                $xml_AddItem .= "
                                <InternationalShippingServiceOption>
                                    <ShippingService>USPSPriorityMailInternational</ShippingService>
                                    <ShippingServicePriority>1</ShippingServicePriority>
                                    <ShipToLocation>Worldwide</ShipToLocation>
                                </InternationalShippingServiceOption>"; 
                            }

                        $xml_AddItem .= "
                            </ShippingDetails>
                            <ShippingPackageDetails>
                            <MeasurementUnit>English</MeasurementUnit>
                            <WeightMajor unit='lbs'>".$eBay_AddItem['WeightMajor']."</WeightMajor>
                            <WeightMinor unit='oz'>".$eBay_AddItem['WeightMinor']."</WeightMinor>
                            <PackageDepth unit='inches'>".$eBay_AddItem['PackageDepth']."</PackageDepth>
                            <PackageLength unit='inches'>".$eBay_AddItem['PackageLength']."</PackageLength>
                            <PackageWidth unit='inches'>".$eBay_AddItem['PackageWidth']."</PackageWidth>
                            <ShippingIrregular>".$eBay_AddItem['ShippingIrregular']."</ShippingIrregular>
                            <ShippingPackage>".$eBay_AddItem['ShippingPackage']."</ShippingPackage>
                        </ShippingPackageDetails>
                        <ReturnPolicy>
                            <ReturnsAcceptedOption>".$eBay_AddItem['ReturnsAcceptedOption']."</ReturnsAcceptedOption>
                            <RefundOption>".$eBay_AddItem['RefundOption']."</RefundOption>
                            <ReturnsWithinOption>".$eBay_AddItem['ReturnsWithinOption']."</ReturnsWithinOption>
                            <ShippingCostPaidByOption>".$eBay_AddItem['ShippingCostPaidByOption']."</ShippingCostPaidByOption>
                        </ReturnPolicy>
                    <Site>".$eBay_AddItem['Site']."</Site>
                    </Item>    
                </AddItemRequest>
                ";

            // make the cURL call
            $AddItem = $this->cURL($this->uri_trading, "AddItem", $xml_AddItem);

            // return success XML data, or call error from eBay
            return $AddItem;

        }



        /* GetCategories
        // Retrieve the latest category hierarchy for the eBay site 
        //   specified in the CategorySiteID property.
        // @input:  $CategorySiteID 
        // @input:  $CategoryParentID  (bool)
        //
        // @output: $GetCategories     (array) of eBay listing categories and info
        */
        public function GetCategories($CategorySiteID, $CategoryParentID = false) {

            // Begin assembling XML query string
            $XML_GetCategories = "
                <?xml version=\"1.0\" encoding=\"utf-8\" ?>
                <GetCategoriesRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                    <RequesterCredentials>
                        <eBayAuthToken>".$this->auth_token."</eBayAuthToken>
                    </RequesterCredentials>
                    <CategorySiteID>".$CategorySiteID."</CategorySiteID>
                    <DetailLevel>ReturnAll</DetailLevel>";
            if($CategoryParentID) {
                $XML_GetCategories .= "<CategoryParent>".$CategoryParentID."</CategoryParent>";
            }
            else { 
                $XML_GetCategories .= "<LevelLimit>2</LevelLimit>";
            }

            $XML_GetCategories .= " 
                </GetCategoriesRequest>
            ";

            // make the cURL call
            $GetCategories = $this->cURL($this->uri_trading, "GetCategories", $XML_GetCategories, "PHP");

            // return success XML data, or call error from eBay
            return $GetCategories;

        }



    	/* GetMyeBaySelling */ 
    	// Retrieve information from the All Selling section 
    	//   of the authenticated user's My eBay account.
    	// @param $List (string) Active, Sold, Unsold
    	//
    	public function GetMyeBaySelling($List) {

			// assemble XML request string
			$xml_GetMyeBaySelling = "
				<?xml version=\"1.0\" encoding=\"utf-8\" ?>
				<GetMyeBaySellingRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
    				<RequesterCredentials>
    					<eBayAuthToken>".$this->auth_token."</eBayAuthToken>
    				</RequesterCredentials>
                ";

            /* List selection ActiveList, SoldList, UnsoldList */
                switch($List) {
                    case "Active":
                        $xml_GetMyeBaySelling .= "<ActiveList><Include>true</Include></ActiveList>";
                    break;

                    case "Sold":
                        $xml_GetMyeBaySelling .= "<SoldList><Include>true</Include></SoldList>";
                    break;

                    case "Unsold":
                        $xml_GetMyeBaySelling .= "<UnsoldList><Include>true</Include></UnsoldList>";
                    break;
                }

            $xml_GetMyeBaySelling .= "      
  				</GetMyeBaySellingRequest>
  			";

  			$GetMyeBaySelling = $this->cURL($this->uri_trading, "GetMyeBaySelling", $xml_GetMyeBaySelling);

			return $GetMyeBaySelling;

    	}



    	/* GetItem */
        // Retrieve the data for a single item listed on an eBay site. 
        // @input  $ItemID  (string)  The eBay item ID of the desired listing 
        //                              of which to retrieve data
        //
    	public function GetItem($ItemID) {

			// assemble XML request string
			$XML_GetItem = "
				<?xml version=\"1.0\" encoding=\"utf-8\" ?>
				<GetItemRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
    				<RequesterCredentials>
    					<eBayAuthToken>$this->auth_token</eBayAuthToken>
    				</RequesterCredentials>
  					<ItemID>$ItemID</ItemID>
  				</GetItemRequest>
  			";

  			$GetItem = $this->cURL($this->uri_trading, "GetItem", $XML_GetItem);

			return $GetItem;

		}



        /* GetStore */
        // Retrieve configuration information for the eBay store 
        //   owned by the user specified with UserID.
        //
        public function GetStore() {

            // assemble XML request string
            $XML_GetStore = "
                <?xml version=\"1.0\" encoding=\"utf-8\" ?>
                <GetStoreRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                    <RequesterCredentials>
                        <eBayAuthToken>$this->auth_token</eBayAuthToken>
                    </RequesterCredentials>
                    <CategoryStructureOnly>true</CategoryStructureOnly>
                </GetStoreRequest>
            ";

            $GetStore = $this->cURL($this->uri_trading, "GetStore", $XML_GetStore);

            return $GetStore;

        }


	}  /* END eBayTradingAPI */


?>