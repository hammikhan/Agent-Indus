<?php

namespace App\Http\Traits\APIS;

use App\Models\ApiOffer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

trait HititTrait
{
    public static function lowFareSearch($request)
    {
        $passengerTypeQuantityList = array();
	    if ($request['adults'] > 0) {
	        $passengerTypeQuantityList[] = array(
	            'hasStrecher' => '',
	            'passengerType' => array(
	                'code' => 'ADLT'
	            ),
	            'quantity' => $request['adults']
	        );
	    }

	    if ($request['children'] > 0) {
	        $passengerTypeQuantityList[] = array(
	            'hasStrecher' => '',
	            'passengerType' => array(
	                'code' => 'CHLD'
	            ),
	            'quantity' => $request['children']
	        );
	    }

	    if ($request['infants'] > 0) {
	        $passengerTypeQuantityList[] = array(
	            'hasStrecher' => '',
	            'passengerType' => array(
	                'code' => 'INFT'
	            ),
	            'quantity' => $request['infants']
	        );
	    }
		
		if ($request['tripType'] == "return") {
			$trip_type = 'ROUND_TRIP';
			$originDestinationInformationList = array(
				[
					'dateOffset' => '0',
					'departureDateTime' => $request['departureDate'],
					'originLocation' => array(
						'locationCode' => $request['origin']
					),
					'destinationLocation' => array(
						'locationCode' => $request['destination']
					),
					'flexibleFaresOnly' => 'false',
					'includeInterlineFlights' => 'false',
					'openFlight' => '',
				],[
					'dateOffset' => '0',
					'departureDateTime' => $request['returnDate'],
					'originLocation' => array(
						'locationCode' => $request['destination']
					),
					'destinationLocation' => array(
						'locationCode' => $request['origin']
					),
					'flexibleFaresOnly' => 'false',
					'includeInterlineFlights' => 'false',
					'openFlight' => '',
				]
			);
		}else{
			$trip_type = 'ONE_WAY';
			$originDestinationInformationList = [
				'dateOffset' => '0',
				'departureDateTime' => $request['departureDate'],
				'originLocation' => array(
					'locationCode' => $request['origin']
				),
				'destinationLocation' => array(
					'locationCode' => $request['destination']
				),
				'flexibleFaresOnly' => 'false',
				'includeInterlineFlights' => 'false',
				'openFlight' => '',
			];
		}
        
	    $requestArray = array(
	        'AirAvailabilityRequest' => array(
	            'clientInformation' => array(
	                'clientIP' => env('pia_clientIP'),
	                'member' => 'false',
	                'password' => env('pia_password'),
	                'userName' => env('pia_userName'),
					'preferredCurrency' => 'PKR',
	            ),
	            'originDestinationInformationList' => $originDestinationInformationList,
	            'travelerInformation' => array(
	                'passengerTypeQuantityList' => $passengerTypeQuantityList
	            ),
	            'tripType' => $trip_type,
	            'frequentFlyerRedemption' => '',
	            'generateOnlyAvailability' => '',
	            'reissue' => '',
	            'showInterlineFlights' => '',
	            'useCitySearch' => '',
				'allFaresPerFlights' => '',
				'seeServiceLog' => '',
				'availabilityExtended' => '',
				'zedIetReservation' => ''
	        )
	    );
		// dd($requestArray);

		try {
			// $client = new \SoapClient(env('pia_url'), array('trace' => 1, 'exceptions' => 1));
			// $response = $client->GetAvailability($requestArray);
			
			// $soapRequest = $client->__getLastRequest();
			// $requestDom = new \DOMDocument();
			// $requestDom->preserveWhiteSpace = false;
			// $requestDom->formatOutput = true;
			// $requestDom->loadXML($soapRequest);
			// if ($request['tripType'] == "return") {
			// 	Storage::put('Hitit/flightSearchRequestRT.xml', $requestDom->saveXML());
			// }else{
			// 	Storage::put('Hitit/flightSearchRequestOW.xml', $requestDom->saveXML());
			// }
			// $soapResponse = $client->__getLastResponse();
			// $responseDom = new \DOMDocument();
			// $responseDom->preserveWhiteSpace = false;
			// $responseDom->formatOutput = true;
			// $responseDom->loadXML($soapResponse);
			// if ($request['tripType'] == "return") {
			// 	Storage::put('Hitit/flightSearchResponseRT.xml', $responseDom->saveXML());	
			// 	Storage::put('Hitit/flightSearchResponseRT.json', json_encode($response, JSON_PRETTY_PRINT));	
			// }else{
			// 	Storage::put('Hitit/flightSearchResponseOW.xml', $responseDom->saveXML());	
			// 	Storage::put('Hitit/flightSearchResponseOW.json', json_encode($response, JSON_PRETTY_PRINT));	
			// }
			// $response = json_decode(json_encode($response),true);
			
			///////////////////////////Old Response////////////////////////
				if ($request['tripType'] == "return") {
					$response = Storage::get('Hitit/LowFare/flightSearchResponseRT-1-1.json');
				}else{
					$response = Storage::get('Hitit/LowFare/flightSearchResponseOW-1-1-1.json');
				}
				$response = json_decode($response,true);
				// dd($response);
			////////////////////////End Old Response///////////////////////
			if ($request['tripType'] == "return") {
				$res = self::makeResponseReturn($response,$request);
			}else{
				$res = self::makeResponseOneway($response,$request);
			}
			// dd($response);
			$finalRes = $res;
			return $finalRes;
		} catch (\SoapFault $e) {
			$response = self::hititRemoveNamespaceFromXML($client->__getLastResponse());
			$xml   = simplexml_load_string($response);
			$jsonResponse = json_encode((array) $xml);
			return ['status' => '400', 'msg' => $jsonResponse];
		}
		
		
    }
	public static function lowFareSearch2($request){
		dd($request);
		if($request['children'] > 0){
			$child = '<passengerTypeQuantityList>
						  	<hasStrecher/>
						  	<passengerType>
							 	<code>CHLD</code>
						  	</passengerType>
						  	<quantity>'.$request['children'].'</quantity>
					   	</passengerTypeQuantityList>';
			}
			$infant = '';
			if ($request['infants'] > 0) {
			$infant = '<passengerTypeQuantityList>
						  	<hasStrecher/>
						  	<passengerType>
							 	<code>INFT</code>
						  	</passengerType>
						  	<quantity>'.$request['infants'].'</quantity>
					   	</passengerTypeQuantityList>';
			}
	  
		$message ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:impl="http://impl.soap.ws.crane.hititcs.com/">
			<soapenv:Header/>
				<soapenv:Body>
					<impl:GetAvailability>
						<AirAvailabilityRequest>
						<clientInformation>
							<clientIP>'.env('pia_clientIP').'</clientIP>
							<member>false</member>
							<password>'.env('pia_password').'</password>
							<userName>'.env('pia_userName').'</userName>
							<preferredCurrency>PKR</preferredCurrency>
						</clientInformation>
						<originDestinationInformationList>
							<prefferedCabinClass>ECONOMY</prefferedCabinClass>
							<dateOffset>0</dateOffset>
							<departureDateTime>'.$request['departureDate'].'</departureDateTime>
							<destinationLocation>
								<locationCode>'.$request['destination'].'</locationCode>
							</destinationLocation>
							<flexibleFaresOnly>false</flexibleFaresOnly>
							<includeInterlineFlights>false</includeInterlineFlights>
							<openFlight>false</openFlight>
							<originLocation>
								<locationCode>'.$request['origin'].'</locationCode>
							</originLocation>
						</originDestinationInformationList>
						<travelerInformation>
							<passengerTypeQuantityList>
								<hasStrecher/>
								<passengerType>
									<code>ADLT</code>
								</passengerType>
								<quantity>'.$request['adults'].'</quantity>
							</passengerTypeQuantityList>
							'.$child.$infant.'
						</travelerInformation>
						<tripType>ONE_WAY</tripType>
						</AirAvailabilityRequest>
					</impl:GetAvailability>
				<impl:GetAirAvailability/></soapenv:Body>
			</soapenv:Envelope>';
			dd($message);
	}
	public static function createPNR($request) {
		$api_offer = ApiOffer::where('ref_key',$request['itn_ref_key'])->first();
        $fareData = $api_offer->data;
		$passengerData = $request['passengers'];
	    $data = $fareData['flight'];
	    $index = $fareData['index'];
	    
	    // dd($request);
	    $price = $data['fareComponentList'][$index]['pricingOverview']['totalAmount']['value'];
	    $lowestPriceIndex = $index;

	    $bookOriginDestinationOptions = array();
	    $bookOriginDestinationOptionList = array();

	    if (is_array($data['boundList'])) {	
	        if (is_array($data['fareComponentList'][$lowestPriceIndex]['passengerFareInfoList'])) {
	            $passengerFareInfoList = $data['fareComponentList'][$lowestPriceIndex]['passengerFareInfoList'];
	        } else {
	            $passengerFareInfoList = $data['fareComponentList'][$lowestPriceIndex]['passengerFareInfoList'];
	        } 
			$passengerFareInfoList = self::putOnZeroIndex($passengerFareInfoList);
	        $xyz = 0;
			// dd($passengerFareInfoList);

			$passengerFareInfoList[0]['fareInfoList'] = self::putOnZeroIndex($passengerFareInfoList[0]['fareInfoList']);
			$data['boundList'] = self::putOnZeroIndex($data['boundList']);
	        foreach ($data['boundList'] as $bl) {
	            $bookFlightSegmentList = array();
	            if (is_array($bl['availFlightSegmentList'])) {
					foreach ($bl['availFlightSegmentList'] as $seg) {
	                    $flightSegment = (array) $seg['flightSegment'];
						$bookingClassListArray = self::putOnZeroIndex($seg['bookingClassList']);
	                    $bookFlightSegmentList[] = array(
	                        'actionCode' => 'NN',
	                        'bookingClass' => array(
	                            'cabin' => $bookingClassListArray[$lowestPriceIndex]['cabin'],
	                            'resBookDesigCode' => $passengerFareInfoList[0]['fareInfoList'][$xyz]['resBookDesigCode'],
	                            'resBookDesigQuantity' => $bookingClassListArray[$lowestPriceIndex]['resBookDesigQuantity'],
	                        ),
	                        'fareInfo' => array(
	                            'cabinClassCode' => $passengerFareInfoList[0]['fareInfoList'][$xyz]['cabinClassCode'],
	                            'fareBaggageAllowance' => (array) $passengerFareInfoList[0]['fareInfoList'][$xyz]['fareBaggageAllowance'],
	                            'fareGroupName' => $passengerFareInfoList[0]['fareInfoList'][$xyz]['fareGroupName'],
	                            'fareReferenceCode' => $passengerFareInfoList[0]['fareInfoList'][$xyz]['fareReferenceCode'],
	                            'fareReferenceID' => $passengerFareInfoList[0]['fareInfoList'][$xyz]['fareReferenceID'],
	                            'fareReferenceName' => $passengerFareInfoList[0]['fareInfoList'][$xyz]['fareReferenceName'],
	                            'flightSegmentSequence' => $passengerFareInfoList[0]['fareInfoList'][$xyz]['flightSegmentSequence'],
	                            'notValidAfter' => $passengerFareInfoList[0]['fareInfoList'][$xyz]['notValidAfter'],
	                            'notValidBefore' => $passengerFareInfoList[0]['fareInfoList'][$xyz]['notValidBefore'],
	                            'resBookDesigCode' => $passengerFareInfoList[0]['fareInfoList'][$xyz]['resBookDesigCode'],
	                        ),
	                        'addOnSegment' => '',
	                        'sequenceNumber' => '',
	                        'flightSegment' => $flightSegment,
	                        'involuntaryPermissionGiven' => 'false'
	                    );
	                    $xyz++;
	                }
	            }
				// dd($bookFlightSegmentList);
	            $bookOriginDestinationOptionList[] = array(
	                'bookFlightSegmentList' => $bookFlightSegmentList
	            );
	        }
	        $bookOriginDestinationOptions = array(
	            'bookOriginDestinationOptionList' => $bookOriginDestinationOptionList
	        );
	    }
		
	    $specialRequestDetails = array();
	    $specialRequestDetails['otherServiceInformations'] = array(
	        array(
	            'airTravelerSequence' => '0',
	            'code' => 'OSI',
	            'explanation' => 'CTCB 92 334 9112041',
	            'flightSegmentSequence' => '0',
	        ),
	        array(
	            'airTravelerSequence' => '0',
	            'code' => 'OSI',
	            'explanation' => 'CTCE HAMID22401@GMAIL.COM',
	            'flightSegmentSequence' => '0',
	        )
	    );

	    $airTravelerSequence = 1;
		
	    if (is_array($data['fareComponentList'][$lowestPriceIndex]['passengerFareInfoList'])) {
	        $ff = $data['fareComponentList'][$lowestPriceIndex]['passengerFareInfoList'];
	        $airTravelerList = array();
	        $sq = 0;
			$ff = self::putOnZeroIndex($ff);
			// dd($ff);
	        // foreach ($ff as $pInfo) {
				
	        //     if ($pInfo['passengerTypeQuantity']['passengerType']['code'] == "CHLD") {
	        //         foreach ($passengerData->Child as $cn) {
	        //             $airTraveler = array(
	        //                 'unaccompaniedMinor' => '',
	        //                 'birthDate' => $cn->dob,
	        //                 'shareMarketInd' => '',
	        //                 'gender' => 'C',
	        //                 'passengerTypeCode' => $pInfo->passengerTypeQuantity->passengerType->code,
	        //                 'personName' => array(
	        //                     'shareMarketInd' => '',
	        //                     'givenName' => $cn->firstname,
	        //                     'surname' => $cn->lastname,
	        //                     'unaccompaniedMinor' => '',
	        //                 ),
	        //                 'requestedSeatCount' => '1',
	        //                 'accompaniedByInfant' => '0',
	        //                 'hasStrecher' => '',
	        //                 'parentSequence' => '',
	        //                 'contactPerson' => array(
	        //                     'shareMarketInd' => '',
	        //                     'preferred' => '',
	        //                     'useForInvoicing' => '',
	        //                     'email' => array(
	        //                         'email' => $request->email,
	        //                         'markedForSendingRezInfo' => '',
	        //                         'preferred' => '',
	        //                         'shareMarketInd' => '',
	        //                         'useForInvoicing' => '',
	        //                         'shareContactInfo' => '',
	        //                     ),
	        //                     'markedForSendingRezInfo' => 'false',
	        //                     'personName' => array(
	        //                         'givenName' => $cn->firstname,
	        //                         'surname' => $cn->lastname,
	        //                         'preferred' => '',
	        //                         'shareMarketInd' => '',
	        //                         'markedForSendingRezInfo' => '',
	        //                         'useForInvoicing' => '',
	        //                         'shareContactInfo' => '',
	        //                     ),
	        //                     'phoneNumber' => array(
	        //                         'areaCode' => '532',
	        //                         'countryCode' => '+92',
	        //                         'subscriberNumber' => $request->passenger_phone,
	        //                         'preferred' => '',
	        //                         'shareMarketInd' => '',
	        //                         'markedForSendingRezInfo' => '',
	        //                         'useForInvoicing' => '',
	        //                         'shareContactInfo' => '',
	        //                     ),
	        //                     'shareMarketInd' => 'true',
	        //                     'socialSecurityNumber' => '33333333330',
	        //                     'shareContactInfo' => 'true',
	        //                 ),);

	        //             $dob = explode("-", $cn->dob);
	        //             $ssrDate = $dob [2] . strtoupper(date("M", mktime(null, null, null, $dob[1], 1))) . substr($dob[0], -2);

	        //             $specialRequestDetails['specialServiceRequestList'][] = array(
	        //                 'airTravelerSequence' => $airTravelerSequence,
	        //                 'flightSegmentSequence' => '0',
	        //                 'SSR' => array(
	        //                     'code' => 'CHLD',
	        //                     'explanation' => $ssrDate,
	        //                     'allowedQuantityPerPassenger' => '',
	        //                     'bundleRelatedSsr' => '',
	        //                     'extraBaggage' => '',
	        //                     'free' => '',
	        //                     'showOnItinerary' => '',
	        //                     'unitOfMeasureExist' => '',
	        //                     'ticketed' => '',
	        //                 ),
	        //                 'serviceQuantity' => '1',
	        //                 'status' => 'NN',
	        //                 'ticketed' => '',
	        //             );
	        //             $airTravelerSequence++;
	        //         }
	        //         $airTravelerList[] = $airTraveler;
	        //         if (isset($specialRequestDetails['specialServiceRequestList'])) {
	                    
	        //         } else {
	        //             $specialRequestDetails['specialServiceRequestList'] = array();
	        //         }

	        //     } else if ($pInfo['passengerTypeQuantity']['passengerType']['code'] == "INFT") {

	        //         if (isset($specialRequestDetails['specialServiceRequestList'])) {
	                    
	        //         } else {
	        //             $specialRequestDetails['specialServiceRequestList'] = array();
	        //         }


	        //         $infAts = 1;

	        //         foreach ($passengerData->Infant as $in) {
	        //             $dob = explode("-", $in->dob);
	        //             $ssrDate = $dob [2] . strtoupper(date("M", mktime(null, null, null, $dob[1], 1))) . substr($dob[0], -2);
	        //             $specialRequestDetails['specialServiceRequestList'][] = array(
	        //                 'airTravelerSequence' => $infAts,
	        //                 'flightSegmentSequence' => '1',
	        //                 'SSR' => array(
	        //                     'code' => 'INFT',
	        //                     'explanation' => $in->firstname . '/' . $in->lastname . ' ' . $ssrDate,
	        //                     'allowedQuantityPerPassenger' => '',
	        //                     'bundleRelatedSsr' => '',
	        //                     'extraBaggage' => '',
	        //                     'free' => '',
	        //                     'showOnItinerary' => '',
	        //                     'unitOfMeasureExist' => '',
	        //                     'ticketed' => '',
	        //                 ),
	        //                 'serviceQuantity' => '1',
	        //                 'status' => 'NN',
	        //                 'ticketed' => '',
	        //             );
	        //             if (is_array($data->boundList)) {
	        //                 $specialRequestDetails['specialServiceRequestList'][] = array(
	        //                     'airTravelerSequence' => $infAts,
	        //                     'flightSegmentSequence' => '2',
	        //                     'SSR' => array(
	        //                         'code' => 'INFT',
	        //                         'explanation' => $in->firstname . '/' . $in->lastname . ' ' . $ssrDate,
	        //                         'allowedQuantityPerPassenger' => '',
	        //                         'bundleRelatedSsr' => '',
	        //                         'extraBaggage' => '',
	        //                         'free' => '',
	        //                         'showOnItinerary' => '',
	        //                         'unitOfMeasureExist' => '',
	        //                         'ticketed' => '',
	        //                     ),
	        //                     'serviceQuantity' => '1',
	        //                     'status' => 'NN',
	        //                     'ticketed' => '',
	        //                 );
	        //             }
	        //             $airTravelerSequence++;
	        //             $infAts++;
	        //         }
	        //     } else {
	        //         $infantYes = array();
	        //         $infY = 0;
	        //         foreach ($passengerData as $pass) {
			// 			// if($pass['passenger_type'] == 'ADT'){
			// 			// 	$infantYes[] = '0';
			// 			// }
			// 			// if($pass['passenger_type'] == 'INFT'){
			// 			// 	$infantYes[$infY] = '1';
			// 			// }
			// 			$airTraveler = array(
	        //                 'gender' => ($pass['passenger_title'] == "Mr") ? 'M' : 'F',
			// 				'hasStrecher' => '',
			// 				'parentSequence' => '',
	        //                 'passengerTypeCode' => $pInfo['passengerTypeQuantity']['passengerType']['code'],
			// 				'personName' => array(
			// 					'givenName' => $pass['name'],
	        //                     'shareMarketInd' => '',
	        //                     'surname' => $pass['sur_name'],
	        //                     'unaccompaniedMinor' => 0,
	        //                 ),
	        //                 'accompaniedByInfant' => '',
			// 				// 'accompaniedByInfant' => $infantYes[$infY],
			// 				'contactPerson' => array(
	        //                     'email' => array(
	        //                         'email' => $request['customer_email'],
	        //                         'markedForSendingRezInfo' => '',
	        //                         'preferred' => '',
	        //                         'shareMarketInd' => '',
	        //                         'useForInvoicing' => '',
	        //                         'shareContactInfo' => '',
	        //                     ),
	        //                     'markedForSendingRezInfo' => 'false',
	        //                     'personName' => array(
	        //                         'givenName' => $pass['name'],
	        //                         'surname' => $pass['sur_name'],
	        //                         'preferred' => '',
	        //                         'shareMarketInd' => '',
	        //                         'markedForSendingRezInfo' => '',
	        //                         'useForInvoicing' => '',
	        //                         'shareContactInfo' => '',
	        //                     ),
	        //                     'phoneNumber' => array(
	        //                         // 'areaCode' => '532',
	        //                         // 'countryCode' => '+92',
	        //                         'markedForSendingRezInfo' => '',
	        //                         'preferred' => '',
	        //                         'shareMarketInd' => '',
	        //                         'subscriberNumber' => '00923349112041',
	        //                         // 'useForInvoicing' => '',
	        //                         // 'shareContactInfo' => '',
	        //                     ),
	        //                     'shareMarketInd' => 'true',
	        //                     'socialSecurityNumber' => '',
	        //                     'useForInvoicing' => '',
	        //                     'shareContactInfo' => 'true',
	        //                     'preferred' => '',
	        //                     'shareMarketInd' => 'true',
	        //                 ),
	        //                 'requestedSeatCount' => '1',
	        //                 'shareMarketInd' => '',
	        //                 'unaccompaniedMinor' => '',
	        //                 'birthDate' => $pass['dob'],
	        //             );
	        //             $airTravelerSequence++;
	        //             $infY++;
	        //             $airTravelerList[] = $airTraveler;
			// 			// $infY++;
			// 		}
	        //     }
	        //     $sq++;
	        // }

			$infantYes = array();
	        $infY = 0;
			foreach ($passengerData as $pass) {
				if($pass['passenger_type'] == 'ADT'){
					$passengerType = 'ADLT';
					$infantYes[] = '0';
				}
				if($pass['passenger_type'] == 'CNN'){
					$passengerType = 'CHLD';
					$infantYes[$infY] = '1';
				}
				if($pass['passenger_type'] == 'INF'){
					$passengerType = 'INFT';
					$infantYes[$infY] = '1';
				}
				$airTraveler = array(
					'gender' => ($pass['passenger_title'] == "Mr") ? 'M' : 'F',
					'hasStrecher' => '',
					'parentSequence' => '',
					'passengerTypeCode' => $passengerType,
					'personName' => array(
						'givenName' => $pass['name'],
						'shareMarketInd' => '',
						'surname' => $pass['sur_name'],
						'unaccompaniedMinor' => 0,
					),
					'accompaniedByInfant' => '',
					// 'accompaniedByInfant' => $infantYes[$infY],
					'contactPerson' => array(
						'email' => array(
							'email' => $request['customer_email'],
							'markedForSendingRezInfo' => '',
							'preferred' => '',
							'shareMarketInd' => '',
							'useForInvoicing' => '',
							'shareContactInfo' => '',
						),
						'markedForSendingRezInfo' => 'false',
						'personName' => array(
							'givenName' => $pass['name'],
							'surname' => $pass['sur_name'],
							'preferred' => '',
							'shareMarketInd' => '',
							'markedForSendingRezInfo' => '',
							'useForInvoicing' => '',
							'shareContactInfo' => '',
						),
						'phoneNumber' => array(
							// 'areaCode' => '532',
							// 'countryCode' => '+92',
							'markedForSendingRezInfo' => '',
							'preferred' => '',
							'shareMarketInd' => '',
							'subscriberNumber' => '00923349112041',
							// 'useForInvoicing' => '',
							// 'shareContactInfo' => '',
						),
						'shareMarketInd' => 'true',
						'socialSecurityNumber' => '',
						'useForInvoicing' => '',
						'shareContactInfo' => 'true',
						'preferred' => '',
						'shareMarketInd' => 'true',
					),
					'requestedSeatCount' => '1',
					'shareMarketInd' => '',
					'unaccompaniedMinor' => '',
					'birthDate' => $pass['dob'],
				);
				$airTravelerSequence++;
				$infY++;
				$airTravelerList[] = $airTraveler;
				// $infY++;
			}
	    } else {
	        $airTravelerList = array(
	            'unaccompaniedMinor' => '',
	            'birthDate' => $passengerData->Adult[0]->dob,
	            'shareMarketInd' => '',
	            'gender' => 'M',
	            'passengerTypeCode' => 'ADLT',
	            'personName' => array(
	                'shareMarketInd' => '',
	                'givenName' => $passengerData->Adult[0]->firstname,
	                'surname' => $passengerData->Adult[0]->lastname,
	                'unaccompaniedMinor' => '',
	            ),
	            'requestedSeatCount' => '1',
	            'accompaniedByInfant' => '',
	            'hasStrecher' => '',
	            'parentSequence' => '',
	            'contactPerson' => array(
	                'shareMarketInd' => '',
	                'preferred' => '',
	                'useForInvoicing' => '',
	                'email' => array(
	                    'email' => $request->email,
	                    'markedForSendingRezInfo' => '',
	                    'preferred' => '',
	                    'shareMarketInd' => '',
	                    'useForInvoicing' => '',
	                    'shareContactInfo' => '',
	                ),
	                'markedForSendingRezInfo' => 'false',
	                'personName' => array(
	                    'givenName' => $passengerData->Adult[0]->firstname,
	                    'surname' => $passengerData->Adult[0]->lastname,
	                    'preferred' => '',
	                    'shareMarketInd' => '',
	                    'markedForSendingRezInfo' => '',
	                    'useForInvoicing' => '',
	                    'shareContactInfo' => '',
	                ),
	                'phoneNumber' => array(
	                    'areaCode' => '532',
	                    'countryCode' => '+92',
	                    'subscriberNumber' => $request->passenger_phone,
	                    'preferred' => '',
	                    'shareMarketInd' => '',
	                    'markedForSendingRezInfo' => '',
	                    'useForInvoicing' => '',
	                    'shareContactInfo' => '',
	                ),
	                'shareMarketInd' => 'true',
	                'socialSecurityNumber' => '33333333330',
	                'shareContactInfo' => 'true',
	            ),
	        );
	    }
	    // $apiObject = json_decode($request->apiObject);
		// dd($airTravelerList);
	    $params = array(
	        'AirBookingRequest' => array(
	            'clientInformation' => array(
					'clientIP' => env('pia_clientIP'),
	                'member' => '0',
	                'password' => env('pia_password'),
	                'userName' => env('pia_userName'),
	                'preferredCurrency' => 'PKR',
	            ),
	            'airItinerary' => array(
	                'bookOriginDestinationOptions' => $bookOriginDestinationOptions,
	                'adviceCodeSegmentExist' => 'false',
	            ),
	            'airTravelerList' => $airTravelerList,
	            'contactInfoList' => array(
	                'shareMarketInd' => '',
	                'preferred' => '',
	                'markedForSendingRezInfo' => '',
	                'useForInvoicing' => '',
	                'shareContactInfo' => '',
	                'adress' => array(
	                    'useForInvoicing' => '',
	                    'markedForSendingRezInfo' => '',
	                    'shareMarketInd' => '',
	                    'preferred' => '',
	                    'formatted' => '',
	                    'countryCode' => 'PK',
	                    'shareContactInfo' => '',
	                ),
	                'email' => array(
	                    'useForInvoicing' => '',
	                    'markedForSendingRezInfo' => '',
	                    'shareMarketInd' => '',
	                    'preferred' => '',
	                    'email' => 'hamid22401@gmail.com',
	                    'shareContactInfo' => '',
	                ),
	                'personName' => array(
	                    'useForInvoicing' => '',
	                    'markedForSendingRezInfo' => '',
	                    'preferred' => '',
	                    'givenName' => 'HAMID',
	                    'surname' => 'AFRIDI',
	                    'shareMarketInd' => '',
	                    'shareContactInfo' => '',
	                ),
	                'phoneNumber' => array(
	                    'useForInvoicing' => '',
	                    'markedForSendingRezInfo' => '',
	                    'shareMarketInd' => '',
	                    'preferred' => '',
	                    'areaCode' => '321',
	                    'countryCode' => '+92',
	                    'subscriberNumber' => '9112041',
	                    'shareContactInfo' => '',
	                ),
	            ),
				'infantWithSeatCount' => 0,
	            'requestPurpose' => 'MODIFY_PERMANENTLY_AND_CALC',
	            'specialRequestDetails' => $specialRequestDetails,
	        )
	    );
		// return $params;
	    // dd($params);

		try {
			$client = new \SoapClient(env('pia_url'), array('trace' => TRUE, 'exceptions' => 1));
			$response = $client->CreateBooking($params);
			
			$soapRequest = $client->__getLastRequest();
			$requestDom = new \DOMDocument();
			$requestDom->preserveWhiteSpace = false;
			$requestDom->formatOutput = true;
			$requestDom->loadXML($soapRequest);
			Storage::put('Hitit/PNR/CreatePnrRequest_' . date('Y-m-d-H-i-s') . '.xml', $requestDom->saveXML());

			$soapResponse = $client->__getLastResponse();
			$responseDom = new \DOMDocument();
			$responseDom->preserveWhiteSpace = false;
			$responseDom->formatOutput = true;
			$responseDom->loadXML($soapResponse);
			Storage::put('Hitit/PNR/CreatePnrResponse_' . date('Y-m-d-H-i-s') . '.xml', $responseDom->saveXML());			
			Storage::put('Hitit/PNR/CreatePnrResponse_' . date('Y-m-d-H-i-s') . '.json', json_encode($response, JSON_PRETTY_PRINT));
			$response = json_encode($response);
			// **************Get Response From Storage*************\\
			// $response = Storage::get('Hitit/PNR/CreatePnrResponse.json');
			// **************End Get Response From Storage*************\\

			$response = json_decode($response,true);

			// dd($response);

			if (array_key_exists('AirBookingResponse', $response)) {
				$CreatePassengerNameRecordRS = $response['AirBookingResponse'];
				$ApplicationResults = $CreatePassengerNameRecordRS['airBookingList']['airReservation']['bookingReferenceIDList'];
				if (!isset($ApplicationResults['ID'])) {
					return ['status' => '400', 'response' => $response];
				}
				if (array_key_exists('airItinerary', $CreatePassengerNameRecordRS['airBookingList']['airReservation'])) {
					$pnr = $ApplicationResults['ID'];
					$airlinePNR = $ApplicationResults['ID'];
					$TotalAmount = $CreatePassengerNameRecordRS['airBookingList']['ticketInfo']['totalAmount']['value'];
					$lastTicketingDate = '';
				}
				$createPNRData = [
					'status' => '200', 
					'pnr' => $pnr, 
					'TotalAmount' => $TotalAmount, 
					'airlinePNR' => $airlinePNR, 
					'response' => $response, 
					'last_ticketing_date' => @$lastTicketingDate
				];
				return $createPNRData;
			}
			return ['status' => '400', 'response' => $response];
		} catch (\SoapFault $fault) {
			$soapRequest = $client->__getLastRequest();
			$requestDom = new \DOMDocument();
			$requestDom->preserveWhiteSpace = false;
			$requestDom->formatOutput = true;
			$requestDom->loadXML($soapRequest);
			Storage::put('Hitit/PNR/CreatePnrRequest_' . date('Y-m-d-H-i-s') . '.xml', $requestDom->saveXML());

			$soapResponse = $client->__getLastResponse();
			$responseDom = new \DOMDocument();
			$responseDom->preserveWhiteSpace = false;
			$responseDom->formatOutput = true;
			$responseDom->loadXML($soapResponse);
			Storage::put('Hitit/PNR/CreatePnrResponse_' . date('Y-m-d-H-i-s') . '.xml', $responseDom->saveXML());	

			$response = self::hititRemoveNamespaceFromXML($client->__getLastResponse());
			$xml   = simplexml_load_string($response);
			$jsonResponse = json_encode((array) $xml);
			dd($response);
			return ['status' => '400', 'response' => $jsonResponse];
		}
	}
	
