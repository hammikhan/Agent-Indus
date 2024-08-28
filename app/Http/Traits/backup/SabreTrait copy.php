<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait SabreTrait {
    public static function sabre_auth2(){
        $apiUrl = 'https://api.havail.sabre.com';
        $userId = '9999';
        $group = 'LK6D';
        $domain = 'AA';
        $PASSWORD= 'asf84038';

        // $clientSecret = 'sprdra2';
        // $formatVersion = 'V1';
        
        $token_url = $apiUrl . '/v2/auth/token';

        $clientId = base64_encode(base64_encode("V1:" . $userId . ":" . $group . ":" . $domain) . ':' . base64_encode($PASSWORD));

        $ch = curl_init($token_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $clientId,
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
            "grant_type: client_credentials"
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }
        curl_close($ch);
        // Storage::put('Sabre/apiToken.json', $response);
        return $response;
    }
    public static function search($requestData){
        // return $requestData;
        $requestJson = '{
            "OTA_AirLowFareSearchRQ": {
                "DirectFlightsOnly":false,
                "Version": "6.1.0",
                "POS": {
                    "Source": [
                      {
                        "PseudoCityCode": "LK6D",
                        "RequestorID": {
                          "Type": "1",
                          "ID": "1"
                        }
                      }
                    ]
                },
                "OriginDestinationInformation": [
                    {
                      "RPH": "1",
                      "DepartureDateTime": "'.$requestData['departureDate'].'T00:00:00",
                      "OriginLocation": {
                        "LocationCode": "'.$requestData['origin'].'"
                      },
                      "DestinationLocation": {
                        "LocationCode": "'.$requestData['destination'].'"
                      }
                    }
                ],
                "TravelPreferences": {
                    "ValidInterlineTicket": true,
                    "FlightTypePref": {
                        "MaxConnections": "0"
                    }
                },

                "TravelerInfoSummary": {
                    "SeatsRequested": [
                        1
                    ],
                    "AirTravelerAvail": [
                        {
                            "PassengerTypeQuantity": [
                                {
                                    "Code": "ADT",
                                    "Quantity": '.$requestData['adults'].'
                                }
                            ]
                        }
                    ]
                },
                "TPA_Extensions": {
                    "IntelliSellTransaction": {
                        "RequestType": {
                            "Name": "50ITINS"
                        }
                    }
                }
            }
        }';
        // return $requestJson;
        $url = 'https://api.havail.sabre.com/v4.3.0/shop/flights?mode=live';
        $type = 'POST';

        $authResp = self::sabre_auth2();
        $access_token = json_decode($authResp,true);
        $key = $access_token['access_token'];

        $apiToken = '';
        
        $res = self::curl_action($type,$url,$requestJson,$key,$apiToken);
        // Storage::put('Sabre/flightSearchResponse2.json', $res['res']);

        // $finalResult = json_decode($res['res'],true);
        // return $finalResult;
        // $errorMessage = json_decode($finalResult['message'],true);
        // $res = Storage::get('Sabre/flightSearchResponse1.json');

        return self::oneWayResponse($res['res'],$key);
    }
    public static function curl_action($type,$url,$data,$key = null , $apiToken = null)
    {
        // return $key;
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
            $response = curl_exec($curl2);
            curl_close($curl2);
            if (curl_errno($curl2)) {
                echo 'cURL Error: ' . curl_error($curl2);
            }
            
            $res = ['key'=> $key, 'res'=> $response];
            return $res;
        }
        else{
            return array();
        }
    }

    public static function oneWayResponse($res,$key){
        $bearer_key = $key;
        $res = json_decode($res);
        $finalData = array();
        $i = 0;
        foreach ($res->OTA_AirLowFareSearchRS->PricedItineraries->PricedItinerary as $ait) {
            $finalData[$i]['api'] = "Sabre";
            $ait->bearerKey = $bearer_key;
            $finalData[$i]['MarketingAirline']['FareRules'] = "NA";

            // $apiOffer = new ApiOfferModel();
            // $apiOffer->api = "Sabre";
            // $apiOffer->data = json_encode($ait);
            // // return $apiOffer->data;
            // $apiOffer->timestamp = time();
            // $apiOffer->query = json_encode($request->except('apiToken', 'apiKey', 'apiSecret'));
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
                            $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Cabin'] = 'Economy (' . $aip->FareInfos->FareInfo[$f]->TPA_Extensions->Cabin->Cabin . ')';
                        } else if (isset($aip->Tickets)) {
                            $finalData[$i]['LowFareSearch'][$o]['Segments'][$f]['Cabin'] = 'Economy (' . $aip->Tickets->Ticket[$o]->AirItineraryPricingInfo->FareInfos->FareInfo[0]->TPA_Extensions->Cabin->Cabin . ')';
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
                    $cnn = @$baggage['CNN'];
                    $inf = @$baggage['INF'];
                    foreach($finalData[$i]['LowFareSearch'] as $key=>$lowfares){
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
            
            $finalData[$i]['api_offer_id'] = $i;

            $i++;
        }
        $finalResult = ['status'=> '200' , 'msg' => $finalData];
        return $finalResult;
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
