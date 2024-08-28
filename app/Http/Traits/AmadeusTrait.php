<?php
/*
||***********************************************||
|====== Created On 20/09/2021 By HAMID AFRIDI=====|
||***********************************************||
*/
namespace App\Http\Traits;

use App\Models\ApiOffer;
use Illuminate\Support\Facades\Storage;
use App\Models\ApiOfferModel;
use Illuminate\Support\Str;

trait AmadeusTrait {
    protected static $API_KEY='CnaPdDbtluNUGrdZjwJzpleAcCjU97Pe';
    protected static $API_SECRET ="2ujSv6TgILRkJXBt";
    protected static $Provider = '1G';
    protected static $TOKEN_LINK = 'https://test.api.amadeus.com/v1/security/oauth2/token';
    protected static $LINK = 'https://test.api.amadeus.com/v2/shopping/flight-offers?';
    protected static $accessToken = '';

	public static function lowFareSearch($request){
        // return ($request->all());
		// $apiObject = json_decode($request->apiObject);
		if($request->airlines==""){
		    $allowedAirlinesArray = [];
		}
		else{
		    $allowedAirlinesArray = explode(',',$request->airlines);
		}
		$url = 'https://test.api.amadeus.com/v2/shopping/flight-offers?';
		$url .= 'originLocationCode='.strtoupper($request->origin);
		$url .= '&destinationLocationCode='.strtoupper($request->destination);
		$url .= '&departureDate='.date('Y-m-d',strtotime($request->departureDate));
		if($request->tripType =='return'){
			$url .= '&returnDate='.date('Y-m-d',strtotime($request->returnDate));
		}
		$url .= '&adults='.$request->adults;
		$url .= '&children='.$request->children;
		$url .= '&infants='.$request->infants;
		$url .= '&currencyCode=PKR';
		$url .= '&travelClass='.strtoupper($request->cabin);
		if($request->max){
			$url .= '&max='.$request->max;
		}else{
			$url .= '&max=10';
		}
		$accessToken = self::accessToken(self::$API_KEY,self::$API_SECRET);

		Storage::put('Amadeus/flightSearchRequest.json', $url);
		$type = 'GET';
        $res = self::curl_action($type,$url,$accessToken);
		$response = json_decode($res,true);
		if($request->tripType =='return'){
			Storage::put('Amadeus/flightSearchResponseReturn.json', json_encode($response, JSON_PRETTY_PRINT));
		}
		Storage::put('Amadeus/flightSearchResponse.json', json_encode($response, JSON_PRETTY_PRINT));
        // ===========Old Response from storage=============\\
		// $res = Storage::get('Amadeus/flightSearchResponse.json');
		// ===========End Old Response from storage=============\\
		$result = json_decode($res,true);
        
		if(@$result['errors']){
			$error = array();
			$error['error'] = $result['errors'][0]['title'];
			$error['message'] = $result['errors'][0]['detail'];
			return ['status'=> '400' , 'msg' => $error];
		}

        $allowedAirlinesArray = array();

		$finalData = self::makeResponse($result,$request,$accessToken, $allowedAirlinesArray);
		return ['status'=> '200' , 'msg' => $finalData];
	}
	public static function lowFareSearchMulti($request){
		$apiObject = json_decode($request->apiObject);
		if($request->airlines==""){
		    $allowedAirlinesArray = [];
		}
		else{
		    $allowedAirlinesArray = explode(',',$request->airlines);
		}
		$form_data = array();
		$origin= array();
		$travelers = array();

		foreach($request->leg as $key => $leg){
			$origin[$key]['id'] = $key+1;
			$origin[$key]['originLocationCode'] = $leg['origin'];
			$origin[$key]['destinationLocationCode'] = $leg['destination'];
			$origin[$key]['departureDateTimeRange']['date'] = date('Y-m-d',strtotime($leg['departureDate']));
		}

		$i = 0;
		$x = 0;
		while ($x < $request->adults) {
			$travelers[$i]['id'] = $i+1;
			$travelers[$i]['travelerType'] = 'ADULT';
			// $travelers[$i]['fareOptions'] = array('STANDARD');
			$i++;
			$x++;
		}
		$x = 0;
		while ($x < $request->children) {
			$travelers[$i]['id'] = $i+1;
			$travelers[$i]['travelerType'] = 'CHILD';
			$i++;
			$x++;
		}
		$x = 0;
		while ($x < $request->infants) {
			$travelers[$i]['id'] = $i+1;
			$travelers[$i]['associatedAdultId'] = $x+1;
			$travelers[$i]['travelerType'] = 'HELD_INFANT';
			$i++;
			$x++;
		}


		$form_data['originDestinations'] = $origin;
		$form_data['travelers'] = $travelers;
		$form_data['sources'] = array(
			0 => 'GDS'
		);
		if($request->max){
			$form_data['searchCriteria']['maxFlightOffers'] = $request->max;
		}else{
			$form_data['searchCriteria']['maxFlightOffers'] = 10;
		}
		$form_data['currencyCode'] = $apiObject->currency;


		// return $form_data;
		$accessToken = self::accessToken($apiObject->apiKey,$apiObject->apiSecret);
		$res = self::curl_action_multi(json_encode($form_data),$accessToken);
		Storage::put('Amadeus/flightSearchResponseMulti.json', $res);
		$result = json_decode($res,true);
		// return $result;

		if(@$result['errors']){
			$error = array();
			$error['error'] = $result['errors'][0]['title'];
			$error['message'] = $result['errors'][0]['detail'];
			return $error;
		}

		$finalData = self::makeResponse($result,$request,$accessToken, $allowedAirlinesArray);
		return ['status'=> '200' , 'msg' => $finalData];

	}
	public static function makeResponse($result,$request,$accessToken,$allowedAirlinesArray=[]){
		$apiObject = json_decode($request->apiObject);
		$finalData = array();
		$price = 0;
		foreach ($result['data'] as $key => $val) {

			$finalData[$key]['api'] = 'Amadeus';
			// foreach($result['dictionaries']['carriers'] as $air_cod => $air_name){
			// 	$finalData[$key]['MarketingAirline']['Airline'] = $air_cod;
			// }
			$finalData[$key]['MarketingAirline']['FareRules'] = 'NA';

			$Flights = array();
			foreach($val['itineraries'] as $itn_key => $itinery){
				// $departureTime = '';
				// $arrivalTime = '';
				$segments = array();
				$PassengerFares = array();
				foreach($itinery['segments'] as $seg_key => $seg){
					$finalData[$key]['MarketingAirline']['Airline'] = $seg['carrierCode'];
					// if(count($allowedAirlinesArray)>0 && in_array($seg['carrierCode'], $allowedAirlinesArray)){
					//     $is_allowed = true;
					// }
					// else if(count($allowedAirlinesArray)==0){
					//     $is_allowed = true;
					// }
					if($seg_key == 0){
						$departureTime = $seg['departure']['at'];
					}
					$arrivalTime = $seg['arrival']['at'];
					$segments[$seg_key]['Duration'] = self::getDurationInMinutes($seg['duration']);
					$segments[$seg_key]['OperatingAirline']['Code'] = $seg['carrierCode'];
					$segments[$seg_key]['OperatingAirline']['FlightNumber'] = $seg['number'];
					$segments[$seg_key]['MarketingAirline']['Code'] = $seg['carrierCode'];
					$segments[$seg_key]['MarketingAirline']['FlightNumber'] = $seg['number'];
					$segments[$seg_key]['Departure']['LocationCode'] = $seg['departure']['iataCode'];
					$segments[$seg_key]['Departure']['DepartureDateTime'] = $seg['departure']['at'];
					$segments[$seg_key]['Arrival']['LocationCode'] = $seg['arrival']['iataCode'];
					$segments[$seg_key]['Arrival']['ArrivalDateTime'] = $seg['arrival']['at'];

					$BaggagePolicy = array();
					// dd($val['travelerPricings']);
					foreach($val['travelerPricings'] as $trav_key => $traveler){
						$price =
						$trav_type = '';
						if($traveler['travelerType']== 'ADULT'){
							$trav_type = 'Adult';
						}
						if($traveler['travelerType']== 'CHILD'){
							$trav_type = 'Child';
						}
						if($traveler['travelerType']== 'HELD_INFANT'){
							$trav_type = 'Infant';
						}
						if($trav_type != ''){
							foreach($traveler['fareDetailsBySegment'] as $bagg){
								if($bagg['segmentId']== $seg['id']){
									if(@$bagg['includedCheckedBags']['quantity']){
										$Baggage['Weight'] = @$bagg['includedCheckedBags']['quantity'];
										$Baggage['Unit'] = 'Piece(s)';
										$Baggage['PaxType'] = $trav_type;
										$segments[$seg_key]['Cabin'] = $bagg['cabin'];
									}else{
										$Baggage['Weight'] = @$bagg['includedCheckedBags']['weight'];
										$Baggage['Unit'] = @$bagg['includedCheckedBags']['weightUnit'];
										$Baggage['PaxType'] = $trav_type;
										$segments[$seg_key]['Cabin'] = $bagg['cabin'];
									}
									$segments[$seg_key]['EquipType'] = '';
								}
							}
							array_push($BaggagePolicy,$Baggage);
						}
					}
				}

				$passengerTypes = [
					'ADULT' => 'Adult',
					'CHILD' => 'Child',
					'HELD_INFANT' => 'Infant'
				];
				$passengerQuantityArray = [
					'ADULT' => $request->adults,
					'CHILD' => $request->children,
					'HELD_INFANT' => $request->infants
				];
				
				// dd($val['travelerPricings']);
				foreach ($val['travelerPricings'] as $passPriceKey => $pass_price) {
					$taxFare = $pass_price['price']['total'] - $pass_price['price']['base'];
					$travelerType = $pass_price['travelerType'];
				
					if (array_key_exists($travelerType, $passengerTypes)) {
						$paxType = $passengerTypes[$travelerType];
						// Check if the PaxType already exists in the PassengerFares array
						$paxTypeExists = false;
						foreach ($PassengerFares as $fare) {
							if ($fare['PaxType'] === $paxType) {
								$paxTypeExists = true;
								break;
							}
						}
					
						// Only add to PassengerFares if PaxType does not already exist
						if (!$paxTypeExists) {
							$PassengerFares[$passPriceKey] = [
								'PaxType' => $paxType,
								'Currency' => 'PKR',
								'Quantity' => $passengerQuantityArray[$travelerType],
								'BasePrice' => floatval($pass_price['price']['base']),
								'Taxes' => $taxFare,
								'Fees' => 0,
								'ServiceCharges' => 0,
								'TotalPrice' => floatval($pass_price['price']['total']),
							];
						}
					}
				}

				$Flights[$itn_key]['Segments'] = $segments;
				$Flights[$itn_key]['TotalDuration'] = self::getDurationInMinutes($itinery['duration']);
				$Flights[$itn_key]['NonRefundable'] = false;
				$Flights[$itn_key]['MultiFares'] = false;
				$Flights[$itn_key]['Fares'][0]['RefID'] = Str::uuid();
				$Flights[$itn_key]['Fares'][0]['Currency'] = 'PKR';
				$Flights[$itn_key]['Fares'][0]['BaseFare'] = floatval($val['price']['base']);
				$Flights[$itn_key]['Fares'][0]['Taxes'] = floatval($val['price']['grandTotal']) - floatval($val['price']['base']);
				$Flights[$itn_key]['Fares'][0]['TotalFare'] = floatval($val['price']['grandTotal']) + 0;
				$Flights[$itn_key]['Fares'][0]['BillablePrice'] = floatval($val['price']['grandTotal']) + 0;
				$Flights[$itn_key]['Fares'][0]['BaggagePolicy'] = $BaggagePolicy;
				$Flights[$itn_key]['Fares'][0]['PassengerFares'] = $PassengerFares;

				// $finalData[$key]['Flights'][$itn_key]['Fares'][0]['RefID'] = Str::uuid();
				// $finalData[$key]['Flights'][$itn_key]['Fares'][0]['Currency'] = 'PKR';
				// $finalData[$key]['Flights'][$itn_key]['Fares'][0]['BaseFare'] = floatval($val['price']['base']);
				// $finalData[$key]['Flights'][$itn_key]['Fares'][0]['Taxes'] = floatval($val['price']['grandTotal']) - floatval($val['price']['base']);
				// $finalData[$key]['Flights'][$itn_key]['Fares'][0]['TotalFare'] = floatval($val['price']['grandTotal']) + 0;
				// $finalData[$key]['Flights'][$itn_key]['Fares'][0]['BillablePrice'] = floatval($val['price']['grandTotal']) + 0;
				// $finalData[$key]['Flights'][$itn_key]['Fares'][0]['BaggagePolicy'] = $BaggagePolicy;

				
				
				$finalData[$key]['Flights'] = $Flights;
			}

				

			$val['bearerKey'] = $accessToken;
			$apiOffer = new ApiOffer();
			$apiOffer->api = "Amadeus";
			$apiOffer->data = json_encode($val);
			$apiOffer->ref_key = Str::uuid();
			$apiOffer->finaldata = $finalData[$key];
			$apiOffer->timestamp = time();
			$apiOffer->query = json_encode($request->except('apiObject'));
			$apiOffer->save();
			// $finalData[$key]['api_offer_id'] = $apiOffer->id;
			$finalData[$key]['itn_ref_key'] = $apiOffer->ref_key;
			    
		}
		return $finalData;
	}
	public static function createPnr($requestData){
		$api_offer = ApiOffer::where('ref_key',$requestData['itn_ref_key'])->first();
        $fareData = $api_offer->data;
		
		$res = $fareData;
		$passengers = $requestData['passengers'];
		
		
		$accessToken = $res['bearerKey'];
		unset($res['bearerKey']);
		$travelers = array();
		$phone = array();
		$documents = array();

		$i = 0;
		
		foreach($passengers as $passKey => $pass){
			$StaringDate = date('Y-m-d',strtotime($pass['document_expiry_date']));
			$issue = date("Y-m-d", strtotime(date("Y-m-d", strtotime($StaringDate)) . " - 10 year"));

			$travelers[$i]['id'] 			= $i+1;
			$travelers[$i]['dateOfBirth'] 		= date('Y-m-d',strtotime($pass['dob']));
			$travelers[$i]['name']['firstName'] = $pass['name'];
			$travelers[$i]['name']['lastName'] 	= $pass['sur_name'];

			$travelers[$i]['gender'] = ($pass['passenger_gender'] == 'M') ? 'MALE' : 'FEMALE';

			$travelers[$i]['contact']['emailAddress'] = $requestData['customer_email'];
			$phone['deviceType'] 	= 'MOBILE';
			$phone['countryCallingCode'] 	= countryDialCode($requestData['customer_country']);
			$phone['number'] = str_replace('92', '', $requestData['customer_phone']);

			$travelers[$i]['contact']['phones'][] = $phone;

			$documents['documentType'] = 'PASSPORT';
			$documents['birthPlace'] = $pass['nationality'];
			$documents['issuanceLocation'] = $pass['nationality'];
			$documents['issuanceDate'] = $issue;
			$documents['number'] = $pass['document_number'];
			$documents['expiryDate'] = date('Y-m-d',strtotime($pass['document_expiry_date']));
			$documents['issuanceCountry'] = 'PK';
			$documents['validityCountry'] = 'PK';
			$documents['nationality'] = 'PK';
			$documents['holder'] = true;

			$travelers[$i]['documents'][] = $documents;

			$i++;
		}

		$travelers1 = json_encode($travelers);

		// dd($travelers1);
		$flighData = '{
		  "data": {
		    "type": "flight-order",
		    "flightOffers": ['
		        .json_encode($res).
		    '],
		    "travelers": '.$travelers1.',
		    "remarks": {
		      "general": [
		        {
		          "subType": "GENERAL_MISCELLANEOUS",
		          "text": "ONLINE BOOKING FROM INCREIBLE VIAJES"
		        }
		      ]
		    },
		    "ticketingAgreement": {
		      "option": "DELAY_TO_CANCEL",
		      "delay": "6D"
		    },
		    "contacts": [
		      {
		        "addresseeName": {
		          "firstName": "Hamid",
		          "lastName": "Afridi"
		        },
		        "companyName": "DevHubx",
		        "purpose": "STANDARD",
		        "phones": [
		          {
		            "deviceType": "MOBILE",
		            "countryCallingCode": "'.countryDialCode($requestData['customer_country']).'",
		            "number": "'.str_replace('92', '', $requestData['customer_phone']).'"
		          }
		        ],
		        "emailAddress": "'.$requestData['customer_email'].'",
		        "address": {
		          "lines": [
		            "Deans Trade Center Peshawer"
		          ],
		          "postalCode": "44000",
		          "cityName": "Pakistan",
		          "countryCode": "PK"
		        }
		      }
		    ]
		  }
		}';
		$apiRequestArray = json_encode(json_decode($flighData, true), JSON_PRETTY_PRINT);
		Storage::put('Amadeus/pnr/pnrReq_' . date('Y-m-d-H-i-s') . '.json', $apiRequestArray);

		/*************************************************\
		*=========================API Call****************|
		\*************************************************/
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://test.api.amadeus.com/v1/booking/flight-orders',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>$apiRequestArray,
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Bearer '.$accessToken,
		    'Content-Type: application/json'
		  ),
		));

		$resp = curl_exec($curl);
		curl_close($curl);
		Storage::put('Amadeus/pnr/pnrRes_' . date('Y-m-d-H-i-s') . '.json', json_encode(json_decode($resp, true), JSON_PRETTY_PRINT));
		////////////////////////////End API Call//////////////////////
		// $resp = Storage::get('Amadeus/pnr/pnrRes_2024-08-01-04-21-42.json');
		

		$response = json_decode($resp,true);
		if(@$response['errors']){
			return ['status'=> '400' ,'error' => $response['errors'][0]['detail'], 'response' => $response['errors']];
		}

		$TotalAmount = 0;
		if(@$response['data']['flightOffers']){
			$TotalAmount = @$response['data']['flightOffers'][0]['price']['grandTotal'];
			$lastTicketingDate = @$response['data']['flightOffers'][0]['lastTicketingDate'];
			$airlinePNR = '';
		}
		$pnr = $response['data']['associatedRecords'][0]['reference'];

		return [
			'status' => '200', 
			'pnr' => $pnr, 
			'TotalAmount' => $TotalAmount, 
			'airlinePNR' => $airlinePNR, 
			'response' => $response, 
			'last_ticketing_date' => @$lastTicketingDate
		];
		// return ['status'=> '200' , 'pnr'=> $pnr, 'response' => $response];
		
	}
	public static function issueTicket($request){
		$customer_data = json_decode($request['customer_data'],true);
		// dd($customer_data,$request);
		$passData = $customer_data['passengers'];
		$ticketData = array();
		$TicketNumber = '/TKT172-';
		$response = json_decode($request->pnrResponse);
		foreach($passData as $key => $pass){
				$ticketData[$key]['passenger_type'] = $pass['passenger_type'];
				$ticketData[$key]['name'] = $pass['name'];
				$ticketData[$key]['sur_name'] = $pass['sur_name'];
				$ticketData[$key]['TicketNumber'] = '"TWA"'.$TicketNumber.rand(0,9999999999);
		}
		if($TicketNumber){
			$finalResponse = ['status'=> '200' , 'msg' => json_encode($response) ,  'ticketData'=> $ticketData];
		}else {
			$finalResponse = ['status'=> '500' , 'error' => 'Something went wrong......'];
		}
		return $finalResponse;
	}
	/*
	*||||||||||||||||||||| Curl and other functions ||||||||||||||||||||
	*/
	public static function curl_action($type,$url,$accessToken)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => $type,
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Bearer '.$accessToken
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

	public static function curl_action_multi($form_data,$accessToken)
	{

		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://test.api.amadeus.com/v2/shopping/flight-offers',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => $form_data,
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Bearer '.$accessToken,
		    'Content-Type: application/json'
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

	public static function accessToken($key,$secret){
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://test.api.amadeus.com/v1/security/oauth2/token',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => 'grant_type=client_credentials&client_id='.$key.'&client_secret='.$secret,
		  CURLOPT_HTTPHEADER => array(
		    'Content-Type: application/x-www-form-urlencoded'
		  ),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		$res = json_decode($response,true);
		// dd($res['access_token']);
		return $res['access_token'];
	}
	public static function getDuration($d1, $d2){
		$date1 = str_replace("T", " ", $d1);
		$date1 = strtotime($date1);
		$date2 = str_replace("T", " ", $d2);
		$date2 = strtotime($date2);
		$diff = ($date1 - $date2) / 60;
		$h = floor($diff / 60);
		$m = $diff % 60;
		$hours = $h;
		$minutes = $m;
		$duration = $hours . " Hours " . $minutes . " Minutes";
		return $duration;
	}
	public static function getDurationInMinutes($time){
		preg_match('/PT(\d+H)?(\d+M)?/', $time, $matches);

		$totalMinutes = 0;
	
		if (!empty($matches[1])) {
			$hours = (int) rtrim($matches[1], 'H');
			$totalMinutes += $hours * 60;
		}
	
		if (!empty($matches[2])) {
			$minutes = (int) rtrim($matches[2], 'M');
			$totalMinutes += $minutes;
		}
	
		return $totalMinutes;
	}

  	public static function fareRules($offer,$request){
  		$apiObject = json_decode($request->apiObject);

  		$res = json_decode($offer->data,true);
		$passData = json_decode($offer->passengerData,true);
		
		$accessToken = $res['bearerKey'];
		unset($res['bearerKey']);

  		$post_fields = '{
		  "data": {
		    "type": "flight-offers-pricing",
		    "flightOffers": [
		        '.json_encode($res).'
		    ]
		  },
		  "currencyCode":"'.$apiObject->currency.'"
		}';


  		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://test.api.amadeus.com/v1/shopping/flight-offers/pricing?include=detailed-fare-rules',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => $post_fields,
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Bearer '.$accessToken,
		    'Content-Type: application/json'
		  ),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		Storage::put('Amadeus/fareRules/fareRule.json', $response);

		// $response = Storage::get('Amadeus/fareRules/fareRule.json');

		$res = json_decode($response,true);
		if(@$res['errors']){
			$fare_rule = 'No Fare Rule Available';
		}else{
			// echo "<pre>";
			// print_r($res['included']['detailed-fare-rules']);die;

			$fareArray = array();
			if(@$res['included']['detailed-fare-rules']){
				$fare_rule = '<div class="addReadMore">';
				foreach($res['included']['detailed-fare-rules'] as $key => $fare){
					$fare_rule .= '<h4> Flight '.$key.'</h4>';
					foreach($fare['fareNotes']['descriptions'] as $desc){
						$fare_rule .= '<h5>'.$desc['descriptionType'].'</h5>';
						$fare_rule .= '<p>'.strtolower(str_replace('-','',$desc['text'])).'</p>';
					}
				}
				$fare_rule .= '</div>';
			}else{
				$fare_rule = 'No Fare Rule Available';
			}
		}
		$fareArray[0] = $fare_rule;

		// echo "<pre>";
		// print_r($fareArray);die;
		return $fareArray;
  	}
}