	public static function issueTicket($order){
		$apiResponse = json_decode($order->apiResponse, true);
		$pnrRefList = $apiResponse['AirBookingResponse']['airBookingList']['airReservation']['bookingReferenceIDList'];
		$ticketInfo = $apiResponse['AirBookingResponse']['airBookingList']['ticketInfo']['totalAmount'];
		
		$message ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:impl="http://impl.soap.ws.crane.hititcs.com/">
				<soapenv:Header/>
				<soapenv:Body>
				<impl:TicketReservation>
					<AirTicketReservationRequest>
					'.self::credentials().'
					<bookingReferenceID>
						<companyName>
							<cityCode>'.$pnrRefList['companyName']['cityCode'].'</cityCode>
							<code>'.$pnrRefList['companyName']['code'].'</code>
							<codeContext>'.$pnrRefList['companyName']['codeContext'].'</codeContext>
							<companyFullName>'.env('pia_companyFullName').'</companyFullName>
							<companyShortName>'.env('pia_companyShortName').'</companyShortName>
							<countryCode>PK</countryCode>
						</companyName>
						<ID>'.$pnrRefList['ID'].'</ID>
					</bookingReferenceID>
					<fullfillment>
						<paymentDetails>
						<paymentDetailList>
							<miscChargeOrder>
								<avsEnabled/>
								<capturePaymentToolNumber>false</capturePaymentToolNumber>
								<paymentCode>INV</paymentCode>
								<threeDomainSecurityEligible>false</threeDomainSecurityEligible>
								<transactionFeeApplies/>
								<MCONumber>'.env('pia_MCONumber').'</MCONumber>
							</miscChargeOrder>
							<payLater/>
							<paymentAmount>
								<currency>
									<code>'.$ticketInfo['currency']['code'].'</code>
								</currency>
								<value>'.$ticketInfo['value'].'</value>
							</paymentAmount>
							<paymentType>MISC_CHARGE_ORDER</paymentType>
							<primaryPayment>true</primaryPayment>
						</paymentDetailList>
						</paymentDetails>
					</fullfillment>
					<requestPurpose>COMMIT</requestPurpose>
					</AirTicketReservationRequest>
				</impl:TicketReservation>
				</soapenv:Body>
			</soapenv:Envelope>';
			Storage::put('Hitit/Ticket/'.date('Y-m-d-H-i-s').'ticket_issue_req.xml', $message);
			// dd($message);
		///////////////////////Ticket Issue API Call////////////////////////
			$request = self::HititprettyPrint($message);
			$response = self::Hititcurl_action($request);
			Storage::put('Hitit/Ticket/'.date('Y-m-d-H-i-s').'ticket_issue_resp.xml',self::HititprettyPrint($response));
		////////////////////////////////////////////////////////////////////
		// $response = Storage::get('Hitit/Ticket/2024-08-20-17-09-03ticket_issue_resp.xml');
		// dd($response);
		$response = self::hititRemoveNamespaceFromXML($response);
		$xml   = simplexml_load_string($response);
		$jsonResponse = json_decode(json_encode((array) $xml), true);
		
		if (array_key_exists('AirTicketReservationResponse', $jsonResponse['Body']['TicketReservationResponse'])) {
			$ticketData = array();
            $issueTicketRS = $jsonResponse['Body']['TicketReservationResponse']['AirTicketReservationResponse']['airBookingList'];
            if (!array_key_exists('ID', $issueTicketRS['airReservation']['bookingReferenceIDList'])) {
				return ['status' => '400', 'msg' => json_encode($jsonResponse)];
            } else {
				$couponInfoList = self::putOnZeroIndex($issueTicketRS['ticketInfo']['ticketItemList']['couponInfoList']);
				$ticketsData = $couponInfoList[0];
				$ticketData[0]['name'] = $ticketsData['airTraveler']['contactPerson']['personName']['givenName'];
				$ticketData[0]['sur_name'] = $ticketsData['airTraveler']['contactPerson']['personName']['surname'];
				$ticketData[0]['TicketNumber'] = $ticketsData['ticketDocumentNbr'];
				return ['status'=> '200' , 'msg' => json_encode($jsonResponse) ,  'ticketData'=> $ticketData];
            }
        } else {
            Log::info($jsonResponse);
			return ['status' => '400', 'msg' => json_encode($jsonResponse)];
        }
	}
	public static function cancelBookingRequest($order){
		$apiResponse = json_decode($order->apiResponse, true);
		$pnrRefList = $apiResponse['AirBookingResponse']['airBookingList']['airReservation']['bookingReferenceIDList'];

		$childBookingReferenceIDList = '<childBookingReferenceIDList>
				<companyName>
					<cityCode>'.$pnrRefList['companyName']['cityCode'].'</cityCode>
					<code>'.$pnrRefList['companyName']['code'].'</code>
					<codeContext>'.$pnrRefList['companyName']['codeContext'].'</codeContext>
					<companyFullName>'.env('pia_companyFullName').'</companyFullName>
					<companyShortName>'.env('pia_companyShortName').'</companyShortName>
					<countryCode>PK</countryCode>
				</companyName>
				<ID>'.$order['pnrCode'].'</ID>
				<referenceID>'.$pnrRefList['referenceID'].'</referenceID>
			</childBookingReferenceIDList>';
			
		$message ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
			xmlns:impl="http://impl.soap.ws.crane.hititcs.com/">
			<soapenv:Header/>
			<soapenv:Body>
			<impl:CancelBooking>
				<AirCancelBookingRequest>
					'.self::credentials().'
					<bookingReferenceID>
						<companyName>
							<cityCode>'.$pnrRefList['companyName']['cityCode'].'</cityCode>
							<code>'.$pnrRefList['companyName']['code'].'</code>
							<codeContext>'.$pnrRefList['companyName']['codeContext'].'</codeContext>
							<companyFullName>'.env('pia_companyFullName').'</companyFullName>
							<companyShortName>'.env('pia_companyShortName').'</companyShortName>
							<countryCode>PK</countryCode>
						</companyName>
						<ID>'.$order['pnrCode'].'</ID>
						<referenceID>'.$pnrRefList['referenceID'].'</referenceID>
						'.$childBookingReferenceIDList.'
					</bookingReferenceID>
					<requestPurpose>COMMIT</requestPurpose>
				</AirCancelBookingRequest>
			</impl:CancelBooking>
			</soapenv:Body>
		</soapenv:Envelope>';
		Storage::put('Hitit/Cancel/'.date('Y-m-d-H-i-s').'cancel_req.xml', $message);
		// dd($message);
		///////////////////////Cancel Ticket API Call////////////////////////
			$request = self::HititprettyPrint($message);
			$response = self::Hititcurl_action($request);
			Storage::put('Hitit/Cancel/'.date('Y-m-d-H-i-s').'cancel_resp.xml',self::HititprettyPrint($response));
		////////////////////////////////////////////////////////////////////
			// $response = Storage::get('Hitit/Cancel/2024-08-23-15-18-49cancel_resp.xml');
		///////////////////////////////////////////////////////////////////
		$response = self::hititRemoveNamespaceFromXML($response);
		$xml   = simplexml_load_string($response);
		$jsonResponse = json_decode(json_encode((array) $xml), true);
		// Storage::put('Hitit/Cancel/'.date('Y-m-d-H-i-s').'cancel_resp.json', json_encode($jsonResponse, JSON_PRETTY_PRINT));
		// dd($jsonResponse['Body']['CancelBookingResponse']['AirCancelBookingResponse']['airBookingList']);
		$couponInfoList = $jsonResponse['Body']['CancelBookingResponse']['AirCancelBookingResponse']['airBookingList']['ticketInfo']['ticketItemList']['couponInfoList'];
		$couponInfoList = self::putOnZeroIndex($couponInfoList);

		if(@$couponInfoList){
			$ticket = array();
			$airline = array();
			foreach($couponInfoList as $key => $coupin){
				if($coupin['couponFlightSegment']['status'] == 'XX'){
					$airline[$key]['pnrStatus'] = 'Cancelled';
					$ticket[$key]['ticketStatus'] = 'Cancelled';
				}else{
					$airline[$key]['pnrStatus'] = 'Confirmed';
					$ticket[$key]['ticketStatus'] = 'Ticketed';
				}
			}
			return ['status'=> '200',  'ticket'=> $ticket, 'airline' => $airline, 'msg' => json_encode($response)];
		}else{
			Log::info($jsonResponse);
			return ['status' => '400', 'msg' => json_encode($jsonResponse)];
		}
		

	}
	public static function voidBookingRequest($order){
		$apiResponse = json_decode($order->apiResponse, true);
		$pnrRefList = $apiResponse['AirBookingResponse']['airBookingList']['airReservation']['bookingReferenceIDList'];
		// dd($pnrRefList);

		$message ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:impl="http://impl.soap.ws.crane.hititcs.com/">
			<soapenv:Header/>
			<soapenv:Body>
			<impl:VoidTicket>
				<VoidTicketRequest>
					'.self::credentials().'
					<bookingReferenceID>
						<companyName>
							<cityCode>'.$pnrRefList['companyName']['cityCode'].'</cityCode>
							<code>'.$pnrRefList['companyName']['code'].'</code>
							<codeContext>'.$pnrRefList['companyName']['codeContext'].'</codeContext>
							<companyFullName>'.env('pia_companyFullName').'</companyFullName>
							<companyShortName>'.env('pia_companyShortName').'</companyShortName>
							<countryCode>PK</countryCode>
						</companyName>
						<ID>'.$order['pnrCode'].'</ID>
						<referenceID>'.$pnrRefList['referenceID'].'</referenceID>
					</bookingReferenceID>
					<operationType>VOID_BOOKING</operationType>
				</VoidTicketRequest>
			</impl:VoidTicket>
			</soapenv:Body>
		</soapenv:Envelope>';

		///////////////////////Void Ticket API Call////////////////////////
			$request = self::HititprettyPrint($message);
			Storage::put('Hitit/Void/'.date('Y-m-d-H-i-s').'void_req.xml', $request);
			$response = self::Hititcurl_action($request);
			Storage::put('Hitit/Void/'.date('Y-m-d-H-i-s').'void_resp.xml',self::HititprettyPrint($response));
		////////////////////////////////////////////////////////////////////
			// $response = Storage::get('Hitit/Void/2024-08-19-22-31-47void_resp.xml');
		///////////////////////////////////////////////////////////////////
		$response = self::hititRemoveNamespaceFromXML($response);
		$xml   = simplexml_load_string($response);
		$jsonResponse = json_decode(json_encode((array) $xml), true);
		Storage::put('Hitit/Void/'.date('Y-m-d-H-i-s').'void_resp.json', json_encode($jsonResponse, JSON_PRETTY_PRINT));

		$ticketInfo = $jsonResponse['Body']['VoidTicketResponse']['AirBookingModifyResponse']['airBookingList']['ticketInfo'];
		if(@$ticketInfo){
			if($ticketInfo['ticketItemList']['status'] == 'RF'){
				$ticket[0] = [
					'ticketStatus' => 'Voided'
				];
				return ['status' => '200', 'ticket' => $ticket, 'msg' => json_encode($jsonResponse)];
			}else{
				return ['status' => '400', 'msg' => json_encode($jsonResponse)];
			}
		}else{
			Log::info($jsonResponse);
			return ['status' => '400', 'msg' => json_encode($jsonResponse)];
		}
		

	}
	public static function issueTicket_old($order)
    {
		$apiResponse = json_decode($order->apiResponse, true);
		$pnrRefList = $apiResponse['AirBookingResponse']['airBookingList']['airReservation']['bookingReferenceIDList'];
		// dd($apiResponse['airBookingList']['ticketInfo']['totalAmount']);
		$ticketInfo = $apiResponse['AirBookingResponse']['airBookingList']['ticketInfo']['totalAmount'];
		// dd($pnrRefList,$ticketInfo);
        $params = array(
            'AirCancelBookingRequest' => array(
                'clientInformation' => array(
					'clientIP' => env('pia_clientIP'),
	                'member' => '0',
	                'password' => env('pia_password'),
	                'userName' => env('pia_userName'),
	                'preferredCurrency' => 'PKR',
                ),
                'bookingReferenceID' => array(
                    'companyName' => array(
                        'cityCode' => $pnrRefList['companyName']['cityCode'],
                        'code' => $pnrRefList['companyName']['code'],
                        'codeContext' => $pnrRefList['companyName']['codeContext'],
                        'companyFullName' => $pnrRefList['companyName']['companyFullName'],
                        'companyShortName' => $pnrRefList['companyName']['companyShortName'],
                        'countryCode' => $pnrRefList['companyName']['countryCode']
                    ),
                    "ID" => $pnrRefList['ID'],
                    "referenceID" => $pnrRefList['referenceID'],
                ),
				'fullfillment' => array(
                    'paymentDetails' => [
                        'paymentDetailList' => [
                            'miscChargeOrder' => [
                                'avsEnabled' => '',
                                'capturePaymentToolNumber' => false,
                                'paymentCode' => 'INV',
                                'threeDomainSecurityEligible' => false,
                                'transactionFeeApplies' => '',
                                'MCONumber' => env('pia_MCONumber')
                            ],
                            'payLater' => '',
                            'paymentAmount' => [
                                'currency' => [
                                    'code' => $ticketInfo['currency']['code']
                                ],
                                'mileAmount' => '',
                                'value' => $ticketInfo['value'],
                            ],
                            'paymentType' => 'MISC_CHARGE_ORDER',
                            'primaryPayment' => true,
                        ]
                    ]
				),
                'requestPurpose' => 'COMMIT'
            )
        );
		// dd($params);
		try {
			$client = new \SoapClient('http://app-stage.crane.aero/craneota/CraneOTAService?wsdl', array('trace' => TRUE, 'exceptions' => 1));
			$response = $client->TicketReservation($params);
			

			$soapRequest = $client->__getLastRequest();
			$requestDom = new \DOMDocument();
			$requestDom->preserveWhiteSpace = false;
			$requestDom->formatOutput = true;
			$requestDom->loadXML($soapRequest);
			Storage::put('Hitit/Issue/'.$pnrRefList['ID'].'-IssueRequest.xml', $requestDom->saveXML());

			$soapResponse = $client->__getLastResponse();
			$responseDom = new \DOMDocument();
			$responseDom->preserveWhiteSpace = false;
			$responseDom->formatOutput = true;
			$responseDom->loadXML($soapResponse);
			Storage::put('Hitit/Issue/'.$pnrRefList['ID'].'-IssueResponse.xml', $responseDom->saveXML());

			$response = json_encode($response);
			$response = json_decode($response, TRUE);

			dd($response);
		} catch (\SoapFault $fault) {
			dd($fault->getMessage());
			$soapRequest = $client->__getLastRequest();
			$requestDom = new \DOMDocument();
			$requestDom->preserveWhiteSpace = false;
			$requestDom->formatOutput = true;
			$requestDom->loadXML($soapRequest);
			Storage::put('Hitit/Issue/'.$pnrRefList['ID'].'-IssueRequest.xml', $requestDom->saveXML());
		
			$soapResponse = $client->__getLastResponse();
			$responseDom = new \DOMDocument();
			$responseDom->preserveWhiteSpace = false;
			$responseDom->formatOutput = true;
			$responseDom->loadXML($soapResponse);
			Storage::put('Hitit/Issue/'.$pnrRefList['ID'].'-IssueResponse.xml', $responseDom->saveXML());
		
			throw $fault;
		}
       
    }
	/********************Make Response Oneway**************/
	public static function makeResponseOneway($response,$req){
		if(isset($response['faultstring'])){
			return ['status'=>'400','msg'=>$response['faultstring']];
		}
		if(!array_key_exists('originDestinationOptionList',$response['Availability']['availabilityResultList']['availabilityRouteList']['availabilityByDateList'])){
			return ['status' => '400', 'msg' => "No Flights available on this date, Please try another one.."];
		}

		$hitit = array();
		$hitit = self::SetHititResponseForOneWay($response,true);
		// dd($hitit);
        $finalData = array();
        $flights = array();

        foreach($hitit as $key => $oneway){
			if (array_key_exists(0, $oneway['fareComponentGroupList'])) {
				$connecting_flights = array();
				foreach ($oneway['fareComponentGroupList'] as $sec_key => $sec_value)
				{
					if (array_key_exists('boundList', $sec_value)){
						$connecting_flights['boundList']['availFlightSegmentList'][] = $sec_value['boundList']['availFlightSegmentList'];
					}
					if (array_key_exists('fareComponentList', $sec_value)){
						$connecting_flights['fareComponentList'] = $sec_value['fareComponentList'];
					}
				}
				$flights[$key] = $connecting_flights;
			}elseif (array_key_exists(0, $oneway['fareComponentGroupList']['boundList'])) {
				$connecting_flights = array();
				foreach ($oneway['fareComponentGroupList'] as $third_key => $third_value)
				{
					if ($third_key == 'boundList') {
						foreach ($third_value as $forth_key => $forth_value)
						{
							if (array_key_exists('availFlightSegmentList', $forth_value)){
								$connecting_flights['boundList']['availFlightSegmentList'][] = $forth_value['availFlightSegmentList'];
							}
						}
					}
					if (array_key_exists('fareComponentList', $oneway['fareComponentGroupList'])){
						$connecting_flights['fareComponentList'] = $oneway['fareComponentGroupList']['fareComponentList'];
					}
				}
				$flights[$key] = $connecting_flights;
			}else{
				if (array_key_exists('boundList', $oneway['fareComponentGroupList'])){
					$flights[$key]['boundList'] = $oneway['fareComponentGroupList']['boundList'];
				}
				if (array_key_exists('fareComponentList', $oneway['fareComponentGroupList'])){
					$flights[$key]['fareComponentList'] = $oneway['fareComponentGroupList']['fareComponentList'];
				}
			}
        }
        /********************Make Own Response*******************/
        $finalData = array();
        $min_pr = 10000000;
        $LowFareSearch = array();
        $segments = array();
        $PassengerFares = array();
        
        if(!empty($flights)){
			
        	foreach($flights as $key => $flight){
				if(isset($flight['fareComponentList'])){
					$finalData[$key]['api'] = 'Hitit';
					$finalData[$key]['MarketingAirline']['Airline'] = 'PK';
					$finalData[$key]['MarketingAirline']['FareRules'] = 'NA';

					$index = '';
					$journeyDuration = 0;
					$BaggagePolicy = array();
					$PassengerFares = array();
					$min_price = PHP_INT_MAX;
					$BaseFare = 0;
					$Taxes = 0;
					$price_key = '';
        		
					/*---------minimum price flight key--------*/
					$flight['fareComponentList'] = self::putOnZeroIndex($flight['fareComponentList']);
					foreach($flight['fareComponentList'] as $key1 => $price)
                    { 
                    	if($price['pricingOverview']['totalAmount']['value'] < $min_price)
                    	{
                    	  $min_price = $price['pricingOverview']['totalAmount']['value'];
                    	  $Taxes = $price['pricingOverview']['totalTax']['value'];
						  $BaseFare = $min_price - $Taxes;
                    	  $price_key = $key1;
                    	  $index 	 = $key1;

                    	}
                    }
        			/*---------End minimum price flight key--------*/

					$flight['boundList']['availFlightSegmentList'] = self::putOnZeroIndex($flight['boundList']['availFlightSegmentList']);
					$depart_time = $flight['boundList']['availFlightSegmentList'][0]['flightSegment']['departureDateTime'];
					
					foreach($flight['boundList']['availFlightSegmentList'] as $seg_key => $segmnt){
						$journeyDuration = self::getDurationInMinutes($segmnt['flightSegment']['journeyDuration']);
						$segments[$seg_key]['Duration'] = $journeyDuration;
						$segments[$seg_key]['OperatingAirline']['Code'] = $segmnt['flightSegment']['airline']['code'];
						$segments[$seg_key]['OperatingAirline']['FlightNumber'] = $segmnt['flightSegment']['flightNumber'];
						$segments[$seg_key]['MarketingAirline']['Code'] = $segmnt['flightSegment']['airline']['code'];
						$segments[$seg_key]['MarketingAirline']['FlightNumber'] = $segmnt['flightSegment']['flightNumber'];
						$segments[$seg_key]['Departure']['LocationCode'] = $segmnt['flightSegment']['departureAirport']['locationCode'];
						$segments[$seg_key]['Departure']['DepartureDateTime'] = $segmnt['flightSegment']['departureDateTime'];
						$segments[$seg_key]['Arrival']['LocationCode'] = $segmnt['flightSegment']['arrivalAirport']['locationCode'];
						$segments[$seg_key]['Arrival']['ArrivalDateTime'] = $segmnt['flightSegment']['arrivalDateTime'];
						$segments[$seg_key]['Cabin'] = '';
						// $segments[$seg_key]['Cabin'] = $segmnt['bookingClassList'][$price_key]['cabin'].' ('. $segmnt['bookingClassList'][$price_key]['resBookDesigCode'] .')';
						$segments[$seg_key]['EquipType'] = '';
						// $arraival_time = $segmnt['flightSegment']['arrivalDateTime'];
					}

                    /*--------------Passenger Fares and Baggage-------------*/
					$passengerFareInfoList = self::putOnZeroIndex($flight['fareComponentList'][$price_key]['passengerFareInfoList']);
                    foreach($passengerFareInfoList as $pass_key => $fare_obj){
						$passType = $fare_obj['passengerTypeQuantity']['passengerType']['code'];
	                    if ($passType == "ADLT") {
	                        $code = "Adult";
	                    }
	                    if ($passType == "CHLD") {
	                        $code = "Child";
	                    }
	                    if ($passType == "INFT") {
	                        $code = "Infant";
	                    }

	                    $PassengerFareBreakdown['PaxType'] = $code;
	                    $PassengerFareBreakdown['Currency'] = 'PKR';
	                    $PassengerFareBreakdown['Quantity'] = $fare_obj['passengerTypeQuantity']['quantity'];
	                    $PassengerFareBreakdown['BasePrice'] = $fare_obj['pricingInfo']['equivBaseFare']['value'] + $fare_obj['pricingInfo']['surcharges']['totalAmount']['value'];
	                    $PassengerFareBreakdown['Taxes'] = $fare_obj['pricingInfo']['taxes']['totalAmount']['value'];
	                    $PassengerFareBreakdown['Fees'] = 0;
	                    $PassengerFareBreakdown['ServiceCharges'] = 0;
	                    $PassengerFareBreakdown['TotalPrice'] = $fare_obj['pricingInfo']['totalFare']['amount']['value'];
						array_push($PassengerFares,$PassengerFareBreakdown);
						///////////////////Baggage/////////////////////////
						$fareInfoList = self::putOnZeroIndex($fare_obj['fareInfoList']);

						$weight = $fareInfoList[0]['fareBaggageAllowance']['maxAllowedWeight']['weight'];
						$unit = $fareInfoList[0]['fareBaggageAllowance']['maxAllowedWeight']['unitOfMeasureCode'];

						$Baggage['Weight'] = $weight;
						$Baggage['Unit'] = $unit;
						$Baggage['PaxType'] = $code;
						array_push($BaggagePolicy,$Baggage);
	                }
                    /*--------------End Passenger Fares and Baggage-------------*/

					$LowFareSearch['Segments'] = $segments;
					$LowFareSearch['TotalDuration'] = $journeyDuration;
					$finalData[$key]['Flights'][0] = $LowFareSearch;
					$finalData[$key]['Flights'][0]['NonRefundable'] = false;
					$finalData[$key]['Flights'][0]['MultiFares'] = false;
					$finalData[$key]['Flights'][0]['Fares'][0]['RefID'] = Str::uuid();
					$finalData[$key]['Flights'][0]['Fares'][0]['Currency'] = "PKR";
					$finalData[$key]['Flights'][0]['Fares'][0]['BaseFare'] = $BaseFare;
					$finalData[$key]['Flights'][0]['Fares'][0]['Taxes'] = $Taxes;
					$finalData[$key]['Flights'][0]['Fares'][0]['TotalFare'] = $min_price;
					$finalData[$key]['Flights'][0]['Fares'][0]['BillablePrice'] = $min_price;
					$finalData[$key]['Flights'][0]['Fares'][0]['BaggagePolicy'] = $BaggagePolicy;
					$finalData[$key]['Flights'][0]['Fares'][0]['PassengerFares'] = $PassengerFares;

					$originalData = array();
					$originalData['index'] = $index;
					$originalData['flight'] = $flight;
					$finalDataDb = json_encode($finalData[$key]);
					$apiOffer = new ApiOffer();
					$apiOffer->api = "Hitit";
					$apiOffer->data = json_encode($originalData);
					$apiOffer->ref_key = Str::uuid();
					$apiOffer->finaldata = json_decode($finalDataDb,true);
					$apiOffer->timestamp = time();
					$apiOffer->query = json_encode($req);
					// $apiOffer->query = json_encode($req->except('apiObject'));
					$apiOffer->save();
					$finalData[$key]['itn_ref_key'] = $apiOffer->ref_key;
				}
        	}
        }
        // dd($finalData);
		return ['status'=> '200' , 'msg' => $finalData];

	}
	/********************Make Response Return**************/
	public static function makeResponseReturn($response,$req){
		
		if(isset($response['faultstring'])){
			return ['status'=>'400','msg'=>$response['faultstring']];
		}
		if(!array_key_exists('originDestinationOptionList',$response['Availability']['availabilityResultList']['availabilityRouteList'][0]['availabilityByDateList'])){
			return ['status' => '400', 'msg' => "No Flights available on this date, Please try another one.."];
		}

		$hitit = array();
		$hitit = self::SetHititResponseForRoundTrip($response,true);
		dd($hitit);
        $finalData = array();
        $flights = array();
        foreach($hitit as $key => $oneway){
			if (array_key_exists(0, $oneway['fareComponentGroupList'])) {
				$connecting_flights = array();
				foreach ($oneway['fareComponentGroupList'] as $sec_key => $sec_value)
				{
					if (array_key_exists('boundList', $sec_value)){
						$connecting_flights['boundList']['availFlightSegmentList'][] = $sec_value['boundList']['availFlightSegmentList'];
					}
					if (array_key_exists('fareComponentList', $sec_value)){
						$connecting_flights['fareComponentList'] = $sec_value['fareComponentList'];
					}
				}
				$flights[$key] = $connecting_flights;
			}elseif (array_key_exists(0, $oneway['fareComponentGroupList']['boundList'])) {
				$connecting_flights = array();
				foreach ($oneway['fareComponentGroupList'] as $third_key => $third_value)
				{
					if ($third_key == 'boundList') {
						foreach ($third_value as $forth_key => $forth_value)
						{
							if (array_key_exists('availFlightSegmentList', $forth_value)){
								$connecting_flights['boundList']['availFlightSegmentList'][] = $forth_value['availFlightSegmentList'];
							}
						}
					}
					if (array_key_exists('fareComponentList', $oneway['fareComponentGroupList'])){
						$connecting_flights['fareComponentList'] = $oneway['fareComponentGroupList']['fareComponentList'];
					}
				}
				$flights[$key] = $connecting_flights;
			}else{
				if (array_key_exists('boundList', $oneway['fareComponentGroupList'])){
					$flights[$key]['boundList'] = $oneway['fareComponentGroupList']['boundList'];
				}
				if (array_key_exists('fareComponentList', $oneway['fareComponentGroupList'])){
					$flights[$key]['fareComponentList'] = $oneway['fareComponentGroupList']['fareComponentList'];
				}
			}
        }
        /********************Make Own Response*******************/
        $finalData = array();
        $min_pr = 10000000;

        $LowFareSearch = array();
        
        $fareBreakDown = array();
        
        if(!empty($flights)){
        	foreach($flights as $key => $flight){
        		$finalData[$key]['api'] = 'Hitit';
        		$finalData[$key]['MarketingAirline']['Airline'] = 'PK';
        		$finalData[$key]['MarketingAirline']['FareRules'] = 'NA';

        		$index = '';
        		if(isset($flight['fareComponentList'])){

        			/*---------minimum price flight key--------*/
        			$min_price = PHP_INT_MAX;
                    $price_key = '';
					foreach($flight['fareComponentList'] as $key1 => $price)
                    {
                    	if($price['pricingOverview']['totalAmount']['value'] < $min_price)
                    	{
                    	  $min_price = $price['pricingOverview']['totalAmount']['value'];
                    	  $price_key = $key1;
                    	  $index 	 = $price_key;
                    	}
                    }
        			/*---------End minimum price flight key--------*/

        			/*-------------if array key not exist 0 index-----------*/
						$legs = self::putOnZeroIndex($flight['boundList']['availFlightSegmentList']);
						$passengerFareInfoList = self::putOnZeroIndex($flight['fareComponentList'][$price_key]['passengerFareInfoList']);
        			/*-------------if array key not exist 0 index-----------*/
        			

        			foreach($legs as $leg_key => $flights2){
        				$flights2 = self::putOnZeroIndex($flights2);


	    				/* ------segments-----*/
	    				$segments = array();

						$depart_time = $flights2[0]['flightSegment']['departureDateTime'];
						foreach($flights2 as $seg_key => $segmnt){
							$segments[$seg_key]['Duration'] = $segmnt['flightSegment']['journeyDuration'];

							$segments[$seg_key]['OperatingAirline']['Code'] = $segmnt['flightSegment']['airline']['code'];

							$segments[$seg_key]['OperatingAirline']['FlightNumber'] = $segmnt['flightSegment']['flightNumber'];

							$segments[$seg_key]['Departure']['LocationCode'] = $segmnt['flightSegment']['departureAirport']['locationCode'];

							$segments[$seg_key]['Departure']['DepartureDateTime'] = $segmnt['flightSegment']['departureDateTime'];

							$segments[$seg_key]['Arrival']['LocationCode'] = $segmnt['flightSegment']['arrivalAirport']['locationCode'];

							$segments[$seg_key]['Arrival']['ArrivalDateTime'] = $segmnt['flightSegment']['arrivalDateTime'];
							
							/*-------------Passenger Baggage-------------*/
							foreach($passengerFareInfoList as $pass_key => $pass_info){
								// ---Passenger Type----//
								$passType = $pass_info['passengerTypeQuantity']['passengerType']['code'];
								if ($passType == "ADLT") {
			                        $code = "ADT";
			                    }
			                    if ($passType == "CHLD") {
			                        $code = "CNN";
			                    }
			                    if ($passType == "INFT") {
			                        $code = "INF";
			                    }
								// --end passenger type--//

								$fareInfoList = self::putOnZeroIndex($pass_info['fareInfoList']);

								$segments[$seg_key]['Baggage'][$code]['Weight'] = @$fareInfoList[$seg_key]['fareBaggageAllowance']['maxAllowedWeight']['weight'];

								$segments[$seg_key]['Baggage'][$code]['Unit'] = @$fareInfoList[$seg_key]['fareBaggageAllowance']['maxAllowedWeight']['unitOfMeasureCode'];
							}
							/*-----------End Passenger Baggage-----------*/

							$segments[$seg_key]['Cabin'] = $segmnt['bookingClassList'][$price_key]['cabin'].' ('. $segmnt['bookingClassList'][$price_key]['resBookDesigCode'] .')';

							$arraival_time = $segmnt['flightSegment']['arrivalDateTime'];
						}
						/* -----end segments---*/
						$LowFareSearch['Segments'] = $segments;
		        		$LowFareSearch['TotalDuration'] = self::getDuration($arraival_time ,  $depart_time);
		        		$finalData[$key]['LowFareSearch'][$leg_key] = $LowFareSearch;
        			}
					

                    /*--------------Passenger fare info-------------*/
                    foreach($passengerFareInfoList as $pass_key => $fare_obj){
                    	// ---Passenger Type----//
						$passType = $fare_obj['passengerTypeQuantity']['passengerType']['code'];
	                    if ($passType == "ADLT") {
	                        $code = "ADT";
	                    }
	                    if ($passType == "CHLD") {
	                        $code = "CNN";
	                    }
	                    if ($passType == "INFT") {
	                        $code = "INF";
	                    }
						// --end passenger type--//

	                    $fareBreakDown[$code]['Quantity'] = $fare_obj['passengerTypeQuantity']['quantity'];

	                    $fareBreakDown[$code]['TotalFare'] =$fare_obj['pricingInfo']['totalFare']['amount']['value'];

	                    $fareBreakDown[$code]['BaseFare'] =$fare_obj['pricingInfo']['equivBaseFare']['value'];

	                    $fareBreakDown[$code]['TotalTax'] =$fare_obj['pricingInfo']['taxes']['totalAmount']['value'];
	                }

                    
                    /*--------------End Passenger fare info-------------*/

        		}

        		$finalData[$key]['Fares']['CurrencyCode'] = "PKR";
        		$finalData[$key]['Fares']['TotalPrice'] = $min_price;
        		$finalData[$key]['Fares']['fareBreakDown'] = $fareBreakDown;

        		$originalData['index'] = $index;
        		$originalData['flight'] = $flight;
        		// $val['bearerKey'] = $accessToken;
				$apiOffer = new ApiOffer();
	            $apiOffer->api = "Hitit";
	            $apiOffer->data = json_encode($originalData);
	            $apiOffer->finaldata = json_encode($finalData[$key]);
	            $apiOffer->timestamp = time();
	            $apiOffer->query = json_encode($req->except('apiObject'));
	            $apiOffer->save();
	            $finalData[$key]['api_offer_id'] = $apiOffer->id;
        	}
        }
        return $finalData;

	}
	/********************Make Response Return**************/
	public static function makeResponseMulti($response,$req){
		if(isset($response['faultstring'])){
			return ['status'=>'400','msg'=>$response['faultstring']];
		}else if(isset($response['Availability']['availabilityResultList']['availabilityRouteList'][0])){
			return ['status'=>'400','msg'=>'No Result found'];
		}
		$hitit = array();
        if (!is_array($response['Availability']['availabilityResultList']['availabilityRouteList']['availabilityByDateList']['originDestinationOptionList'])) {
            $hitit[] = $response['Availability']['availabilityResultList']['availabilityRouteList']['availabilityByDateList']['originDestinationOptionList'];
        } else {
            $hitit = $response['Availability']['availabilityResultList']['availabilityRouteList']['availabilityByDateList']['originDestinationOptionList'];
        }
        $hitit = self::putOnZeroIndex($hitit);

        $finalData = array();
        $flights = array();
        foreach($hitit as $key => $oneway){
			if (array_key_exists(0, $oneway['fareComponentGroupList'])) {
				$connecting_flights = array();
				foreach ($oneway['fareComponentGroupList'] as $sec_key => $sec_value)
				{
					if (array_key_exists('boundList', $sec_value)){
						$connecting_flights['boundList']['availFlightSegmentList'][] = $sec_value['boundList']['availFlightSegmentList'];
					}
					if (array_key_exists('fareComponentList', $sec_value)){
						$connecting_flights['fareComponentList'] = $sec_value['fareComponentList'];
					}
				}
				$flights[$key] = $connecting_flights;
			}elseif (array_key_exists(0, $oneway['fareComponentGroupList']['boundList'])) {
				$connecting_flights = array();
				foreach ($oneway['fareComponentGroupList'] as $third_key => $third_value)
				{
					if ($third_key == 'boundList') {
						foreach ($third_value as $forth_key => $forth_value)
						{
							if (array_key_exists('availFlightSegmentList', $forth_value)){
								$connecting_flights['boundList']['availFlightSegmentList'][] = $forth_value['availFlightSegmentList'];
							}
						}
					}
					if (array_key_exists('fareComponentList', $oneway['fareComponentGroupList'])){
						$connecting_flights['fareComponentList'] = $oneway['fareComponentGroupList']['fareComponentList'];
					}
				}
				$flights[$key] = $connecting_flights;
			}else{
				if (array_key_exists('boundList', $oneway['fareComponentGroupList'])){
					$flights[$key]['boundList'] = $oneway['fareComponentGroupList']['boundList'];
				}
				if (array_key_exists('fareComponentList', $oneway['fareComponentGroupList'])){
					$flights[$key]['fareComponentList'] = $oneway['fareComponentGroupList']['fareComponentList'];
				}
			}
        }
        /********************Make Own Response*******************/
        $finalData = array();
        $min_pr = 10000000;

        $LowFareSearch = array();
        
        $fareBreakDown = array();
        
        if(!empty($flights)){
        	foreach($flights as $key => $flight){
        		$finalData[$key]['api'] = 'Hitit';
        		$finalData[$key]['MarketingAirline']['Airline'] = 'PK';
        		$finalData[$key]['MarketingAirline']['FareRules'] = 'NA';

        		if(isset($flight['fareComponentList'])){

        			/*---------minimum price flight key--------*/
        			$min_price = PHP_INT_MAX;
                    $price_key = '';
					foreach($flight['fareComponentList'] as $key1 => $price)
                    {
                    	if($price['pricingOverview']['totalAmount']['value'] < $min_price)
                    	{
                    	  $min_price = $price['pricingOverview']['totalAmount']['value'];
                    	  $price_key = $key1;

                    	}
                    }
        			/*---------End minimum price flight key--------*/

        			/*-------------if array key not exist 0 index-----------*/
						$legs = self::putOnZeroIndex($flight['boundList']['availFlightSegmentList']);
						$passengerFareInfoList = self::putOnZeroIndex($flight['fareComponentList'][$price_key]['passengerFareInfoList']);
        			/*-------------if array key not exist 0 index-----------*/
        			

        			foreach($legs as $leg_key => $flights2){
        				$flights2 = self::putOnZeroIndex($flights2);


	    				/* ------segments-----*/
	    				$segments = array();

						$depart_time = $flights2[0]['flightSegment']['departureDateTime'];
						foreach($flights2 as $seg_key => $segmnt){
							$segments[$seg_key]['Duration'] = $segmnt['flightSegment']['journeyDuration'];

							$segments[$seg_key]['OperatingAirline']['Code'] = $segmnt['flightSegment']['airline']['code'];

							$segments[$seg_key]['OperatingAirline']['FlightNumber'] = $segmnt['flightSegment']['flightNumber'];

							$segments[$seg_key]['Departure']['LocationCode'] = $segmnt['flightSegment']['departureAirport']['locationCode'];

							$segments[$seg_key]['Departure']['DepartureDateTime'] = $segmnt['flightSegment']['departureDateTime'];

							$segments[$seg_key]['Arrival']['LocationCode'] = $segmnt['flightSegment']['arrivalAirport']['locationCode'];

							$segments[$seg_key]['Arrival']['ArrivalDateTime'] = $segmnt['flightSegment']['arrivalDateTime'];
							
							/*-------------Passenger Baggage-------------*/
							foreach($passengerFareInfoList as $pass_key => $pass_info){
								// ---Passenger Type----//
								$passType = $pass_info['passengerTypeQuantity']['passengerType']['code'];
								if ($passType == "ADLT") {
			                        $code = "ADT";
			                    }
			                    if ($passType == "CHLD") {
			                        $code = "CNN";
			                    }
			                    if ($passType == "INFT") {
			                        $code = "INF";
			                    }
								// --end passenger type--//

								$fareInfoList = self::putOnZeroIndex($pass_info['fareInfoList']);

								$segments[$seg_key]['Baggage'][$code]['Weight'] = @$fareInfoList[$seg_key]['fareBaggageAllowance']['maxAllowedWeight']['weight'];

								$segments[$seg_key]['Baggage'][$code]['Unit'] = @$fareInfoList[$seg_key]['fareBaggageAllowance']['maxAllowedWeight']['unitOfMeasureCode'];
							}
							/*-----------End Passenger Baggage-----------*/

							$segments[$seg_key]['Cabin'] = $segmnt['bookingClassList'][$price_key]['cabin'].' ('. $segmnt['bookingClassList'][$price_key]['resBookDesigCode'] .')';

							$arraival_time = $segmnt['flightSegment']['arrivalDateTime'];
						}
						/* -----end segments---*/
						$LowFareSearch['Segments'] = $segments;
		        		$LowFareSearch['TotalDuration'] = self::getDuration($arraival_time ,  $depart_time);
		        		$finalData[$key]['LowFareSearch'][$leg_key] = $LowFareSearch;
        			}
					

                    /*--------------Passenger fare info-------------*/
                    foreach($passengerFareInfoList as $pass_key => $fare_obj){
                    	// ---Passenger Type----//
						$passType = $fare_obj['passengerTypeQuantity']['passengerType']['code'];
	                    if ($passType == "ADLT") {
	                        $code = "ADT";
	                    }
	                    if ($passType == "CHLD") {
	                        $code = "CNN";
	                    }
	                    if ($passType == "INFT") {
	                        $code = "INF";
	                    }
						// --end passenger type--//

	                    $fareBreakDown[$code]['Quantity'] = $fare_obj['passengerTypeQuantity']['quantity'];

	                    $fareBreakDown[$code]['TotalFare'] =$fare_obj['pricingInfo']['totalFare']['amount']['value'];

	                    $fareBreakDown[$code]['BaseFare'] =$fare_obj['pricingInfo']['equivBaseFare']['value'];

	                    $fareBreakDown[$code]['TotalTax'] =$fare_obj['pricingInfo']['taxes']['totalAmount']['value'];
	                }

                    
                    /*--------------End Passenger fare info-------------*/

        		}

        		$finalData[$key]['Fares']['CurrencyCode'] = "PKR";
        		$finalData[$key]['Fares']['TotalPrice'] = $min_price;
        		$finalData[$key]['Fares']['fareBreakDown'] = $fareBreakDown;

        		// $val['bearerKey'] = $accessToken;
				$apiOffer = new ApiOffer();
	            $apiOffer->api = "Hitit";
	            // $apiOffer->data = json_encode($val);
	            $apiOffer->finaldata = json_encode($finalData[$key]);
	            $apiOffer->timestamp = time();
	           	$apiOffer->query = json_encode($req->except('apiObject'));
	            $apiOffer->save();
	            $finalData[$key]['api_offer_id'] = $apiOffer->id;
        	}
        }

        
        return $finalData;
	}
	public static function credentials()
	{
		return "<clientInformation>
					<clientIP>".env('pia_clientIP')."</clientIP>
					<member>false</member>
					<password>".env('pia_password')."</password>
					<userName>".env('pia_userName')."</userName>
					<preferredCurrency>PKR</preferredCurrency>
				</clientInformation>";
	}
	/************************Fare Rules********************/
	public static function fareRules($data) {
		$flight =  json_decode($data->finaldata);
		// return $flight;

	    $client = new \SoapClient('https://app.crane.aero/craneota/CraneOTAService?wsdl', array('trace' => TRUE, 'exceptions' => 0));

	    $passengerTypeQuantityList = array();
	    $originDestination = array();
	    $pass = array();

	    if (isset($flight->Fares->fareBreakDown)) {
		    foreach($flight->Fares->fareBreakDown as $key => $passType){
		    	if($key == 'ADT'){
		    		$code = 'ADLT';
		    	}else if($key == 'CHD'){
		    		$code = 'CHLD';
		    	}else{
		    		$code = 'INFT';
		    	}

		    	$passengerTypeQuantityList[] = array(
			        'hasStrecher' => '',
			        'passengerType' => array(
			            'code' => $code
			        ),
			        'quantity' => $passType->Quantity
			    );
		    }
	    }

	    if (isset($flight->LowFareSearch)) {
		    foreach($flight->LowFareSearch as $LowFareSearch){
		    	foreach($LowFareSearch->Segments as $segKey => $segment){
		    		$originDestination[] = array(
	                    'dateOffset' => '0',
	                    'departureDateTime' => $segment->Departure->DepartureDateTime,
	                    'destinationLocation' => array(
	                        'locationCode' => $segment->Arrival->LocationCode,
	                    ),
	                    'flexibleFaresOnly' => 'false',
	                    'includeInterlineFlights' => 'false',
	                    'openFlight' => 'false',
	                    'originLocation' => array(
	                        'locationCode' => $segment->Departure->LocationCode,
	                    ),
	                );
		    	}
		    }
		}

	    $params = array(
	        'AirAvailabilityRequest' => array(
	            'clientInformation' => array(
	                'clientIP' => '129.0.0.1',
	                'member' => '0',
	                'password' => 'Pakmat10',
	                'userName' => 'A231BB74',
	                'preferredCurrency' => 'PKR',
	            ),
	            'originDestinationInformationList' => $originDestination,
	            'travelerInformation' => array(
	                'passengerTypeQuantityList' => $passengerTypeQuantityList
	            ),
	            'tripType' => 'ONE_WAY',
	            'frequentFlyerRedemption' => '',
	            'generateOnlyAvailability' => '',
	            'reissue' => '',
	            'showInterlineFlights' => '',
	            'useCitySearch' => ''
	        )
	    );

	    // return $params;
	    $response = $client->GetAirExtraChargesAndProducts($params);
	    return $response;
	}
	/*
	*||||||||||||||||||||| Auth and other functions ||||||||||||||||||||
	*/
	public static function hititAuth(){

		$client = new \SoapClient('http://app-stage.crane.aero/craneota/CraneOTAService?wsdl', array('trace' => TRUE, 'exceptions' => 1));
	    $params = array(
	        'AvailabilityGeneralParametersRequest' => array(
	            'clientInformation' => array(
	                'clientIP' => '129.0.0.1',
	                'member' => '0',
	                'password' => "Pia123",
	                'userName' => "PSA27463822"
	            )
	        )
	    );
		// return $params;
	    $response = $client->GetAvailabilityGeneralParameters($params);
		Storage::put('Pia/HititAuth.json', json_encode($response, JSON_PRETTY_PRINT));
	    dd($response);
	    return $response;
	}

