<?php

namespace App\APIS\Sabre;

use App\Models\ApiOfferModel;
use Illuminate\Support\Facades\Storage;
class SabreClass
{
    public static function sabre_auth($apiToken) {

        $curl = curl_init();
        $headr = array();
        $headr[] = 'Authorization: Basic '.$apiToken;
        $headr[] = 'Content-Type: application/x-www-form-urlencoded';
        $headr[] = 'grant_type: client_credentials';

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headr);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, "https://api.havail.sabre.com/v2/auth/token");
        //curl_setopt($curl, CURLOPT_URL, "https://api-crt.cert.havail.sabre.com/v2/auth/token");

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $access_token = curl_exec($curl);
        curl_close($curl);
        $access_token = json_decode($access_token);
        //return $access_token;
        if (isset($access_token->access_token)) {
            return $access_token->access_token;
        } else {
            return false;
        }
    }
    public static function flightSearch($request){
        $apiObj = json_decode($request->apiObject);
        if($request->airlines==""){
            $allowedAirlinesArray = [];
        }
        else{
            $allowedAirlinesArray = explode(',', $request->airlines);
        }
        $json = '{
            "OTA_AirLowFareSearchRQ": {
                "DirectFlightsOnly":false,
              "Version": "'.$apiObj->version_bfm.'",
              "POS": {
                "Source": [
                  {
                    "PseudoCityCode": "'.$apiObj->pcc.'",
                    "RequestorID": {
                      "Type": "1",
                      "ID": "1",
                      "CompanyName": {
                        "Code": "'.$apiObj->company_code.'",
                        "content": "'.$apiObj->company_content.'"
                      }
                    }
                  }
                ]
              },
              "OriginDestinationInformation": [';
        $json.='
        {
          "RPH": "1",
          "DepartureDateTime": "' . $request->departureDate . 'T00:00:00",
          "OriginLocation": {
            "LocationCode": "' . $request->origin . '"
          },
          "DestinationLocation": {
            "LocationCode": "' . $request->destination . '"
          }
        } ';

        if ($request->trip == "Return") {
        $json.='  ,
                        {
                        "RPH": "2",
                        "DepartureDateTime": "' . $request->arrivalDate . 'T00:00:00",
                        "OriginLocation": {
                            "LocationCode": "' . $request->destination . '"
                        },
                        "DestinationLocation": {
                            "LocationCode": "' . $request->origin . '"
                        }
                        }';
        }
        $json.='
                    ],
                    "TravelPreferences": {
                        "ValidInterlineTicket": true,
                        "FlightTypePref": {
        ';
        if ($request->nonStop == "true") {
        $json .='			      	"MaxConnections": "0"';
        } else {
        $json .='			      	"MaxConnections": "5"';
        }
        $json.= '		      },
                        "CabinPref": [
                            {
                            ';
        if ($request->ticket_class == "Business") {
        $json.='			    "Cabin": "Business"';
        } else if ($request->ticket_class == "Premium") {
        $json.='			    "Cabin": "PremiumEconomy"';
        } else {
        $json.='			    "Cabin": "Economy"';
        }

        $json.='		  }
                        ]
                    },

                    "TravelerInfoSummary": {
                        "SeatsRequested": [
                        ' . ($request->adults + $request->children + $request->infants) . '
                        ],
                        "AirTravelerAvail": [
                        {
                            "PassengerTypeQuantity": [';
        if ($request->adults > 0) {
        $json.='		            {
                                "Code": "ADT",
                                "Quantity": ' . $request->adults . '
                            }';
        if ($request->children > 0 || $request->infants > 0) {
            $json.=',';
        }
        }
        if ($request->children > 0) {
        $json.='		            {
                                "Code": "CNN",
                                "Quantity": ' . $request->children . '
                            }';
        if ($request->infants > 0) {
            $json.=',';
        }
        }
        if ($request->infants > 0) {
        $json.='		            {
                                "Code": "INF",
                                "Quantity": ' . $request->infants . '
                            }';
        }
        $json.='		  ]
                        }
                        ],
                    "PriceRequestInformation": {';

        $json.='
                        "AccountCode" : [{
                                "Code" : "CCC93899"
                            },{
                                "Code" : "SCCI0121"
                            }]
                        }
                    },
                    "TPA_Extensions": {
                        "MultiTicket" :{
                            "DisplayPolicy" : "GOW2RT"
                        },
                        "IntelliSellTransaction": {
                        "RequestType": {
                            "Name": "50ITINS"
                        }
                        }
                    }
                    }
                }';
                // return $json;
                Storage::put('Sabre/FlightSearchReq.json', $json);
        $url = 'https://api.havail.sabre.com/v4.3.0/shop/flights?mode=live';
        $type = 'POST';
        $res = self::curl_action($type,$url,$json,$token = null,$apiObj->apiToken);
        // return $res;
        Storage::put('Sabre/returnFlightSearchResponse.json', $res['res']);
        // $bfm = Storage::get('Sabre/returnFlightSearchResponse.json');
        $bfm = json_decode($res['res']);
        // $bfm = json_decode($bfm);
        // return $bfm;
        if(@$bfm->errorCode){
            $res = ['status'=> '500' , 'msg' => "Somethin went wrong."];
            return $res;
        }
        else{
             // Storage::put('Sabre/returnFlightSearchResponseKey.json', $res['key']);
            // $bearer_key = Storage::get('Sabre/returnFlightSearchResponseKey.json');
            $bearer_key = $res['key'];
            $finalData = array();
            $i = 0;
            foreach ($bfm->OTA_AirLowFareSearchRS->PricedItineraries->PricedItinerary as $ait) {
                $finalData[$i]['api'] = "Sabre";
                $ait->bearerKey = $bearer_key;
                $finalData[$i]['MarketingAirline']['FareRules'] = "NA";

                $apiOffer = new ApiOfferModel();
                $apiOffer->api = "Sabre";
                $apiOffer->data = json_encode($ait);
                // return $apiOffer->data;
                $apiOffer->timestamp = time();
                $apiOffer->query = json_encode($request->except('apiToken', 'apiKey', 'apiSecret'));
                // $apiOffer->save();

                $o = 0;
                foreach ($ait->AirItinerary->OriginDestinationOptions->OriginDestinationOption as $origin) {
                    $f = 0;

                    foreach ($origin->FlightSegment as $flight) {
                        $finalData[$i]['MarketingAirline']['Airline'] = $flight->MarketingAirline->Code;
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Duration'] = "PT" . (floor($flight->ElapsedTime / 60)) . "H" . ($flight->ElapsedTime % 60) . "M";
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['OperatingAirline']['Code'] = $flight->OperatingAirline->Code;
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['OperatingAirline']['FlightNumber'] = $flight->OperatingAirline->FlightNumber;
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Departure']['LocationCode'] = $flight->DepartureAirport->LocationCode;
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Arrival']['LocationCode'] = $flight->ArrivalAirport->LocationCode;
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Departure']['DepartureDateTime'] = $flight->DepartureDateTime;
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Arrival']['ArrivalDateTime'] = $flight->ArrivalDateTime;


                        $finalData[$i]['LowFareSearch'][$o]['FareId'] = "";
                        foreach ($ait->AirItineraryPricingInfo as $aip) {
                            if (isset($aip->FareInfos)) {
                                $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Cabin'] = $request->ticket_class . ' (' . $aip->FareInfos->FareInfo[$f]->TPA_Extensions->Cabin->Cabin . ')';
                            } else if (isset($aip->Tickets)) {
                                $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Cabin'] = $request->ticket_class . ' (' . $aip->Tickets->Ticket[$o]->AirItineraryPricingInfo->FareInfos->FareInfo[0]->TPA_Extensions->Cabin->Cabin . ')';
                            }
                        }
                        $departureTime = "";
                        $arrivalTime = "";
                        $count = count($finalData[$i]['LowFareSearch'][$o]['Segments'])-1;
                        foreach($finalData[$i]['LowFareSearch'][$o]['Segments'] as $key => $seg){
                            if($key==0){
                                $departureTime = $seg['Departure']['DepartureDateTime'];
                            }
                            if($key==$count){
                                $arrivalTime = $seg['Arrival']['ArrivalDateTime'];
                            }

                        }

                        $totalDuration = self::getDuration($arrivalTime ,  $departureTime);
                        $finalData[$i]['LowFareSearch'][$o]['TotalDuration'] = $totalDuration;
                        $f++;
                    }

                    $o++;
                }

                $fareBreakDown = array();
                $baggage = array();

                foreach ($ait->AirItineraryPricingInfo as $price) {
                    $finalData[$i]['Fares']['CurrencyCode'] = $price->ItinTotalFare->TotalFare->CurrencyCode;
                    $finalData[$i]['Fares']['TotalPrice'] = $price->ItinTotalFare->TotalFare->Amount;


                    if (isset($price->PTC_FareBreakdowns)) {
                        foreach ($price->PTC_FareBreakdowns->PTC_FareBreakdown as $bd) {
                            $fareBreakDown[$bd->PassengerTypeQuantity->Code] = array('Quantity' => $bd->PassengerTypeQuantity->Quantity, 'TotalFare' => $bd->PassengerFare->TotalFare->Amount, 'TotalTax' => $bd->PassengerFare->Taxes->TotalTax->Amount,  'BaseFare' =>$bd->PassengerFare->TotalFare->Amount - $bd->PassengerFare->Taxes->TotalTax->Amount);

                            foreach ($bd->PassengerFare->TPA_Extensions->BaggageInformationList->BaggageInformation as $bag) {
                                foreach ($bag->Segment as $segBag) {
                                    $bagString = "";
                                    foreach ($bag->Allowance as $key=>$allow) {
                                        $bagString.= json_encode($allow);
                                    }
                                    $bagString = str_replace("{", "", $bagString);
                                    $bagString = str_replace("}", "", $bagString);
                                    $bagString = str_replace("\\", "", $bagString);
                                    $bagString = str_replace('"', '', $bagString);
                                        // dd($bd->PassengerTypeQuantity->Code);
                                    $baggage[$bd->PassengerTypeQuantity->Code] = $bagString;
                                }
                            }
                        }
                        // return $baggage;
                        $adt = $baggage['ADT'];
                        // return $baggage['CNN'];
                        $cnn = @$baggage['CNN'];
                        $inf = @$baggage['INF'];
                        foreach( $finalData[$i]['LowFareSearch'] as $key=>$lowfares){
                            foreach($lowfares['Segments'] as $k=> $seg){
                                $weight = '';
                                $unit = '';
                                // return $baggage['CNN'];
                                $weight = explode(":", $adt);
                                $index = count($weight) - 1 ;
                                $unit =  $weight[$index];
                                $weight = str_replace(",Unit", "", $weight[1]);
                                if($unit != "kg"){
                                    $unit = "Piece(s)";
                                }

                                $finalData[$i]['LowFareSearch'][$key]['Segments'][$k]['Baggage']['ADT']['Weight'] = $weight;
                                $finalData[$i]['LowFareSearch'][$key]['Segments'][$k]['Baggage']['ADT']['Unit'] = $unit;
                                if(@$baggage['CNN']){
                                    $weightCNN = [];
                                    $unitCNN = [];

                                    $weightCNN = explode(":", $cnn);
                                    // return $weight;
                                    $index = count($weightCNN) - 1 ;
                                    $unitCNN =  $weightCNN[$index];
                                    $weightCNN = @str_replace(",Unit", "", @$weightCNN[1]);

                                    if($unitCNN != "kg"){
                                        $unitCNN = "Piece(s)";
                                    }
                                    $finalData[$i]['LowFareSearch'][$key]['Segments'][$k]['Baggage']['CNN']['Weight'] = $weightCNN;
                                    $finalData[$i]['LowFareSearch'][$key]['Segments'][$k]['Baggage']['CNN']['Unit'] = $unitCNN;
                                }
                                if(@$baggage['INF']){
                                    $weightINF = [];
                                    $unitINF = [];
                                    $weightINF = explode(":", $inf);
                                    $index = count($weightINF) - 1 ;
                                    $unitINF =  $weightINF[$index];
                                    $weightINF = str_replace(",Unit", "", @$weightINF[1]);

                                    if($unitINF != "kg"){
                                        $unitINF = "Piece(s)";
                                    }
                                    $finalData[$i]['LowFareSearch'][$key]['Segments'][$k]['Baggage']['INF']['Weight'] = $weightINF;
                                    $finalData[$i]['LowFareSearch'][$key]['Segments'][$k]['Baggage']['INF']['Unit'] = $unitINF;
                                }
                                // $finalData[$i]['LowFareSearch'][$key]['Segments'][$k]['Baggage']['KJU'] = '';
                            }
                        }
                    } else {
                        $fareBreakDown['Total'] = array('Quantity' => 'All', 'TotalFare' => $price->ItinTotalFare->TotalFare->Amount, 'TotalTax' => $price->ItinTotalFare->Taxes->Tax[0]->Amount,  'BaseFare' =>$bd->PassengerFare->TotalFare->Amount - $bd->PassengerFare->Taxes->TotalTax->Amount);
                    }

                }
                $finalData[$i]['Fares']['fareBreakDown'] = $fareBreakDown;
                $apiOffer->finaldata = json_encode($finalData[$i]);
                if(count($allowedAirlinesArray)>0 && in_array($finalData[$i]['MarketingAirline']['Airline'], $allowedAirlinesArray)){
                    $apiOffer->save();
                    $finalData[$i]['api_offer_id'] = $apiOffer->id;
                }
                else if(count($allowedAirlinesArray)==0){
                    $apiOffer->save();
                    $finalData[$i]['api_offer_id'] = $apiOffer->id;
                }
                else{
                    unset($finalData[$i]);
                }

                $i++;
            }
        $res = ['status'=> '200' , 'msg' => $finalData];
        return $res;
        }
    }
    public static function flightSearchMulti($request){
        $apiObj = json_decode($request->apiObject);
        if($request->airlines==""){
            $allowedAirlinesArray = [];
        }
        else{
            $allowedAirlinesArray = explode(',', $request->airlines);

        }
        $request->leg = json_decode($request->leg, true);
        $count = sizeof($request->leg);
        $json = '{
                    "OTA_AirLowFareSearchRQ": {
                        "DirectFlightsOnly":false,
                    "Version": "'.$apiObj->version_bfm.'",
                    "POS": {
                        "Source": [
                            {
                                "PseudoCityCode": "'.$apiObj->pcc.'",
                                "RequestorID": {
                                "Type": "1",
                                "ID": "1",
                                "CompanyName": {
                                    "Code": "'.$apiObj->company_code.'",
                                    "content": "'.$apiObj->company_content.'"
                                }
                                }
                            }
                        ]
                    },
				    "OriginDestinationInformation": [';
              $counter = 1;
              for($i=0; $i<$count; $i++ ){
                $json.='
                {
                  "RPH": "'. $counter .'",
                  "DepartureDateTime": "' .  date("Y-m-d", strtotime($request->leg[$i]['departureDate'])) . 'T00:00:00",
                  "OriginLocation": {
                    "LocationCode": "' . $request->leg[$i]['origin'] . '"
                  },
                  "DestinationLocation": {
                    "LocationCode": "' . $request->leg[$i]['destination'] . '"
                  }
                } ';
                if($counter < $count){
                  $json.=',';
                }
                $counter++;
              }


        $json.='
                        ],
                        "TravelPreferences": {
                        "ValidInterlineTicket": true,
                        "FlightTypePref": {
            ';
        if ($request->nonStop == "true") {
            $json .='			      	"MaxConnections": "0"';
        } else {
            $json .='			      	"MaxConnections": "5"';
        }
        $json.= '		      },
                        "CabinPref": [
                            {
                                ';
        if ($request->pref == "Business") {
            $json.='			    "Cabin": "Business"';
        } else if ($request->pref == "Premium") {
            $json.='			    "Cabin": "PremiumEconomy"';
        } else {
            $json.='			    "Cabin": "Economy"';
        }

        $json.='		  }
                            ]
                        },

                        "TravelerInfoSummary": {
                        "SeatsRequested": [
                            ' . ($request->adults + $request->children + $request->infants) . '
                        ],
                        "AirTravelerAvail": [
                            {
                            "PassengerTypeQuantity": [';
        if ($request->adults > 0) {
            $json.='		            {
                                "Code": "ADT",
                                "Quantity": ' . $request->adults . '
                                }';
            if ($request->children > 0 || $request->infants > 0) {
                $json.=',';
            }
        }
        if ($request->children > 0) {
            $json.='		            {
                                "Code": "CNN",
                                "Quantity": ' . $request->children . '
                                }';
            if ($request->infants > 0) {
                $json.=',';
            }
        }
        if ($request->infants > 0) {
            $json.='		            {
                                "Code": "INF",
                                "Quantity": ' . $request->infants . '
                                }';
        }
        $json.='		  ]
                            }
                        ],
                        "PriceRequestInformation": {';
        $json.='
                            "AccountCode" : [{
                                    "Code" : "CCC93899"
                                },{
                                    "Code" : "SCCI0121"
                                }]
                            }
                        },
                        "TPA_Extensions": {
                        "MultiTicket" :{
                            "DisplayPolicy" : "GOW2RT"
                        },
                        "IntelliSellTransaction": {
                            "RequestType": {
                            "Name": "50ITINS"
                            }
                        }
                        }
                    }
                    }';
        // return $json;
        $url = 'https://api.havail.sabre.com/v4.3.0/shop/flights?mode=live';
        $type = 'POST';
        // return $json;
        $res = self::curl_action($type,$url,$json,$token = null,$apiObj->apiToken);
        // return $res;
         Storage::put('Sabre/multiFlightSearchResponse.json', $res['res']);
         $bfm = Storage::get('Sabre/multiFlightSearchResponse.json');
        $bfm = json_decode($res['res']);
        // $bfm = json_decode($bfm);
        // $error_message = "";

        if(@$bfm->errorCode){
            // $msg = json_decode($bfm->message);

            // $error =  $msg->OTA_AirLowFareSearchRS->Errors->Error;
            // foreach($error as $key=>$err){

            //     if($error_message== ""){
            //         $error_message = $err->ShortText;
            //     }
            // }
            // $res = ['status'=> '500' , 'msg' => $error_message];
            // return $res;
            $res = ['status'=> '500' , 'msg' => "Somethin went wrong."];
            return $res;
        }
        else{


            // Storage::put('Sabre/returnFlightSearchResponse.json', $res['res']);
            // $bfm = Storage::get('Sabre/returnFlightSearchResponse.json');
            // $bfm = json_decode($res['res']);
            // Storage::put('Sabre/returnFlightSearchResponseKey.json', $res['key']);
            // $bearer_key = Storage::get('Sabre/returnFlightSearchResponseKey.json');
            $bearer_key = @$res['key'];
            $finalData = array();
            $i = 0;
            foreach ($bfm->OTA_AirLowFareSearchRS->PricedItineraries->PricedItinerary as $ait) {
	            $finalData[$i]['api'] = "Sabre";
               $ait->bearerKey = $bearer_key;
                $finalData[$i]['MarketingAirline']['FareRules'] = "NA";
                $apiOffer = new ApiOfferModel();
                $apiOffer->api = "Sabre";
                $apiOffer->data = json_encode($ait);
                $apiOffer->timestamp = time();
                $apiOffer->query = json_encode($request->except('apiToken', 'apiKey', 'apiSecret'));
                // $apiOffer->save();

                $o = 0;

                foreach ($ait->AirItinerary->OriginDestinationOptions->OriginDestinationOption as $origin) {
                    $f = 0;

                    foreach ($origin->FlightSegment as $flight) {
                        $finalData[$i]['MarketingAirline']['Airline'] = $flight->MarketingAirline->Code;
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Duration'] = "PT" . (floor($flight->ElapsedTime / 60)) . "H" . ($flight->ElapsedTime % 60) . "M";
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['OperatingAirline']['Code'] = $flight->OperatingAirline->Code;
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['OperatingAirline']['FlightNumber'] = $flight->OperatingAirline->FlightNumber;
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Departure']['LocationCode'] = $flight->DepartureAirport->LocationCode;
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Arrival']['LocationCode'] = $flight->ArrivalAirport->LocationCode;
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Departure']['DepartureDateTime'] = $flight->DepartureDateTime;
                        $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Arrival']['ArrivalDateTime'] = $flight->ArrivalDateTime;


                        $finalData[$i]['LowFareSearch'][$o]['FareId'] = "";
                        foreach ($ait->AirItineraryPricingInfo as $aip) {
                            if (isset($aip->FareInfos)) {
                                $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Cabin'] = $request->ticket_class . ' (' . $aip->FareInfos->FareInfo[$f]->TPA_Extensions->Cabin->Cabin . ')';
                            } else if (isset($aip->Tickets)) {
                                $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Cabin'] = $request->ticket_class . ' (' . $aip->Tickets->Ticket[$o]->AirItineraryPricingInfo->FareInfos->FareInfo[0]->TPA_Extensions->Cabin->Cabin . ')';
                            }
                        }
                        $departureTime = "";
                        $arrivalTime = "";
                        $count = count($finalData[$i]['LowFareSearch'][$o]['Segments'])-1;
                        foreach($finalData[$i]['LowFareSearch'][$o]['Segments'] as $key => $seg){
                            if($key==0){
                                $departureTime = $seg['Departure']['DepartureDateTime'];
                            }
                            if($key==$count){
                                $arrivalTime = $seg['Arrival']['ArrivalDateTime'];
                            }

                        }

                        $totalDuration = self::getDuration($arrivalTime ,  $departureTime);
                        $finalData[$i]['LowFareSearch'][$o]['TotalDuration'] = $totalDuration;
                        $f++;
                    }

                    $o++;
                }

                $fareBreakDown = array();
                $baggage = array();

                foreach ($ait->AirItineraryPricingInfo as $price) {
                    $finalData[$i]['Fares']['CurrencyCode'] = $price->ItinTotalFare->TotalFare->CurrencyCode;
                    $finalData[$i]['Fares']['TotalPrice'] = $price->ItinTotalFare->TotalFare->Amount;


                    if (isset($price->PTC_FareBreakdowns)) {
                        foreach ($price->PTC_FareBreakdowns->PTC_FareBreakdown as $bd) {
                            $fareBreakDown[$bd->PassengerTypeQuantity->Code] = array('Quantity' => $bd->PassengerTypeQuantity->Quantity, 'TotalFare' => $bd->PassengerFare->TotalFare->Amount, 'TotalTax' => $bd->PassengerFare->Taxes->TotalTax->Amount,'BaseFare' =>$bd->PassengerFare->TotalFare->Amount - $bd->PassengerFare->Taxes->TotalTax->Amount);

                            foreach ($bd->PassengerFare->TPA_Extensions->BaggageInformationList->BaggageInformation as $bag) {
                                foreach ($bag->Segment as $segBag) {
                                    $bagString = "";
                                    foreach ($bag->Allowance as $key=>$allow) {
                                        $bagString.= json_encode($allow);
                                    }
                                    $bagString = str_replace("{", "", $bagString);
                                    $bagString = str_replace("}", "", $bagString);
                                    $bagString = str_replace("\\", "", $bagString);
                                    $bagString = str_replace('"', '', $bagString);
                                        // dd($bd->PassengerTypeQuantity->Code);
                                    $baggage[$bd->PassengerTypeQuantity->Code] = $bagString;
                                }
                            }
                        }
                        // return $baggage;
                        $adt = $baggage['ADT'];
                        // return $baggage['CNN'];
                        $cnn = @$baggage['CNN'];
                        $inf = @$baggage['INF'];
                        foreach( $finalData[$i]['LowFareSearch'] as $key=>$lowfares){
                            foreach($lowfares['Segments'] as $k=> $seg){
                                $weight = '';
                                $unit = '';
                                // return $baggage['CNN'];
                                $weight = explode(":", $adt);
                                $index = count($weight) - 1 ;
                                $unit =  $weight[$index];
                                $weight = str_replace(",Unit", "", $weight[1]);
                                if($unit != "kg"){
                                    $unit = "Piece(s)";
                                }

                                $finalData[$i]['LowFareSearch'][$key]['Segments'][$k]['Baggage']['ADT']['Weight'] = $weight;
                                $finalData[$i]['LowFareSearch'][$key]['Segments'][$k]['Baggage']['ADT']['Unit'] = $unit;
                                if(@$baggage['CNN']){
                                    $weightCNN = [];
                                    $unitCNN = [];

                                    $weightCNN = explode(":", $cnn);
                                    // return $weight;
                                    $index = count($weightCNN) - 1 ;
                                    $unitCNN =  $weightCNN[$index];
                                    $weightCNN = @str_replace(",Unit", "", @$weightCNN[1]);

                                    if($unitCNN != "kg"){
                                        $unitCNN = "Piece(s)";
                                    }
                                    $finalData[$i]['LowFareSearch'][$key]['Segments'][$k]['Baggage']['CNN']['Weight'] = $weightCNN;
                                    $finalData[$i]['LowFareSearch'][$key]['Segments'][$k]['Baggage']['CNN']['Unit'] = $unitCNN;
                                }
                                if(@$baggage['INF']){
                                    $weightINF = [];
                                    $unitINF = [];
                                    $weightINF = explode(":", $inf);
                                    $index = count($weightINF) - 1 ;
                                    $unitINF =  $weightINF[$index];
                                    $weightINF = str_replace(",Unit", "", @$weightINF[1]);

                                    if($unitINF != "kg"){
                                        $unitINF = "Piece(s)";
                                    }
                                    $finalData[$i]['LowFareSearch'][$key]['Segments'][$k]['Baggage']['INF']['Weight'] = $weightINF;
                                    $finalData[$i]['LowFareSearch'][$key]['Segments'][$k]['Baggage']['INF']['Unit'] = $unitINF;
                                }
                            }
                        }
                    } else {
                        $fareBreakDown['Total'] = array('Quantity' => 'All', 'TotalFare' => $price->ItinTotalFare->TotalFare->Amount, 'TotalTax' => $price->ItinTotalFare->Taxes->Tax[0]->Amount);
                    }

                }
                $finalData[$i]['Fares']['fareBreakDown'] = $fareBreakDown;
                $apiOffer->finaldata = json_encode($finalData[$i]);
                if(count($allowedAirlinesArray)>0 && in_array($finalData[$i]['MarketingAirline']['Airline'], $allowedAirlinesArray)){
                    $apiOffer->save();
                    $finalData[$i]['api_offer_id'] = $apiOffer->id;
                }
                else if(count($allowedAirlinesArray)==0){
                    $apiOffer->save();
                    $finalData[$i]['api_offer_id'] = $apiOffer->id;
                }
                else{
                    unset($finalData[$i]);
                }
                $i++;

            }
            $res = ['status'=> '200' , 'msg' => $finalData];
            return $res;
        }
    }
    public static function pnr($request){
        //return 123;
        $apiObj = json_decode($request->apiObject);
      $data = $request->data;
      $segment = "";
      $numberInParty = 0;

      $request->passengerData = json_decode($request->passengerData,true);
      // return $request->passengerData;
      if(array_key_exists("Adult",$request->passengerData)){
          $numberInParty += count($request->passengerData['Adult']);
      }
      if(array_key_exists("Child",$request->passengerData)){
          $numberInParty += count($request->passengerData['Child']);
      }
      // if(array_key_exists("Infant",$request->passengerData)){
      //     $numberInParty += count($request->passengerData['Infant']);
      // }

      $data = json_decode($data);
      foreach ($data->AirItinerary->OriginDestinationOptions->OriginDestinationOption as $leg) {
        // return $leg;
          foreach ($leg->FlightSegment as $seg) {

              $segment .='  {';
                // return 123;
              $segment .='    "DepartureDateTime": "' . $seg->DepartureDateTime . '",';
              $segment .='    "FlightNumber": "' . $seg->FlightNumber . '",';
              $segment .='    "NumberInParty": "' . $numberInParty . '",'; //dynamic
              $segment .='    "ResBookDesigCode": "' . $seg->ResBookDesigCode . '",';
              $segment .='    "Status": "NN",';
              // $segment .='    "InstantPurchase": true,';
              $segment .='    "DestinationLocation": {';
              $segment .='      "LocationCode": "' . $seg->ArrivalAirport->LocationCode . '"';
              $segment .='    },';
              $segment .='    "MarketingAirline": {';
              $segment .='      "Code": "' . $seg->MarketingAirline->Code . '",';
              $segment .='      "FlightNumber": "' . $seg->FlightNumber . '"';
              $segment .='    },';
              $segment .='    "MarriageGrp": "' . $seg->MarriageGrp . '",';
              $segment .='    "OriginLocation": {';
              $segment .='      "LocationCode": "' . $seg->DepartureAirport->LocationCode . '"';
              $segment .='    }';
              $segment .='  },';
              // return $seg['MarketingAirline']['Code'];

          }
      }

      $segment = substr($segment, 0, -1);

      $personName = '';
      $nameSelect = '';
      $passengerType = '';
      $secureFlight = '';
      $ssr = '';

      $adultToTakeInfant = array();
      $passengerSequence = 1;

      if (count($request->passengerData['Adult']) > 0) {
          foreach ($request->passengerData['Adult'] as $ad) {
          //  return $ad;
              if ($passengerSequence > 1) {
                  $personName.=',';
                  $nameSelect.=',';
                  $secureFlight.=',';
              }
              $personName.='{';
              $personName.='    "NameNumber": "' . $passengerSequence . '.1",';
              $personName.='    "PassengerType": "ADT",';
              // $personName.='    "NameReference": "",';
              $personName.='    "GivenName": "' . $ad['firstname'] . '",';
              $personName.='    "Surname": "' . $ad['lastname'] . '"';
              $personName.='}';

              $nameSelect.='{';
              $nameSelect.='	"NameNumber": "' . $passengerSequence . '.1"';
              $nameSelect.='}';

              $secureFlight.='{
                        "SegmentNumber": "A",
                        "PersonName": {
                          "DateOfBirth": "' . date("Y-m-d", strtotime($ad['dob'])) . '",';
              if ($ad['salute'] == "Mr") {
                  $secureFlight.='        "Gender": "M",';
              } else {
                  $secureFlight.='        "Gender": "F",';
              }

              $secureFlight.='        "NameNumber": "' . $passengerSequence . '.1",
                          "GivenName": "' . $ad['firstname'] . '",
                          "Surname": "' . $ad['lastname'] . '"
                        },
                        "VendorPrefs": {
                          "Airline": {
                            "Hosted": false
                          }
                        }
                      }';

              $adultToTakeInfant[] = $passengerSequence . '.1';

              $passengerSequence++;
          }

          $passengerType.='{';
          $passengerType.='  "Code": "ADT",';
          $passengerType.='  "Quantity": "' . count($request->passengerData['Adult']) . '"';
          $passengerType.='}';
      }


      if(array_key_exists("Child", $request->passengerData)){
        if (count($request->passengerData['Child']) > 0) {
          foreach ($request->passengerData['Child'] as $cn) {
              if ($passengerSequence > 1) {
                  $personName.=',';
                  $nameSelect.=',';
                  $secureFlight.=',';
              }

              $date1 = $cn['dob'];
              $date2 = date('Y-m-d');

              $diff = abs(strtotime($date2) - strtotime($date1));

              $years = floor($diff / (365 * 60 * 60 * 24));

              $personName.='{';
              $personName.='    "NameNumber": "' . $passengerSequence . '.1",';
              $personName.='    "PassengerType": "CNN",';
              // $personName.='    "NameReference": "*C'. $years.'",';
              $personName.='    "GivenName": "' . $cn['firstname'] . '*C' . $years . '",';
              $personName.='    "Surname": "' . $cn['lastname'] . '"';
              $personName.='}';

              $nameSelect.='{';
              $nameSelect.='	"NameNumber": "' . $passengerSequence . '.1"';
              $nameSelect.='}';

              $secureFlight.='{
                        "SegmentNumber": "A",
                        "PersonName": {
                          "DateOfBirth": "' . date("Y-m-d", strtotime($cn['dob'])) . '",';
              if ($cn['salute'] == "Master") {
                  $secureFlight.='        "Gender": "M",';
              } else {
                  $secureFlight.='        "Gender": "F",';
              }

              $secureFlight.='        "NameNumber": "' . $passengerSequence . '.1",
                          "GivenName": "' . $cn['firstname'] . '",
                          "Surname": "' . $cn['lastname'] . '"
                        },
                        "VendorPrefs": {
                          "Airline": {
                            "Hosted": false
                          }
                        }
                      }';
                if ($ssr != "") {
                  $ssr.=',';
                }

                $dob = explode("-", $cn['dob']);
                // $ssrDate = $dob[2] . strtoupper(date("M", mktime(0, 0, 0, $dob[1], 1))) . substr($dob[0], -2);
                $ssr.='   {
                              "SSR_Code": "CHLD",
                              "PersonName": {
                                  "NameNumber" : "' . $passengerSequence . '.1"
                              },
                              "Text": "' . date("dMy", strtotime($cn['dob'])) . '"
                          }';

              $passengerSequence++;

          }

          if ($passengerType != "") {
              $passengerType.=',';
          }
          $passengerType.='{';
          $passengerType.='  "Code": "CNN",';
          $passengerType.='  "Quantity": "' . count($request->passengerData['Child']) . '"';
          $passengerType.='}';
        }
      }

      $infantUsed = 0;
      if(array_key_exists("Infant", $request->passengerData)){
        if (count($request->passengerData['Infant']) > 0) {
          foreach ($request->passengerData['Infant'] as $in) {
              if ($passengerSequence > 1) {
                  $personName.=',';
                  $nameSelect.=',';
                  $secureFlight.=',';
              }

              $date1 = $in['dob'];
              $date2 = date('Y-m-d');
              $diff = abs(strtotime($date2) - strtotime($date1));
              $months = floor(($diff) / (30 * 60 * 60 * 24));

              $personName.='{';
              $personName.='    "NameNumber": "' . $passengerSequence . '.1",';
              $personName.='    "PassengerType": "INF",';
              // $personName.='    "NameReference": "*I'. $months.'",';
              $personName.='    "GivenName": "' . $in['firstname'] . '*I' . $months . '",';
              $personName.='    "Surname": "' . $in['lastname'] . '",';
              $personName.='	  "Infant": true';
              $personName.='}';

              $nameSelect.='{';
              $nameSelect.='	"NameNumber": "' . $passengerSequence . '.1"';
              $nameSelect.='}';

              $secureFlight.='{
                        "SegmentNumber": "A",
                        "PersonName": {
                          "DateOfBirth": "' . date("Y-m-d", strtotime($in['dob'])) . '",';
              if ($in['salute'] == "Master") {
                  $secureFlight.='        "Gender": "MI",';
              } else {
                  $secureFlight.='        "Gender": "FI",';
              }

              $secureFlight.='        "NameNumber": "' . $adultToTakeInfant[$infantUsed] . '",
                          "GivenName": "' . $in['firstname'] . '",
                          "Surname": "' . $in['lastname'] . '"
                        },
                        "VendorPrefs": {
                          "Airline": {
                            "Hosted": false
                          }
                        }
                      }';

              if ($ssr != "") {
                  $ssr.=',';
              }

              $dob = explode("-", $in['dob']);
              // $ssrDate = $dob[2] . strtoupper(date("M", mktime(0, 0, 0, $dob[1], 1))) . substr($dob[0], -2);
              $ssr.='	{
                              "SSR_Code": "INFT",
                              "PersonName": {
                                  "NameNumber" : "' . $adultToTakeInfant[$infantUsed] . '"
                              },
                              "Text": "' . $in['lastname'] . '/' . $in['firstname'] . '/' . date("dMy", strtotime($in['dob'])) . '"
                          }';

              $passengerSequence++;
              $infantUsed++;
          }

          if ($passengerType != "") {
              $passengerType.=',';
          }
          $passengerType.='{';
          $passengerType.='  "Code": "INF",';
          $passengerType.='  "Quantity": "' . count($request->passengerData['Infant']) . '"';
          $passengerType.='}';
        }
      }




      $json = '{
          "CreatePassengerNameRecordRQ": {
            "version": "'.$apiObj->version_pnr.'",
            "targetCity": "'.$apiObj->targetCity.'",
            "haltOnAirPriceError": true,
            "TravelItineraryAddInfo": {
              "AgencyInfo": {
                "Address": {
                  "AddressLine": "'.@$apiObj->AddressLine.'",
                  "CityName": "'.$apiObj->CityName.'",
                  "CountryCode": "'.$apiObj->CountryCode.'",
                  "PostalCode": "'.$apiObj->PostalCode.'",
                  "StateCountyProv": {
                    "StateCode": "'.$apiObj->StateCode.'"
                  },
                  "StreetNmbr": "'.$apiObj->StreetNmbr.'"
                },
                "Ticketing": {
                  "TicketType": "7TAW"
                }
              },
              "CustomerInfo": {
                "ContactNumbers": {
                  "ContactNumber": [
                    {
                      "NameNumber": "1.1",
                      "Phone": "' . $request->passenger_phone . '",
                      "PhoneUseType": "M"
                    }
                  ]
                },
                "Email": [{
                    "Address":"' . $request->passenger_email . '"
                }],

                "PersonName": [' . $personName . ']
              }
            },
            "AirBook": {
              "HaltOnStatus": [
                {
                  "Code": "HL"
                },
                {
                  "Code": "KK"
                },
                {
                  "Code": "LL"
                },
                {
                  "Code": "NN"
                },
                {
                  "Code": "NO"
                },
                {
                  "Code": "UC"
                },
                {
                  "Code": "US"
                }
              ],
              "OriginDestinationInformation": {
                "FlightSegment": [
                  ' . $segment . '
                ]
              },
              "RedisplayReservation": {
                "NumAttempts": 3,
                "WaitInterval": 1500
              }
            },
            "AirPrice": [
              {
                "PriceComparison": {
                  "AmountSpecified": ' . $data->AirItineraryPricingInfo[0]->ItinTotalFare->TotalFare->Amount . ',
                  "AcceptablePriceIncrease": {
                    "HaltOnNonAcceptablePrice": true,
                    "Amount": 1000
                  }
                },
                "PriceRequestInformation": {
                  "Retain": true,
                  "OptionalQualifiers": {
                    "FOP_Qualifiers": {
                      "BasicFOP": {
                        "Type": "CK"
                      }
                    },
                    "PricingQualifiers": {
                      "NameSelect": [' . $nameSelect . '],
                      "PassengerType": [' . $passengerType . '],
                      "Corporate" : {
                          "ID" : ["BTA03"]
                      }
                    }
                  }
                }
              }
            ],
            "SpecialReqDetails": {
              "AddRemark": {
                "RemarkInfo": {
                  "FOP_Remark": {
                    "Type": "CHECK"
                  }
                }
              },
              "SpecialService": {
                "SpecialServiceInfo": {
                  "SecureFlight": [' . $secureFlight . '],
                  "Service": [' . $ssr . ']
                }
              }
            },
            "PostProcessing": {
              "RedisplayReservation": {
                "waitInterval": 100
              },
              "EndTransaction": {
                "Source": {
                  "ReceivedFrom": "RAKAM API"
                }
              }
            }
          }
        }';
        // return $json;

        $url = 'https://api.havail.sabre.com/v2.2.0/passenger/records?mode=create';
        // $url = 'https://api-crt.cert.havail.sabre.com/v2.2.0/passenger/records?mode=create';
        $type = 'POST';
        $key = $data->bearerKey;

        /**************for testing******************/
        // $respon = Storage::get('Sabre/pnr/pnrResp2022-04-12-11-28-22.json');
        // $res['res'] = $respon;
        // return $res;



        $res = self::curl_action($type,$url,$json,$key);
        Storage::put('Sabre/pnr/pnrResp'.date('Y-m-d-H-i-s').'.json', $res['res']);

        if(@$res){
            $resp = json_decode($res['res']);

            $status = $resp->CreatePassengerNameRecordRS->ApplicationResults->status;
            $lastTicketingDate = @$resp->CreatePassengerNameRecordRS->AirPrice[0]->PriceQuote->MiscInformation->HeaderInformation[0]->LastTicketingDate;

            if($status == 'Complete'){
                $finalData = ['status'=> '200' , 'msg' => json_encode($resp) ,  'pnr'=> $resp->CreatePassengerNameRecordRS->ItineraryRef->ID,'lastTicketingDate'=>$lastTicketingDate];
            }else{
                Storage::put('Sabre/pnr/pnrRequset'.date('Y-m-d-H-i-s').'.json', $json);
                Storage::put('Sabre/pnr/pnrError'.date('Y-m-d-H-i-s').'.json', $res['res']);
                $message = $resp->CreatePassengerNameRecordRS->ApplicationResults->Error[0]->SystemSpecificResults[0]->Message[0]->content;

                if(@$message){
                    $finalData = ['status'=> '500' ,'error' =>$message, 'msg' => $message];
                }else{
                    $finalData = ['status'=> '500' ,'error' => 'Unable to perform air booking step', 'msg' => 'Unable to perform air booking step'];
                }
            }
            return $finalData;
        }
        else{
            $res = ['status'=> '500' , 'msg' => "Somethin went wrong."];
            return $res;
        }
    }
    public static function ticket($request){
        $apiObj = json_decode($request->apiObject);
        $data = json_decode($request->pnrResponse);
        $pnrCode = $data->CreatePassengerNameRecordRS->ItineraryRef->ID;
        $nuber = 1;
        $priceQuote = '';
        $passengerData = $data->CreatePassengerNameRecordRS->TravelItineraryRead->TravelItinerary->CustomerInfo->PersonName;
        // return $passengerData;

        foreach($passengerData as $key => $attr){
            if ($attr->PassengerType == 'ADT') {
                if ($priceQuote != "") {
                    $priceQuote.=',';
                }
                $priceQuote.='{
        		              "Record": [
        		                {
        		                  "Number": ' . $nuber . '
        		                }
        		              ]
        		            }';
                $nuber++;
            }
            if ($attr->PassengerType == 'CNN') {
                if ($priceQuote != "") {
                    $priceQuote.=',';
                }
                $priceQuote.='{
        		              "Record": [
        		                {
        		                  "Number": ' . $nuber . '
        		                }
        		              ]
        		            }';
                $nuber++;
            }
            if ($attr->PassengerType == 'INF') {
                if ($priceQuote != "") {
                    $priceQuote.=',';
                }
                $priceQuote.='{
        		              "Record": [
        		                {
        		                  "Number": ' . $nuber . '
        		                }
        		              ]
        		            }';
                $nuber++;
            }
        }

        $json = '{
        "AirTicketRQ": {
            "version": "'.$apiObj->version_ticket.'",
            "targetCity": "'.$apiObj->targetCity.'",
            "DesignatePrinter": {
            "Printers": {
                "Ticket": {
                "CountryCode": "'.$apiObj->CountryCode.'"
                },
                "Hardcopy":{
                    "LNIATA": "'.$apiObj->LNIATA.'"
                }
            }
            },
            "Itinerary": {
            "ID": "' . $pnrCode . '"
            },
            "Ticketing": [
            {
                "MiscQualifiers": {
                "Commission": {
                    "Percent": 0
                }
                },
                "PricingQualifiers": {
                "PriceQuote": [' . $priceQuote . ']
                }
            }
            ],
            "PostProcessing": {
            "EndTransaction": {
                "Source": {
                "ReceivedFrom": "RAKAM API"
                }
            }
            }
        }
        }';
        // return $json;
        $url = 'https://api.havail.sabre.com/v1.2.1/air/ticket';
        $type = 'POST';
        $res = self::curl_action($type,$url,$json);
        Storage::put('Sabre/ticket/ticketRes'.date('Y-m-d-H-i-s').'.json', $res);
        $res = ['status'=> '200' , 'msg' => json_encode($res['res'])];
        return $res;
    }
    public static function rules($request){
      //$request->data = json_decode($request->data,true);
     $request;
      $auth = self::authFareRules();
      // return $auth;
      $res = self::getRules($auth ,  $request);
      return $res;
    }
    public static function authFareRules(){
      $input_xml = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
      <SOAP-ENV:Header>
          <MessageHeader xmlns="http://www.ebxml.org/namespaces/messageHeader">
              <From>
                  <PartyId>Agency</PartyId>
              </From>
              <To>
                  <PartyId>Sabre_API</PartyId>
              </To>
              <ConversationId>2019.09.DevStudio</ConversationId>
              <Action>SessionCreateRQ</Action>
          </MessageHeader>
          <Security xmlns="http://schemas.xmlsoap.org/ws/2002/12/secext">
              <UsernameToken>
                  <Username>9999</Username>
                  <Password>sprdra2</Password>
                  <Organization>LK6D</Organization>
                  <Domain>DEFAULT</Domain>
              </UsernameToken>
          </Security>
      </SOAP-ENV:Header>
      <SOAP-ENV:Body>
          <SessionCreateRQ Version="1.0.0" xmlns="http://www.opentravel.org/OTA/2002/11"/>
      </SOAP-ENV:Body>
      </SOAP-ENV:Envelope>';

      //    $url = 'https://sws-crt.cert.havail.sabre.com';
      $url = 'https://webservices.havail.sabre.com';
      $action = 'SessionCreateRQ'; // Set this to whatever Sabre API action you are calling

      $soapXML = $input_xml; // Your SOAP XML

      $headers = array(
          'Content-Type: text/xml; charset="utf-8"',
          'Content-Length: ' . strlen($soapXML),
          'Accept: text/xml',
          'Keep-Alive: 300',
          'Connection: keep-alive',
          'Cache-Control: no-cache',
          'Pragma: no-cache',
          'SOAPAction: "' . $action . '"'
      );

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 60);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $soapXML);

      $data = curl_exec($ch);
      if (curl_error($ch)) {
          echo "Curl error: " . curl_error($ch);
      } else {
          // echo $data;
          // $data = simplexml_load_string($data);
          // print_r($data);
      //        die($data);
          $plainXML = self::mungXML(trim($data));
          $arrayResult = json_decode(json_encode(SimpleXML_Load_String($plainXML, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
          return $arrayResult;
          // print_r($arrayResult);
      }
    }
    public static function getRules($auth, $offer){
      $buyingDate = str_replace(' ', 'T', date('Y-m-d H:m:s', time()));
      $passengerData = array();
       $offer = json_decode($offer);
      // $offer = json_decode($offer, true);
      //return gettype($offer);
      foreach ($offer->AirItineraryPricingInfo[0]->PTC_FareBreakdowns->PTC_FareBreakdown as $b) {
          $passengerData[$b->PassengerTypeQuantity->Code]['qty'] = $b->PassengerTypeQuantity->Quantity;
          foreach ($b->FareBasisCodes->FareBasisCode as $code) {
             // die(json_encode($code));
              $passengerData[$b->PassengerTypeQuantity->Code]['fareBasisCode'][] = $code->content;
          }
      }
     //die(json_encode($passengerData));
     // $url = 'https://sws-crt.cert.havail.sabre.com';
      $url = 'https://webservices.havail.sabre.com';
      $input_xml2 = '
      <StructureFareRulesRQ Version="1.0.5" xmlns="http://webservices.sabre.com/sabreXML/2003/07" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
          <PriceRequestInformation BuyingDate="' . $buyingDate . '" CurrencyCode="PKR">
            <PassengerTypes>';

      foreach ($passengerData as $type => $p) {
          $input_xml2 .= '        <PassengerType Code="' . $type . '" Count="' . $p['qty'] . '" />';
      }

      $input_xml2 .= '        </PassengerTypes>
            <ReturnAllData Value="1" />
            <FreeBaggageSubscriber Ind="true" />
          </PriceRequestInformation>
          <AirItinerary>
                            <OriginDestinationOptions>';
      foreach ($offer->AirItinerary->OriginDestinationOptions->OriginDestinationOption as $key => $flight) {
          foreach ($flight->FlightSegment as $segIndex => $seg) {
              $input_xml2 .= '
                                <OriginDestinationOption>
                      <FlightSegment DepartureDate="' . $seg->DepartureDateTime . '" ArrivalDate="' . $seg->ArrivalDateTime . '" BookingDate="' . $buyingDate . '" FlightNumber="' . $seg->FlightNumber . '" RealReservationStatus="SS" ResBookDesigCode="' . $seg->ResBookDesigCode . '" SegmentNumber="' . ($segIndex + 1) . '" SegmentType="A">
                        <DepartureAirport LocationCode="' . $seg->DepartureAirport->LocationCode . '" />
                        <ArrivalAirport LocationCode="' . $seg->ArrivalAirport->LocationCode . '" />
                        <MarketingAirline Code="' . $seg->MarketingAirline->Code . '" />
                        <OperatingAirline Code="' . $seg->OperatingAirline->Code . '" />
                      </FlightSegment>
                      <SegmentInformation SegmentNumber="' . ($key + 1) . '"/>';
              foreach ($passengerData as $passengerType => $passenger) {
                  $input_xml2 .= '<PaxTypeInformation PassengerType="' . $passengerType . '" FareComponentNumber="' . ($key + 1) . '" FareBasisCode="' . $passenger['fareBasisCode'][$segIndex] . '" />';
              }
              $input_xml2 .= '    </OriginDestinationOption>';
          }
      }
      // die;

      $input_xml2 .= '
                              </OriginDestinationOptions>
          </AirItinerary>
         </StructureFareRulesRQ>';
      // echo($input_xml2);
      // die;
      // echo $auth;
      // $auth = json_decode($auth, true);
      // $auth = self::mungXML(trim($auth));
      // $auth = json_decode($auth, true);
      // return gettype($auth);
      $sabreReq = '<?xml version="1.0" encoding="UTF-8"?>
            <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eb="http://www.ebxml.org/namespaces/messageHeader" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsd="http://www.w3.org/1999/XMLSchema">
              <SOAP-ENV:Header>
                <eb:MessageHeader SOAP-ENV:mustUnderstand="1" eb:version="1.0">
                  <eb:From>
                    <eb:PartyId />
                  </eb:From>
                  <eb:To>
                    <eb:PartyId />
                  </eb:To>
                  <eb:CPAId>LK6D</eb:CPAId>
                  <eb:ConversationId>' . $auth['soap-env_Body']['SessionCreateRS']['ConversationId'] . '</eb:ConversationId>
                  <eb:Service>StructureFareRulesRQ</eb:Service>
                  <eb:Action>StructureFareRulesRQ</eb:Action>
                  <eb:MessageData>
                    <eb:MessageId>mid:20001209-133003-2333@clientofsabre.com</eb:MessageId>
                    <eb:Timestamp>2001-02-15T11:15:12Z</eb:Timestamp>
                    <eb:TimeToLive>2001-02-15T11:15:12Z</eb:TimeToLive>
                  </eb:MessageData>
                </eb:MessageHeader>
                <wsse:Security xmlns:wsse="http://schemas.xmlsoap.org/ws/2002/12/secext">
                  <wsse:BinarySecurityToken valueType="String" EncodingType="wsse:Base64Binary">' . $auth['soap-env_Header']['wsse_Security']['wsse_BinarySecurityToken'] . '</wsse:BinarySecurityToken>
                </wsse:Security>
              </SOAP-ENV:Header>
              <SOAP-ENV:Body>
                  ' . $input_xml2 . '
              </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>';

      $action = 'StructureFareRulesRQ'; // Set this to whatever Sabre API action you are calling

      $soapXML = $sabreReq; // Your SOAP XML

      $headers = array(
          'Content-Type: text/xml; charset="utf-8"',
          'Content-Length: ' . strlen($soapXML),
          'Accept: text/xml',
          'Keep-Alive: 300',
          'Connection: keep-alive',
          'Cache-Control: no-cache',
          'Pragma: no-cache',
          'SOAPAction: "' . $action . '"'
      );

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 60);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $soapXML);

      $data = curl_exec($ch);
      //return $data;
      if (curl_error($ch)) {
          echo "Curl error: " . curl_error($ch);
      } else {
         // echo $data;
          // $data = simplexml_load_string($data);
          // print_r($data);
          $plainXML = self::mungXML(trim($data));
          $response = json_decode(json_encode(SimpleXML_Load_String($plainXML, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

         // die(json_encode($response['soap-env_Body']['StructureFareRulesRS']));

          $penalties = array();
          if (array_key_exists('Success', $response['soap-env_Body']['StructureFareRulesRS'])) {
              $ruleType = '';
              $responsePenalties = array();
              if (count($passengerData) > 1) {
                  foreach ($response['soap-env_Body']['StructureFareRulesRS']['Summary']['PassengerDetails']['PassengerDetail'] as $p) {
                      $personType = ($p['@attributes']['PassengerTypeCode'] == 'ADT') ? 'Adult' : (($p['@attributes']['PassengerTypeCode'] == 'CNN') ? 'Child' : 'Infant');
                      $responsePenalties[$personType] = $p['PenaltiesInfo']['Penalty'];
                  }
              } else {
                  $responsePersonType = $response['soap-env_Body']['StructureFareRulesRS']['Summary']['PassengerDetails']['PassengerDetail']['@attributes']['PassengerTypeCode'];
                  $personType = ($responsePersonType == 'ADT') ? 'Adult' : (($responsePersonType == 'CNN') ? 'Child' : 'Infant');
                  $responsePenalties[$personType] = $response['soap-env_Body']['StructureFareRulesRS']['Summary']['PassengerDetails']['PassengerDetail']['PenaltiesInfo']['Penalty'];
              }
              foreach ($responsePenalties as $passengerType => $row) {
                  $p = '<h5 class="text-center">Each ' . $passengerType . '</h5>';
                  foreach ($row as $penalty) {
                      if ($ruleType != $penalty['@attributes']['Type']) {
                          $ruleType = $penalty['@attributes']['Type'];
                          $heading = '<b><u>' . $penalty['@attributes']['Type'] . 'able</u></b> <br>';
                      } else {
                          $heading = '';
                      }
                      $p .= $heading . $penalty['@attributes']['Applicability'] . ' Departure: ';
                      if (array_key_exists('Refundable', $penalty['@attributes'])) {
                          $p .= ($penalty['@attributes']['Refundable'] == "false") ? '<b>Not Available</b><br>' : '<b>Available</b>';
                      } else if (array_key_exists('Cat16Info', $penalty['@attributes'])) {
                          $p .= ($penalty['@attributes']['Cat16Info'] == "false") ? '<b>Not Available</b>' : '<b>Available</b>';
                      } else if (array_key_exists('Changeable', $penalty['@attributes'])) {
                          $p .= ($penalty['@attributes']['Changeable'] == "false") ? '<b>Not Available</b><br>' : '<b>Available</b>';
                      }
                      if (array_key_exists('Amount', $penalty['@attributes'])) {
                          $p .= '<br>Charges: <b>' . number_format($penalty['@attributes']['Amount'], 0, '.', ',') . ' ' . $penalty['@attributes']['CurrencyCode'] . '</b><br>';
                      }
                  }
                  $penalties[] = $p;
              }
          }
          // print_r($penalties);
          // die;
          return $penalties;
          //
      }
    }
    public static function mungXML($xml){
      $obj = SimpleXML_Load_String($xml);
      if ($obj === FALSE)
          return $xml;
      // GET NAMESPACES, IF ANY
      $nss = $obj->getNamespaces(TRUE);
      if (empty($nss))
          return $xml;
      // CHANGE ns: INTO ns_
      $nsm = array_keys($nss);
      foreach ($nsm as $key) {
          // A REGULAR EXPRESSION TO MUNG THE XML
          $rgx = '#'               // REGEX DELIMITER
                  . '('               // GROUP PATTERN 1
                  . '\<'              // LOCATE A LEFT WICKET
                  . '/?'              // MAYBE FOLLOWED BY A SLASH
                  . preg_quote($key)  // THE NAMESPACE
                  . ')'               // END GROUP PATTERN
                  . '('               // GROUP PATTERN 2
                  . ':{1}'            // A COLON (EXACTLY ONE)
                  . ')'               // END GROUP PATTERN
                  . '#'               // REGEX DELIMITER
          ;
          // INSERT THE UNDERSCORE INTO THE TAG NAME
          $rep = '$1'          // BACKREFERENCE TO GROUP 1
                  . '_'           // LITERAL UNDERSCORE IN PLACE OF GROUP 2
          ;
          // PERFORM THE REPLACEMENT
          $xml = preg_replace($rgx, $rep, $xml);
      }

      return $xml;
     }
    public static function curl_action($type,$url,$data,$key = null , $apiToken = null)
    {
        if(!$key){
            $key = self::sabre_auth($apiToken);
            //return $key;
        }
        if($key){
            $curl2 = curl_init();
            $header = array();
            $header[] = "Authorization: Bearer " . $key;
            $header[] = "Accept: application/json";
            $header[] = "Content-Type: application/json";
            curl_setopt($curl2, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl2, CURLOPT_POST, true);
            curl_setopt($curl2, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl2, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl2, CURLOPT_URL, $url);
            curl_setopt($curl2, CURLOPT_RETURNTRANSFER, TRUE);
            $bfm = curl_exec($curl2);
            curl_close($curl2);

            $res = ['key'=> $key, 'res'=> $bfm];
	        return $res;
        }
        else{
            return array();
        }
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
