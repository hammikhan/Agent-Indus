<?php

namespace App\Http\Traits\APIS;

use App\Models\ApiOffer;
use Illuminate\Support\Facades\Storage;

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

	    $requestArray = array(
	        'AirAvailabilityRequest' => array(
	            'clientInformation' => array(
	                'clientIP' => env('pia_clientIP'),
	                'member' => 'false',
	                'password' => env('pia_password'),
	                'userName' => env('pia_userName'),
					'preferredCurrency' => 'PKR',
	            ),
	            'originDestinationInformationList' => array(
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
	            ),
	            'travelerInformation' => array(
	                'passengerTypeQuantityList' => $passengerTypeQuantityList
	            ),
	            'tripType' => 'ONE_WAY',
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
		
		// return $requestArray;
		// $client = new \SoapClient(env('pia_url'), array('trace' => TRUE, 'exceptions' => 1));
		$client = new \SoapClient('http://app-stage.crane.aero/craneota/CraneOTAService?wsdl', array('trace' => TRUE, 'exceptions' => 1));
	    $response = $client->GetAvailability($requestArray);
	    Storage::put('Pia/flightSearchResponse.json', json_encode($response, JSON_PRETTY_PRINT));
	    // $response = Storage::get('Pia/flightSearchResponse1.json');
		$response = json_encode($response);
		dd(json_decode($response,true));
	    $res = self::makeResponseOneway(json_decode($response,true),$request);

	    if(@$res['status'] == 400){
	    	$finalRes = $res;
	    }else{
	    	$finalRes = ['status'=> '200' , 'msg' => $res];
	    }
	    dd($finalRes);
    }
	public static function createPnr($request) {
	    
		$passengerData = json_decode($request->passengerData);
	    $orignalData = json_decode($request->data);
	    $data = $orignalData->flight;
	    $index = $orignalData->index;
	    
	    
	    $price = $data->fareComponentList[$index]->pricingOverview->totalAmount->value;
	    $lowestPriceIndex = $index;

	    
	    // $client = new SoapClient('https://app.crane.aero/craneota/CraneOTAService?wsdl', array('trace' => TRUE, 'exceptions' => 0));


	    $bookOriginDestinationOptions = array();
	    $bookOriginDestinationOptionList = array();

	    if (is_array($data->boundList)) {
	        // Return OR Multi City Flights
	        // CHECK WHAT HAPPENS TO fareComponentList in RETURN
	        if (is_array($data->fareComponentList[$lowestPriceIndex]->passengerFareInfoList)) {
	            $ff = $data->fareComponentList[$lowestPriceIndex]->passengerFareInfoList[0];
	        } else {
	            $ff = $data->fareComponentList[$lowestPriceIndex]->passengerFareInfoList;
	        } 
	        $xyz = 0;

	        foreach ($data->boundList as $bl) {
	            $bookFlightSegmentList = array();

	            if (is_array($bl->availFlightSegmentList)) {
	                foreach ($bl->availFlightSegmentList as $seg) {
	                    // Connecting Flight
	                    $flightSegment = (array) $seg->flightSegment;

	                    $bookFlightSegmentList[] = array(
	                        'actionCode' => 'NN',
	                        'bookingClass' => array(
	                            'cabin' => $seg->bookingClassList[$lowestPriceIndex]->cabin,
	                            'resBookDesigCode' => $ff->fareInfoList[$xyz]->resBookDesigCode,
	                            'resBookDesigQuantity' => $seg->bookingClassList[$lowestPriceIndex]->resBookDesigQuantity,
	                        ),
	                        'fareInfo' => array(
	                            'cabinClassCode' => $ff->fareInfoList[$xyz]->cabinClassCode,
	                            'fareBaggageAllowance' => (array) $ff->fareInfoList[$xyz]->fareBaggageAllowance,
	                            'fareGroupName' => $ff->fareInfoList[$xyz]->fareGroupName,
	                            'fareReferenceCode' => $ff->fareInfoList[$xyz]->fareReferenceCode,
	                            'fareReferenceID' => $ff->fareInfoList[$xyz]->fareReferenceID,
	                            'fareReferenceName' => $ff->fareInfoList[$xyz]->fareReferenceName,
	                            'flightSegmentSequence' => $ff->fareInfoList[$xyz]->flightSegmentSequence,
	                            'notValidAfter' => $ff->fareInfoList[$xyz]->notValidAfter,
	                            'notValidBefore' => $ff->fareInfoList[$xyz]->notValidBefore,
	                            'resBookDesigCode' => $ff->fareInfoList[$xyz]->resBookDesigCode,
	                        ),
	                        'addOnSegment' => '',
	                        'sequenceNumber' => '',
	                        'flightSegment' => $flightSegment,
	                        'involuntaryPermissionGiven' => 'false'
	                    );
	                    $xyz++;
	                }
	            } else {
	                // Direct Flight
	                $flightSegment = (array) $bl->availFlightSegmentList->flightSegment;

	                $bookFlightSegmentList[] = array(
	                    'actionCode' => 'NN',
	                    'bookingClass' => array(
	                        'cabin' => $bl->availFlightSegmentList->bookingClassList[$lowestPriceIndex]->cabin,
	                        'resBookDesigCode' => $ff->fareInfoList[$xyz]->resBookDesigCode,
	                        'resBookDesigQuantity' => $bl->availFlightSegmentList->bookingClassList[$lowestPriceIndex]->resBookDesigQuantity,
	                    ),
	                    'fareInfo' => array(
	                        'cabinClassCode' => $ff->fareInfoList[$xyz]->cabinClassCode,
	                        'fareBaggageAllowance' => (array) $ff->fareInfoList[$xyz]->fareBaggageAllowance,
	                        'fareGroupName' => $ff->fareInfoList[$xyz]->fareGroupName,
	                        'fareReferenceCode' => $ff->fareInfoList[$xyz]->fareReferenceCode,
	                        'fareReferenceID' => $ff->fareInfoList[$xyz]->fareReferenceID,
	                        'fareReferenceName' => $ff->fareInfoList[$xyz]->fareReferenceName,
	                        'flightSegmentSequence' => $ff->fareInfoList[$xyz]->flightSegmentSequence,
	                        'notValidAfter' => $ff->fareInfoList[$xyz]->notValidAfter,
	                        'notValidBefore' => $ff->fareInfoList[$xyz]->notValidBefore,
	                        'resBookDesigCode' => $ff->fareInfoList[$xyz]->resBookDesigCode,
	                    ),
	                    'addOnSegment' => '',
	                    'sequenceNumber' => '',
	                    'flightSegment' => $flightSegment,
	                    'involuntaryPermissionGiven' => 'false'
	                );
	                $xyz++;
	            }

	            $bookOriginDestinationOptionList[] = array(
	                'bookFlightSegmentList' => $bookFlightSegmentList
	            );
	        }
	        $bookOriginDestinationOptions = array(
	            'bookOriginDestinationOptionList' => $bookOriginDestinationOptionList
	        );
	    } else if (is_array($data->boundList->availFlightSegmentList)) {
	        // ONE Way AND Connecting Flights
	        if (is_array($data->fareComponentList[$lowestPriceIndex]->passengerFareInfoList)) {
	            $ff = $data->fareComponentList[$lowestPriceIndex]->passengerFareInfoList[0];
	        } else {
	            $ff = $data->fareComponentList[$lowestPriceIndex]->passengerFareInfoList;
	        }

	        $bookFlightSegmentList = array();
	        $xyz = 0;
	        
	        foreach ($data->boundList->availFlightSegmentList as $seg) {
	        	$type = gettype($seg);
	            if($type == 'object'){
		            $flightSegment = (array) $seg->flightSegment;
		            $bookFlightSegmentList[] = array(
		                'actionCode' => 'NN',
		                'bookingClass' => array(
		                    'cabin' => $seg->bookingClassList[$lowestPriceIndex]->cabin,
		                    'resBookDesigCode' => $ff->fareInfoList[$xyz]->resBookDesigCode,
		                    'resBookDesigQuantity' => $seg->bookingClassList[$lowestPriceIndex]->resBookDesigQuantity,
		                ),
		                'fareInfo' => array(
		                    'cabinClassCode' => $ff->fareInfoList[$xyz]->cabinClassCode,
		                    'fareBaggageAllowance' => (array) $ff->fareInfoList[$xyz]->fareBaggageAllowance,
		                    'fareGroupName' => $ff->fareInfoList[$xyz]->fareGroupName,
		                    'fareReferenceCode' => $ff->fareInfoList[$xyz]->fareReferenceCode,
		                    'fareReferenceID' => $ff->fareInfoList[$xyz]->fareReferenceID,
		                    'fareReferenceName' => $ff->fareInfoList[$xyz]->fareReferenceName,
		                    'flightSegmentSequence' => $ff->fareInfoList[$xyz]->flightSegmentSequence,
		                    'notValidAfter' => $ff->fareInfoList[$xyz]->notValidAfter,
		                    'notValidBefore' => $ff->fareInfoList[$xyz]->notValidBefore,
		                    'resBookDesigCode' => $ff->fareInfoList[$xyz]->resBookDesigCode,
		                ),
		                'addOnSegment' => '',
		                'sequenceNumber' => '',
		                'flightSegment' => $flightSegment,
		                'involuntaryPermissionGiven' => 'false'
		            );
	            }else{
	            	$seggs = $seg;
	            	foreach($seggs as $seg){
	            		$flightSegment = (array) $seg->flightSegment;
			            $bookFlightSegmentList[] = array(
			                'actionCode' => 'NN',
			                'bookingClass' => array(
			                    'cabin' => $seg->bookingClassList[$lowestPriceIndex]->cabin,
			                    'resBookDesigCode' => $ff->fareInfoList[$xyz]->resBookDesigCode,
			                    'resBookDesigQuantity' => $seg->bookingClassList[$lowestPriceIndex]->resBookDesigQuantity,
			                ),
			                'fareInfo' => array(
			                    'cabinClassCode' => $ff->fareInfoList[$xyz]->cabinClassCode,
			                    'fareBaggageAllowance' => (array) $ff->fareInfoList[$xyz]->fareBaggageAllowance,
			                    'fareGroupName' => $ff->fareInfoList[$xyz]->fareGroupName,
			                    'fareReferenceCode' => $ff->fareInfoList[$xyz]->fareReferenceCode,
			                    'fareReferenceID' => $ff->fareInfoList[$xyz]->fareReferenceID,
			                    'fareReferenceName' => $ff->fareInfoList[$xyz]->fareReferenceName,
			                    'flightSegmentSequence' => $ff->fareInfoList[$xyz]->flightSegmentSequence,
			                    'notValidAfter' => $ff->fareInfoList[$xyz]->notValidAfter,
			                    'notValidBefore' => $ff->fareInfoList[$xyz]->notValidBefore,
			                    'resBookDesigCode' => $ff->fareInfoList[$xyz]->resBookDesigCode,
			                ),
			                'addOnSegment' => '',
			                'sequenceNumber' => '',
			                'flightSegment' => $flightSegment,
			                'involuntaryPermissionGiven' => 'false'
			            );
	            	}
	            }
	            $xyz++;
	        }
	        $bookOriginDestinationOptionList = array(
	            'bookFlightSegmentList' => $bookFlightSegmentList
	        );
	        $bookOriginDestinationOptions[] = $bookOriginDestinationOptionList;
	    } else {
	        // One Way AND Direct

	        if (is_array($data->fareComponentGroupList->fareComponentList[$lowestPriceIndex]->passengerFareInfoList)) {
	            $ff = $data->fareComponentGroupList->fareComponentList[$lowestPriceIndex]->passengerFareInfoList[0];
	        } else {
	            $ff = $data->fareComponentGroupList->fareComponentList[$lowestPriceIndex]->passengerFareInfoList;
	        }

	        $bookFlightSegmentList = array(
	            'actionCode' => 'NN',
	            'bookingClass' => array(
	                'cabin' => $data->fareComponentGroupList->boundList->availFlightSegmentList->bookingClassList[$lowestPriceIndex]->cabin,
	                'resBookDesigCode' => $ff->fareInfoList->resBookDesigCode,
	                'resBookDesigQuantity' => $data->fareComponentGroupList->boundList->availFlightSegmentList->bookingClassList[$lowestPriceIndex]->resBookDesigQuantity,
	            ),
	            'fareInfo' => array(
	                'cabinClassCode' => $ff->fareInfoList->cabinClassCode,
	                'fareBaggageAllowance' => (array) $ff->fareInfoList->fareBaggageAllowance,
	                'fareGroupName' => $ff->fareInfoList->fareGroupName,
	                'fareReferenceCode' => $ff->fareInfoList->fareReferenceCode,
	                'fareReferenceID' => $ff->fareInfoList->fareReferenceID,
	                'fareReferenceName' => $ff->fareInfoList->fareReferenceName,
	                'flightSegmentSequence' => $ff->fareInfoList->flightSegmentSequence,
	                'notValidAfter' => $ff->fareInfoList->notValidAfter,
	                'notValidBefore' => $ff->fareInfoList->notValidBefore,
	                'resBookDesigCode' => $ff->fareInfoList->resBookDesigCode,
	            ),
	            'addOnSegment' => '',
	            'sequenceNumber' => '',
	            'flightSegment' => (array) $data->fareComponentGroupList->boundList->availFlightSegmentList->flightSegment,
	            'involuntaryPermissionGiven' => 'false'
	        );
	        $bookOriginDestinationOptionList = array(
	            'bookFlightSegmentList' => $bookFlightSegmentList
	        );
	        $bookOriginDestinationOptions[] = $bookOriginDestinationOptionList;
	    }

	    $specialRequestDetails = array();
	    $specialRequestDetails['otherServiceInformations'] = array(
	        array(
	            'airTravelerSequence' => '0',
	            'code' => 'OSI',
	            'explanation' => 'CTCB 92 321 8878832',
	            'flightSegmentSequence' => '0',
	        ),
	        array(
	            'airTravelerSequence' => '0',
	            'code' => 'OSI',
	            'explanation' => 'CTCE WASIQ_WALI@YAHOO.COM',
	            'flightSegmentSequence' => '0',
	        )
	    );

	    $airTravelerSequence = 1;

	    if (is_array($data->fareComponentList[$lowestPriceIndex]->passengerFareInfoList)) {
	        $ff = $data->fareComponentList[$lowestPriceIndex]->passengerFareInfoList;
	        $airTravelerList = array();
	        $sq = 0;
	        foreach ($ff as $pInfo) {
	            if ($pInfo->passengerTypeQuantity->passengerType->code == "CHLD") {
	                foreach ($passengerData->Child as $cn) {
	                    $airTraveler = array(
	                        'unaccompaniedMinor' => '',
	                        'birthDate' => $cn->dob,
	                        'shareMarketInd' => '',
	                        'gender' => 'C',
	                        'passengerTypeCode' => $pInfo->passengerTypeQuantity->passengerType->code,
	                        'personName' => array(
	                            'shareMarketInd' => '',
	                            'givenName' => $cn->firstname,
	                            'surname' => $cn->lastname,
	                            'unaccompaniedMinor' => '',
	                        ),
	                        'requestedSeatCount' => '1',
	                        'accompaniedByInfant' => '0',
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
	                                'givenName' => $cn->firstname,
	                                'surname' => $cn->lastname,
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
	                        ),);

	                    $dob = explode("-", $cn->dob);
	                    $ssrDate = $dob [2] . strtoupper(date("M", mktime(null, null, null, $dob[1], 1))) . substr($dob[0], -2);

	                    $specialRequestDetails['specialServiceRequestList'][] = array(
	                        'airTravelerSequence' => $airTravelerSequence,
	                        'flightSegmentSequence' => '0',
	                        'SSR' => array(
	                            'code' => 'CHLD',
	                            'explanation' => $ssrDate,
	                            'allowedQuantityPerPassenger' => '',
	                            'bundleRelatedSsr' => '',
	                            'extraBaggage' => '',
	                            'free' => '',
	                            'showOnItinerary' => '',
	                            'unitOfMeasureExist' => '',
	                            'ticketed' => '',
	                        ),
	                        'serviceQuantity' => '1',
	                        'status' => 'NN',
	                        'ticketed' => '',
	                    );

	                    $airTravelerSequence++;
	                }

	                $airTravelerList[] = $airTraveler;

	                if (isset($specialRequestDetails['specialServiceRequestList'])) {
	                    
	                } else {
	                    $specialRequestDetails['specialServiceRequestList'] = array();
	                }

	            } else if ($pInfo->passengerTypeQuantity->passengerType->code == "INFT") {

	                if (isset($specialRequestDetails['specialServiceRequestList'])) {
	                    
	                } else {
	                    $specialRequestDetails['specialServiceRequestList'] = array();
	                }


	                $infAts = 1;

	                foreach ($passengerData->Infant as $in) {
	                    $dob = explode("-", $in->dob);
	                    $ssrDate = $dob [2] . strtoupper(date("M", mktime(null, null, null, $dob[1], 1))) . substr($dob[0], -2);
	                    $specialRequestDetails['specialServiceRequestList'][] = array(
	                        'airTravelerSequence' => $infAts,
	                        'flightSegmentSequence' => '1',
	                        'SSR' => array(
	                            'code' => 'INFT',
	                            'explanation' => $in->firstname . '/' . $in->lastname . ' ' . $ssrDate,
	                            'allowedQuantityPerPassenger' => '',
	                            'bundleRelatedSsr' => '',
	                            'extraBaggage' => '',
	                            'free' => '',
	                            'showOnItinerary' => '',
	                            'unitOfMeasureExist' => '',
	                            'ticketed' => '',
	                        ),
	                        'serviceQuantity' => '1',
	                        'status' => 'NN',
	                        'ticketed' => '',
	                    );
	                    if (is_array($data->boundList)) {
	                        $specialRequestDetails['specialServiceRequestList'][] = array(
	                            'airTravelerSequence' => $infAts,
	                            'flightSegmentSequence' => '2',
	                            'SSR' => array(
	                                'code' => 'INFT',
	                                'explanation' => $in->firstname . '/' . $in->lastname . ' ' . $ssrDate,
	                                'allowedQuantityPerPassenger' => '',
	                                'bundleRelatedSsr' => '',
	                                'extraBaggage' => '',
	                                'free' => '',
	                                'showOnItinerary' => '',
	                                'unitOfMeasureExist' => '',
	                                'ticketed' => '',
	                            ),
	                            'serviceQuantity' => '1',
	                            'status' => 'NN',
	                            'ticketed' => '',
	                        );
	                    }
	                    $airTravelerSequence++;
	                    $infAts++;
	                }
	            } else {
	                $infantYes = array();
	                $infY = 0;
	                foreach ($passengerData->Adult as $ad) {
	                    $infantYes[] = '0';
	                }

	                if (count($passengerData->Infant) > 0) {
	                    foreach ($passengerData->Infant as $in) {
	                        $infantYes[$infY] = '1';
	                        $infY++;
	                    }
	                }

	                $infY = 0;

	                foreach ($passengerData->Adult as $ad) {
	                    $airTraveler = array(
	                        'unaccompaniedMinor' => '',
	                        'birthDate' => $ad->dob,
	                        'shareMarketInd' => '',
	                        'gender' => ($ad->salute == "Mr") ? 'M' : 'F',
	                        'passengerTypeCode' => $pInfo->passengerTypeQuantity->passengerType->code,
	                        'personName' => array(
	                            'shareMarketInd' => '',
	                            'givenName' => $ad->firstname,
	                            'surname' => $ad->lastname,
	                            'unaccompaniedMinor' => '',
	                        ),
	                        'requestedSeatCount' => '1',
	                        'accompaniedByInfant' => $infantYes[$infY],
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
	                                'givenName' => $ad->firstname,
	                                'surname' => $ad->lastname,
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
	                        )
	                    );
	                    $airTravelerSequence++;
	                    $infY++;
	                    $airTravelerList[] = $airTraveler;
	                }
	            }

	            $sq++;
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
	    $apiObject = json_decode($request->apiObject);

	    $params = array(
	        'AirBookingRequest' => array(
	            'clientInformation' => array(
	                'clientIP' => $apiObject->clientIP,
	                'member' => '0',
	                'password' => $apiObject->password,
	                'userName' => $apiObject->userName,
	                'preferredCurrency' => 'PKR',
	            ),
	            'airItinerary' => array(
	                'bookOriginDestinationOptions' => $bookOriginDestinationOptions,
	                'adviceCodeSegmentExist' => 'false'
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
	                    'email' => 'wasiq_wali@yahoo.com',
	                    'shareContactInfo' => '',
	                ),
	                'personName' => array(
	                    'useForInvoicing' => '',
	                    'markedForSendingRezInfo' => '',
	                    'preferred' => '',
	                    'givenName' => 'MOHAMMAD WASIQ',
	                    'surname' => 'GHAZNAVI',
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
	                    'subscriberNumber' => '8878832',
	                    'shareContactInfo' => '',
	                ),
	            ),
	            'requestPurpose' => 'MODIFY_PERMANENTLY_AND_CALC',
	            'specialRequestDetails' => $specialRequestDetails,
	        )
	    );
	    return $params;
	    // print_r($params);
	    // exit();

	    // $response = $client->CreateBooking($params);

	    // echo "REQUEST:\n" . $client->__getLastRequest() . "\n";
	   // echo json_encode($response);
	   // exit();

	    return json_encode(array('PNR' => $response->AirBookingResponse->airBookingList->
	        airReservation->bookingReferenceIDList->ID, 'PNR_ID' => $pnr->id));
	}
	/********************Make Response Oneway**************/
	public static function makeResponseOneway($response,$req){
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
        $segments = array();
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
					$flight['fareComponentList'] = self::putOnZeroIndex($flight['fareComponentList']);
					foreach($flight['fareComponentList'] as $key1 => $price)
                    { 
                    	if($price['pricingOverview']['totalAmount']['value'] < $min_price)
                    	{
                    	  $min_price = $price['pricingOverview']['totalAmount']['value'];
                    	  $price_key = $key1;
                    	  $index 	 = $key1;

                    	}
                    }
        			/*---------End minimum price flight key--------*/

        			/*-------------if array key not exist 0 index-----------*/
					$flight['boundList']['availFlightSegmentList'] = self::putOnZeroIndex($flight['boundList']['availFlightSegmentList']);
        			/*-------------if array key not exist 0 index-----------*/

					$depart_time = $flight['boundList']['availFlightSegmentList'][0]['flightSegment']['departureDateTime'];

					foreach($flight['boundList']['availFlightSegmentList'] as $seg_key => $segmnt){

						/*-------------if array key not exist 0 index-----------*/
						$passengerFareInfoList = self::putOnZeroIndex($flight['fareComponentList'][$price_key]['passengerFareInfoList']);

	        			/*-------------if array key not exist 0 index-----------*/
						
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

						// $segments[$seg_key]['Cabin'] = $segmnt['bookingClassList'][$price_key]['cabin'].' ('. $segmnt['bookingClassList'][$price_key]['resBookDesigCode'] .')';

						$arraival_time = $segmnt['flightSegment']['arrivalDateTime'];
					}
					/* ---------------end segments----------------*/

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

        		$LowFareSearch['Segments'] = $segments;
        		// $LowFareSearch['TotalDuration'] = self::getDuration($arraival_time ,  $depart_time);
        		$finalData[$key]['LowFareSearch'][0] = $LowFareSearch;
        		$finalData[$key]['Fares']['CurrencyCode'] = "PKR";
        		$finalData[$key]['Fares']['TotalPrice'] = $min_price;
        		$finalData[$key]['Fares']['fareBreakDown'] = $fareBreakDown;

        		$originalData = array();
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
        // echo '<pre>';
        // print_r($finalData);
        // die();
	}
	/********************Make Response Return**************/
	public static function makeResponseReturn($response,$req){
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
}