	public static function putOnZeroIndex($obj) {
		if (!array_key_exists("0",$obj))
		{
			$obj[0] = $obj;
			foreach($obj as $k => $kVal){
				if($k != 0){
					unset($obj[$k]);
				}
			}
		}
		return $obj;
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
	public static function HititprettyPrint($result)
	{
		$dom = new \DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($result);
		$dom->formatOutput = true;    
		return $dom->saveXML();
	}
	public static function Hititcurl_action($message)
	{
		$header = array(
		"Content-Type: text/xml;charset=UTF-8",
		);
		$soap_do=curl_init(env('pia_url_curl'));
		curl_setopt($soap_do, CURLOPT_POST, true ); 
		curl_setopt($soap_do, CURLOPT_POSTFIELDS, $message); 
		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header); 
		$return = curl_exec($soap_do);
		ini_set('memory_limit', '-1');
		curl_close($soap_do);
		return $return;
	}
	public static function hititRemoveNamespaceFromXML($xml)
	{
		$toRemove = ['S','ns2'];
		// $toRemove = ['air', 'turss', 'crim];
		// This is part of a regex I will use to remove the namespace declaration from string
		$nameSpaceDefRegEx = '(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?';
		// Cycle through each namespace and remove it from the XML string
		foreach( $toRemove as $remove ) {
				// First remove the namespace from the opening of the tag
				$xml = str_replace('<' . $remove . ':', '<', $xml);
				// Now remove the namespace from the closing of the tag
				$xml = str_replace('</' . $remove . ':', '</', $xml);
				// This XML uses the name space with CommentText, so remove that too
				// $xml = str_replace($remove . ':BookingTraveler', 'BookingTraveler', $xml);
				$pattern = "/xmlns:{$remove}{$nameSpaceDefRegEx}/";
				// Remove the actual namespace declaration using the Pattern
				$xml = preg_replace($pattern, '', $xml, 1);
			}
		// Return sanitized and cleaned up XML with no namespaces
		return $xml;
	}
	/////////////////////Atta Lowfare//////////////////////////
	public static function SetHititResponseForOneWay($response)
	{
		$back_response = array();
		if (!(isset($response['Body']['Fault']))) {

			if (!empty($response['Availability']))
			{
				$response['Availability']['availabilityResultList']['availabilityRouteList'] = self::putOnZeroIndex($response['Availability']['availabilityResultList']['availabilityRouteList']);
				
				$check_response = $response['Availability']['availabilityResultList']['availabilityRouteList'][0]['availabilityByDateList'];
				if (array_key_exists('originDestinationOptionList', $check_response)) {
					$first_check_response = $response['Availability']['availabilityResultList']['availabilityRouteList'][0]['availabilityByDateList'];
					$second_check_response = $response['Availability']['availabilityResultList']['availabilityRouteList'][0]['availabilityByDateList']['originDestinationOptionList'];

					if (array_key_exists('dateList', $first_check_response)) {
						unset($first_check_response['dateList']);
					}
					if (!array_key_exists(0, $first_check_response['originDestinationOptionList'])) {
						$back_response['originDestinationOptionList'][] = $first_check_response['originDestinationOptionList'];
					}
					else{
						$back_response = $first_check_response;

					}
				}
				else{
					return ['success' => false, 'message' => 'Avilability Not Found First'];
				}
			}
			else{
				return ['success' => false, 'message' => 'Avilability Not Found Second Last'];
			}
		}
		else{
			return ['success' => false, 'message' => 'Avilability Not Found last'];
		}
		return $back_response['originDestinationOptionList'];
	}
	public static function SetHititResponseForRoundTrip($response)
	{
		$back_response = array();
		if (!(isset($response['Body']['Fault']))) {
			
			if (!empty($response['Availability']))
			{
				$availabilityRouteList = $response['Availability']['availabilityResultList']['availabilityRouteList'];
				if (!isset($availabilityRouteList['availabilityByDateList'])) {
					if (!isset($availabilityRouteList[0]['availabilityByDateList'])) {
						return ['success' => false, 'message' => 'Inbound Flights Not Found On The Selected Date'];
					}
					else{
						$Outbound_Response = $availabilityRouteList[0]['availabilityByDateList'];
					}
					
					if (!isset($availabilityRouteList[1]['availabilityByDateList'])) {
						return ['success' => false, 'message' => 'Outbound Flights Not Not Found On The Selected Date'];
					}
					else{
						$Inbound_Response = $availabilityRouteList[1]['availabilityByDateList'];
					}
					
					if (isset($response['Availability']['availabilityResultList']['availabilityRouteFareMappingList'])) {
						$FareMappingListResponse = $response['Availability']['availabilityResultList']['availabilityRouteFareMappingList']; 
						$fareMapIndex = 0;
						/////////////////////////////////////////////////////////////////////////////////////
						// foreach ($FareMappingListResponse as $Fristkey => $Firstvalue) {
						// 	$Firstvalue['inBoundFareList'] = self::putOnZeroIndex($Firstvalue['inBoundFareList']);
						// 	foreach ($Firstvalue['inBoundFareList'] as $Seckey => $Secvalue) {
						// 		$Get_Inbound = self::putOnZeroIndex($Inbound_Response['originDestinationOptionList']);
						// 		foreach ($Get_Inbound as $Get_Inbound_Key => $Get_Inbound_Value) {
						// 			if (!isset($Get_Inbound_Value['fareComponentGroupList']['fareComponentList'])) {
						// 				continue;
						// 			}
						// 			$Get_Inbound_Value['fareComponentGroupList']['fareComponentList'] = self::putOnZeroIndex($Get_Inbound_Value['fareComponentGroupList']['fareComponentList']);
						// 			foreach ($Get_Inbound_Value['fareComponentGroupList']['fareComponentList'] as $key => $value) {
						// 				if ($value['internalID'] == $Secvalue['inboundFareId']) {
						// 					$FareMappingListResponse[$Fristkey]['Inbound'][] = $Get_Inbound_Value;
						// 				}
						// 			}
						// 		}

						// 	}
						// 	// dd($Get_Inbound);
						// 	$Get_Outbound = self::putOnZeroIndex($Outbound_Response['originDestinationOptionList']);
						// 	foreach ($Get_Outbound as $Get_Outbound_Key => $Get_Outbound_Value) {
						// 		if (!isset($Get_Outbound_Value['fareComponentGroupList']['fareComponentList'])) {
						// 			continue;
						// 		}
						// 		else{
						// 			if (array_key_exists(0, $Get_Outbound_Value['fareComponentGroupList']['fareComponentList'])) {
						// 				foreach ($Get_Outbound_Value['fareComponentGroupList']['fareComponentList'] as $key => $value) {
						// 					if ($value['internalID'] == $Firstvalue['outboundFareId']) {
						// 						$FareMappingListResponse[$Fristkey]['Outbound'][] = $Get_Outbound_Value;
						// 					}
						// 				}
						// 			}
						// 			elseif ($Get_Outbound_Value['fareComponentGroupList']['fareComponentList']['internalID'] == $Firstvalue['outboundFareId']) {
						// 				$FareMappingListResponse[$Fristkey]['Outbound'][] = $Get_Outbound_Value;
						// 			}
						// 		}
						// 	}

						// }
						/////////////////////////////////////////////////////////////////////////////////////
						foreach ($FareMappingListResponse as $Fristkey => $Firstvalue) {
							$Firstvalue['inBoundFareList'] = self::putOnZeroIndex($Firstvalue['inBoundFareList']);
							foreach ($Firstvalue['inBoundFareList'] as $Seckey => $Secvalue) {
								$Get_Inbound = self::putOnZeroIndex($Inbound_Response['originDestinationOptionList']);
								foreach ($Get_Inbound as $Get_Inbound_Key => $Get_Inbound_Value) {
									if (!isset($Get_Inbound_Value['fareComponentGroupList']['fareComponentList'])) {
										continue;
									}
									$Get_Inbound_Value['fareComponentGroupList']['fareComponentList'] = self::putOnZeroIndex($Get_Inbound_Value['fareComponentGroupList']['fareComponentList']);
									foreach ($Get_Inbound_Value['fareComponentGroupList']['fareComponentList'] as $key => $value) {
										if ($value['internalID'] == $Secvalue['inboundFareId']) {
											$FareMappingListResponse[$fareMapIndex]['Inbound'] = $Get_Inbound_Value;
										}
									}
								}

								$Get_Outbound = self::putOnZeroIndex($Outbound_Response['originDestinationOptionList']);
								foreach ($Get_Outbound as $Get_Outbound_Key => $Get_Outbound_Value) {
									if (!isset($Get_Outbound_Value['fareComponentGroupList']['fareComponentList'])) {
										continue;
									}
									if (!isset($Get_Outbound_Value['fareComponentGroupList']['fareComponentList'])) {
										continue;
									}
									$Get_Outbound_Value['fareComponentGroupList']['fareComponentList'] = self::putOnZeroIndex($Get_Outbound_Value['fareComponentGroupList']['fareComponentList']);
									foreach ($Get_Outbound_Value['fareComponentGroupList']['fareComponentList'] as $key => $value) {
										if ($value['internalID'] == $Firstvalue['outboundFareId']) {
											$FareMappingListResponse[$fareMapIndex]['Outbound'] = $Get_Outbound_Value;
										}
									}
								}

								$fareMapIndex++;
							}

						}
						dd($FareMappingListResponse);
						return $FareMappingListResponse;
					}
					else{
						return ['success' => false, 'message' => 'Avilability Not Found'];
					}
				}
				else{
					if (isset($response['Body']['GetAvailabilityResponse']['Availability']['availabilityResultList']['availabilityRouteList']['availabilityByDateList']['originDestinationOptionList'])) {
						$response = $response['Body']['GetAvailabilityResponse']['Availability']['availabilityResultList']['availabilityRouteList']['availabilityByDateList']['originDestinationOptionList'];

						$Inbound = [];
						$Outbound = [];
						$Response = [];
						foreach ($response as $firstkey => $firstvalue) {
							foreach ($firstvalue['fareComponentGroupList']['boundList'] as $key => $value) {
								if ($value['boundCode'] == 'Inbound') {
									$Response[$firstkey]['Inbound'][0]['fareComponentGroupList']['boundList'] = $value;
									$Response[$firstkey]['Inbound'][0]['fareComponentGroupList']['fareComponentList'] = $firstvalue['fareComponentGroupList']['fareComponentList'];
								}
								elseif ($value['boundCode'] == 'Outbound'){
									$Response[$firstkey]['Outbound'][0]['fareComponentGroupList']['boundList'] = $value;
									$Response[$firstkey]['Outbound'][0]['fareComponentGroupList']['fareComponentList'] = $firstvalue['fareComponentGroupList']['fareComponentList'];
								}
							}
							$Response[$firstkey]['somethingdifferent'] = 'somethingdifferent';
						}
						return $Response;
					}else{
						return ['success' => false, 'message' => 'Avilability Not Found'];
					}
				}
			}
			else{
				return ['success' => false, 'message' => 'Avilability Not Found'];
			}
		}
		else{
			return ['success' => false, 'message' => 'Avilability Not Found'];
		}
		return $back_response;
	}
	public static function SetHititResponseForMultiTrip($response)
	{
		try{
		$back_response = array();
		if (!(isset($response['Body']['Fault']))) {
			if (!empty($response['Body']['GetAvailabilityResponse']['Availability']))
			{
			if (!isset($response['Body']['GetAvailabilityResponse']['Availability']['availabilityResultList']['availabilityRouteList']['availabilityByDateList'])) {
				if (!isset($response['Body']['GetAvailabilityResponse']['Availability']['availabilityResultList']['availabilityRouteList'][0]['availabilityByDateList'])) {
					return ['success' => false, 'message' => 'Inbound Flights Not Found On The Selected Date'];
				}
				else{
					$Outbound_Response = $response['Body']['GetAvailabilityResponse']['Availability']['availabilityResultList']['availabilityRouteList'][0]['availabilityByDateList'];
				}

				if (!isset($response['Body']['GetAvailabilityResponse']['Availability']['availabilityResultList']['availabilityRouteList'][1]['availabilityByDateList'])) {
					return ['success' => false, 'message' => 'Outbound Flights Not Not Found On The Selected Date'];
				}
				else{
					$Inbound_Response = $response['Body']['GetAvailabilityResponse']['Availability']['availabilityResultList']['availabilityRouteList'][1]['availabilityByDateList'];
				}

				$response_setting = $response['Body']['GetAvailabilityResponse']['Availability']['availabilityResultList']['availabilityRouteList'];
				if (isset($response['Body']['GetAvailabilityResponse']['Availability']['availabilityResultList']['availabilityRouteFareMappingList'])) {
				$FareMappingListResponse = $response['Body']['GetAvailabilityResponse']['Availability']['availabilityResultList']['availabilityRouteFareMappingList']; 

				$Making_Response = [];
				foreach ($FareMappingListResponse as $Fristkey => $Firstvalue) {
					foreach ($Firstvalue['inBoundFareList'] as $Seckey => $Secvalue) {
					$Get_Inbound = [];
					if (array_key_exists(0, $Inbound_Response['originDestinationOptionList'])) {
						$Get_Inbound = $Inbound_Response['originDestinationOptionList'];
					}
					else{
						$Get_Inbound[] = $Inbound_Response['originDestinationOptionList'];
					}

					foreach ($Get_Inbound as $Get_Inbound_Key => $Get_Inbound_Value) {
						if (!isset($Get_Inbound_Value['fareComponentGroupList']['fareComponentList'])) {
						continue;
						}
						if (array_key_exists(0, $Get_Inbound_Value['fareComponentGroupList']['fareComponentList'])) {
						foreach ($Get_Inbound_Value['fareComponentGroupList']['fareComponentList'] as $key => $value) {
							if ($value['internalID'] == $Secvalue['inboundFareId']) {
							$FareMappingListResponse[$Fristkey]['Inbound'][] = $Get_Inbound_Value;
							}
						}
						}
						else{
						if ($Get_Inbound_Value['fareComponentGroupList']['fareComponentList']['internalID'] == $Secvalue['inboundFareId']) {
							$FareMappingListResponse[$Fristkey]['Inbound'][] = $Get_Inbound_Value;
						}
						}
					}
					}

					$Get_Outbound = [];
					if (array_key_exists(0, $Outbound_Response['originDestinationOptionList'])) {
						$Get_Outbound = $Outbound_Response['originDestinationOptionList'];
					}
					else{
						$Get_Outbound[] = $Outbound_Response['originDestinationOptionList'];
					}

					foreach ($Get_Outbound as $Get_Outbound_Key => $Get_Outbound_Value) {
					if (!isset($Get_Outbound_Value['fareComponentGroupList']['fareComponentList'])) {
						continue;
					}
					else{
						if (array_key_exists(0, $Get_Outbound_Value['fareComponentGroupList']['fareComponentList'])) {
							foreach ($Get_Outbound_Value['fareComponentGroupList']['fareComponentList'] as $key => $value) {
								if ($value['internalID'] == $Firstvalue['outboundFareId']) {
									$FareMappingListResponse[$Fristkey]['Outbound'][] = $Get_Outbound_Value;
								}
							}
						}
						elseif ($Get_Outbound_Value['fareComponentGroupList']['fareComponentList']['internalID'] == $Firstvalue['outboundFareId']) {
							$FareMappingListResponse[$Fristkey]['Outbound'][] = $Get_Outbound_Value;
						}
					}
					}
				}
				return $FareMappingListResponse;
				}
				else{
				// here is a flight avilabilaty but for time shortage not set
				return ['success' => false, 'message' => 'Avilability Not Found'];
				}
			}
			else{
				if (isset($response['Body']['GetAvailabilityResponse']['Availability']['availabilityResultList']['availabilityRouteList']['availabilityByDateList']['originDestinationOptionList'])) {

				$response = $response['Body']['GetAvailabilityResponse']['Availability']['availabilityResultList']['availabilityRouteList']['availabilityByDateList']['originDestinationOptionList'];

				$Inbound = [];
				$Outbound = [];
				$Response = [];
				foreach ($response as $firstkey => $firstvalue) {
					foreach ($firstvalue['fareComponentGroupList']['boundList'] as $key => $value) {
					// dd($firstvalue,$firstkey,$key);
					if ($value['boundCode'] == 'Inbound') {
						$InboundKey = 'Inbound_'.$key;
						$Response[$firstkey][$InboundKey][0]['fareComponentGroupList']['boundList'] = $value;
						$Response[$firstkey][$InboundKey][0]['fareComponentGroupList']['fareComponentList'] = $firstvalue['fareComponentGroupList']['fareComponentList'];
					}
					elseif ($value['boundCode'] == 'Outbound'){
						$Response[$firstkey]['Outbound'][0]['fareComponentGroupList']['boundList'] = $value;
						$Response[$firstkey]['Outbound'][0]['fareComponentGroupList']['fareComponentList'] = $firstvalue['fareComponentGroupList']['fareComponentList'];
					}
					}
					$Response[$firstkey]['somethingdifferent'] = 'somethingdifferent';
				}
				// dd($Response);
				return $Response;
				}
				else{
				return ['success' => false, 'message' => 'Avilability Not Found'];
				}
			}
			}
			else{
			return ['success' => false, 'message' => 'Avilability Not Found'];
			}
		}
		else{
			return ['success' => false, 'message' => 'Avilability Not Found'];
		}
		return $back_response;
		}
		catch(\Exception $e){
		return ['success' => false, 'message' => 'Avilability Not Found' . $e->getLine() . ' - ' . $e->getMessage()];
		}
	}
	
}