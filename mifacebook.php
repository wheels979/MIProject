<?php

    include 'lib/facebook-php-sdk-v4-4.0-dev/vendor/autoload.php';
    include 'lib/facebook-php-ads-sdk-master/vendor/autoload.php';
	
	use Facebook\FacebookSession;
    use Facebook\FacebookRedirectLoginHelper;
    use Facebook\FacebookRequest;
    use Facebook\GraphUser;
    use FacebookAds\Object\AdUser;
    use FacebookAds\Object\AdSet;
    use FacebookAds\Object\AdCampaign;
    use FacebookAds\Object\Fields\AdUserFields;
    use FacebookAds\Object\AdAccount;
    use FacebookAds\Object\Fields\AdAccountFields;
    use FacebookAds\Object\Fields\AdCampaignFields;
    use FacebookAds\Object\Fields\AdSetFields;
    use FacebookAds\Api;
	
	FacebookSession::enableAppSecretProof(false);
	
	class FacebookCall
	{
		/* initialize variables */
		function __construct(){
			// app credentials
			$this->app_id		='793828317357661';
			$this->app_secret	='4c894ffa0e31d57968368edefb5d471f';
			$this->app_token 	= 'CAALRZB47eZAl0BAKaImFezg0CK2imeFATLZA31mCsLXcIocGO6DhqGMMXtMCxakmiP95hiIuQgrV1KeaWjpYaDZBEsoZCZAdqswzlqtsgVltP0WamB6Wu1jY4on3DRZCfsnpdbKotQzttFBpfLG2TrKo9vrNCZAJtR08YtkmnXzCXMFLdCLzpsEx';
			
			$this->session = new FacebookSession($this->app_token);
			Api::init($this->app_id, $this->app_secret, trim($this->app_token, ' '));
			
			$this->stack = array(); // holds batches of fb results (500 per batch)
		}
		
		/* call into facebook to get stats */
		public function loadFacebookAdSetData($request){
			
			date_default_timezone_set('Europe/Dublin');
		
			$today = date('Y-m-d H:i:s');
			$yesterday = date('Y-m-d H:i:s',strtotime('last day'));

			$timestamp1 = strtotime($yesterday);
			$timestamp2 = strtotime($today);
					
			if($request == '')
				$request = '/act_101219069986664/adcampaignstats?limit=10000&start_time='.$timestamp1.'&end_time='.$timestamp2;

			$response = (new FacebookRequest($this->session, 'GET', $request))->execute();
			$object = $response->getGraphObject();					
			
			array_push($this->stack, $object->getProperty('data')->asArray());
			
			$paging = $object->getProperty('paging')->asArray();
			
			// look for if there is additional results "next"
			if(array_key_exists('next', $paging)){				
				$this->loadFacebookAdSetData(substr($paging['next'], 31)); //recurse
			}else{
				// if not return
				return;
			}
		}		
		
		
		
		/* get list of all ad sets */
		public function getAdSetNames(){
			// because ad set name is not part of the returned metrics
			// we have to make another call to the api to retrieve all the ad set names
			$response = (new FacebookRequest($this->session, 'GET', '/act_101219069986664/adcampaigns?limit=10000&fields=name'))->execute();
			$object = $response->getGraphObject();
			$myObject = $object->getProperty('data')->asArray();
			
			$adSetName = [];
			// iterate through ids
			foreach($myObject as $key => $value) {
				// id of ad set
				$theValue = $value->id;
				// array of ad set names indexed by
				// ad set id (for retrieving ad set name later)
				$adSetName[$theValue] = $value->name;
			}

			return $adSetName;		
		}
		
		
		
		/* process results from query by batch */
		public function saveDataToDatabase($adSetNames, $resultsArray){
						
			// database credentials
			$dbhost = "localhost";
			$dbuser = "mi_app";
			$dbpassword = "47RWNntahYp8oAj0";
			$dbname = "mi_metrics";
			
			$conn = new mysqli($dbhost, $dbuser, $dbpassword, $dbname) or die ("Could not connect");
			if($conn->connect_errno > 0){
				die('Unable to connect to database [' . $conn->connect_error . ']');
			}
						
		
			for($i=0; $i< count($resultsArray); $i++){
				
				$objectArray = $resultsArray[$i];
				
				foreach($objectArray as $key => $value) {
					
					$adSetId = substr($value->id, 0, 13);					
					$sql = "INSERT INTO metric_data (providerId, promoCode, cost, impressions, clicks, conversions, lastUpdate)
					VALUES (1, '".substr($adSetNames[$adSetId],0,9)."','".$value->spent."','".$value->impressions."','".$value->clicks."','null',now())";
					
					if ($conn->query($sql) === TRUE) {
						echo "New record created successfully";
					} else {
						echo "Error: " . $sql . "<br>" . $conn->error;
					}
				}
				
			}
			
			$conn->close();
		}
		
	}
	
	// instantiate new instance
	$obj = new FacebookCall;
	// query facebook for data
	$obj->loadFacebookAdSetData('');
	//$obj->processBatch($obj->stack);
	$adSetNames = $obj->getAdSetNames();
	$obj->saveDataToDatabase($adSetNames, $obj->stack);




?>