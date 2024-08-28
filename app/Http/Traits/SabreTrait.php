<?php

namespace App\Http\Traits;

use App\Models\AirlineDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ApiOffer;
use App\Models\PricingEngineTravelAgent;
use App\Models\Provider;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;

trait SabreTrait
{
    public static $soap_link = 'https://webservices.platform.sabre.com';
    public static function sabre_auth()
    {
        $token_url = env('S_URL') . '/v2/auth/token';

        $clientId = base64_encode(base64_encode("V1:" . env('S_USERID') . ":" . env('S_GROUP') . ":" . env('S_DOMAIN')) . ':' . base64_encode(env('S_PASSWORD')));

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
        Storage::put('Sabre/apiToken.json', $response);
        return $response;
    }
    public static function lowFareSearch($requestData)
    {
        $passengers = [
            [
                "Code" => "ADT",
                "Quantity" => $requestData['adults']
            ]
        ];
        
        if ($requestData['children'] > 0) {
            $passengers[] = [
                "Code" => "CNN",
                "Quantity" => $requestData['children']
            ];
        }
        
        if ($requestData['infants'] > 0) {
            $passengers[] = [
                "Code" => "INF",
                "Quantity" => $requestData['infants']
            ];
        }
        
        $requestJson = [
            "OTA_AirLowFareSearchRQ" => [
                "DirectFlightsOnly" => false,
                "Version" => "4",
                "POS" => [
                    "Source" => [
                        [
                            "PseudoCityCode" => env('S_GROUP'),
                            "RequestorID" => [
                                "Type" => "1",
                                "ID" => "1"
                            ]
                        ]
                    ]
                ],
                "TravelPreferences" =>[
                    "CabinPref" => [
                        [
                            "Cabin" => $requestData['ticket_class'],
                           "PreferLevel" => "Preferred"
                        ]
                    ],
                    "TPA_Extensions" =>  [
                        "XOFares" => [
                            "Value" => true
                        ],
                        "JumpCabinLogic" => [
                            "Disabled" => true
                        ],
                        "KeepSameCabin" => [
                            "Enabled" => true
                        ]
                    ]
                ],
                "OriginDestinationInformation" => [
                    [
                        "RPH" => "1",
                        "DepartureDateTime" => $requestData['departureDate'] . 'T00:00:00',
                        "OriginLocation" => [
                            "LocationCode" => $requestData['origin']
                        ],
                        "DestinationLocation" => [
                            "LocationCode" => $requestData['destination']
                        ],
                        // "TPA_Extensions" => [
                        //     // "CabinPref" => [
                        //     //     "Cabin" => "Y",
                        //     //     // "Cabin" => "PremiumEconomy",
                        //     //     // "Cabin" => "Business",
                        //     //     "PreferLevel" => "Preferred"
                        //     // ]
                        //     "Flight" => [

                        //     ]
                        // ]
                    ]
                ]
            ]
        ];
        
        if ($requestData['tripType'] == "return") {
            $requestJson["OTA_AirLowFareSearchRQ"]["OriginDestinationInformation"][] = [
                "RPH" => "2",
                "DepartureDateTime" => $requestData['returnDate'] . 'T00:00:00',
                "OriginLocation" => [
                    "LocationCode" => $requestData['destination']
                ],
                "DestinationLocation" => [
                    "LocationCode" => $requestData['origin']
                ]
            ];
        }
        if ($requestData['tripType'] == "multi") {
            $requestJson["OTA_AirLowFareSearchRQ"]["OriginDestinationInformation"][] = [
                "RPH" => "2",
                "DepartureDateTime" => $requestData['departureDate2'] . 'T00:00:00',
                "OriginLocation" => [
                    "LocationCode" => $requestData['origin2']
                ],
                "DestinationLocation" => [
                    "LocationCode" => $requestData['destination2']
                ]
            ];
        }
        
        $requestJson["OTA_AirLowFareSearchRQ"]["TravelerInfoSummary"] = [
            // "SeatsRequested" => [1],
            "AirTravelerAvail" => [
                [
                    "PassengerTypeQuantity" => $passengers
                ]
            ],
            "PriceRequestInformation" => [
                "CurrencyCode" => "PKR",
                "TPA_Extensions" => [
                    "BrandedFareIndicators" => [
                        "MultipleBrandedFares" => true,
                        "ReturnBrandAncillaries" => true
                    ]
                ]
            ]
        ];
        
        $requestJson["OTA_AirLowFareSearchRQ"]["TPA_Extensions"] = [
            "IntelliSellTransaction" => [
                "RequestType" => [
                    "Name" => "50ITINS"
                ]
            ]
        ];
        $url = 'https://api.havail.sabre.com/v4.3.0/shop/flights?mode=live';
        $type = 'POST';

        // =====================Api Call LowfareSearch===================\\
        $authResp = self::sabre_auth();
        $access_token = json_decode($authResp, true);
        $key = @$access_token['access_token'];
        $apiToken = '';
        Storage::put('Sabre/flightSearchRequest.json', json_encode($requestJson, JSON_PRETTY_PRINT));
        $res = self::curl_action($type,$url,json_encode($requestJson),$key,$apiToken);
        if ($requestData['tripType'] == "return") {
            Storage::put('Sabre/flightSearchReturnResponse.json', json_encode($res, JSON_PRETTY_PRINT));
        }else{
            Storage::put('Sabre/flightSearchResponse.json', json_encode($res, JSON_PRETTY_PRINT));
        }
        // ===========Old Response from storage=============\\
        // if ($requestData['tripType'] == "return") {
        //     $key = '';
        //     $res = Storage::get('Sabre/flightSearchReturnResponse.json');
        //     $res = json_decode($res,true);
        // } else {
        //     $key = '';
        //     $res = Storage::get('Sabre/flightSearchResponse.json');
        //     $res = json_decode($res,true);
        // }
        // // ===========End Old Response from storage=============\\
        
        $apiResponse2 = $res;
        

        if (@$apiResponse2['status']) {
            if ($apiResponse2['status'] == 'Unknown') {
                return ['status' => '400', 'msg' => $apiResponse2['message']];
            }
            if ($apiResponse2['status'] == 'NotProcessed') {
                return ['status' => '400', 'msg' => $apiResponse2['message']];
            }
            if ($apiResponse2['status'] == 'Complete') {
                $message = json_decode($apiResponse2['message'],true);
                if($message['OTA_AirLowFareSearchRS']['PricedItinCount'] == 0){
                    $errors = $message['OTA_AirLowFareSearchRS']['Errors']; 
                    $exists = array_reduce($errors['Error'], function($carry, $error) {
                        return $carry || ($error['Type'] === 'SCHEDULES' && $error['ShortText'] === 'NO FLIGHT SCHEDULES FOR QUALIFIERS USED');
                    }, false);
                    if($exists){
                        return ['status' => '400', 'msg' => "No Flights available on this date, Please try another one.."];
                    }else{
                        return ['status' => '400', 'msg' => $apiResponse2['message']];
                    }
                }
                return ['status' => '400', 'msg' => $apiResponse2['message']];
            }
        }
        $parserResponse = self::oneWayResponse(json_encode($apiResponse2), $key, $requestData);
        // return $parserResponse;
        return $finalResult = ['status' => '200', 'msg' => $parserResponse]; 
    }
    public static function createPNR($requestData)
    {
        $api_offer = ApiOffer::where('ref_key',$requestData['itn_ref_key'])->first();
        $fares = $api_offer->data;
        $finaldata = $api_offer->finaldata;
        
        if(@$requestData['brand_ref_key']){
            $flightBrands = [];
            foreach ($finaldata['Flights'] as $flight) {
                if (isset($flight['Fares']) && is_array($flight['Fares'])) {
                    $filteredFares = array_filter($flight['Fares'], function ($fare) use ($requestData) {
                        return in_array($fare['RefID'], $requestData['brand_ref_key']);
                    });

                    if (!empty($filteredFares)) {
                        foreach ($filteredFares as $filteredFare) {
                            $flightBrands[] = $filteredFare;
                        }
                    }
                }
            }
            
            foreach($flightBrands as $brandKey => $brand){

                $CommandPricing[$brandKey]['FareBasis']= $brand['FareBases'];
            }
        }

        $email = $requestData['customer_email'];
        $phone = $requestData['customer_phone'];
        $passengers = $requestData['passengers'];
        $reqEmail = [
            [
                "Address" => $email,
                "NameNumber" => "1.1"
            ]
        ];
        $reqContactNumber = array();
        $reqEmail = array();
        $reqPassengerName = array();
        $reqAdvancePassenger = array();
        $reqSecureFlight = array();
        $reqService = array();
        $reqPQPassengerType = array();
        $reqFlightSegment = array();
        $countADT = 0;
        $countCNN = 0;
        $countINF = 0;
        $nameNo = 1;

        foreach ($passengers as $key => $value) {
            if ($value['passenger_type'] != 'INF') {
                $passContact = [
                    "Phone" => $phone,
                    "PhoneUseType" => "M",
                    "NameNumber" =>  $nameNo . ".1"
                ];
                $passEmail = [
                    "Address" => $email,
                    "NameNumber" =>  $nameNo . ".1"
                ];

                array_push($reqEmail, $passEmail);
                array_push($reqContactNumber, $passContact);
            }

            if ($value['passenger_type'] == 'ADT') {
                $countADT++;
            }
            if ($value['passenger_type'] == 'CNN') {
                $countCNN += 1;
                // $reqService[] = [
                //     "PersonName" => [
                //         "NameNumber" => $nameNo . ".1"
                //     ],
                //     "Text" => $value['dob'],
                //     "SSR_Code" => "CHLD"
                // ];
            }
            if ($value['passenger_type'] == 'INF') {
                $countINF += 1;
                // $reqService[] = [
                //     "PersonName" => [
                //         "NameNumber" => $nameNo . ".1"
                //     ],
                //     "Text" => $value['sur_name'] . '-' . $value['name'] . '-' . $value['dob'],
                //     "SSR_Code" => "INFT"
                // ];
            }

            $passengerName["Infant"] = $value['passenger_type'] == 'INF' ? true : false;
            $passengerName["PassengerType"] = $value['passenger_type'];
            $passengerName["NameNumber"] = $nameNo . ".1";
            $passengerName["GivenName"] = $value['name'] . ' ' . $value['passenger_title'];
            $passengerName["Surname"] = $value['sur_name'];
            // $passengerName["NameReference"] = $value['passenger_type'].$value['dob'];
            $passengerName["NameReference"] = $value['passenger_type'] != 'ADT' ? self::getNameRef($value['passenger_type'], $value['dob']) : "";

            array_push($reqPassengerName, $passengerName);
            $NameNumber = $value['passenger_type'] != 'INF' ?  $nameNo . ".1" : $countINF . ".1";
            $ssrNameNumber = $NameNumber;
            $secureFlight = [
                "PersonName" => [
                    // "GivenName" =>  $value['name'] . ' ' . $value['passenger_title'],
                    "GivenName" =>  $value['name'] . ' ' . $value['passenger_title'],
                    "Surname" => $value['sur_name'],
                    "DateOfBirth" => $value['dob'],
                    "Gender" => $value['passenger_type'] == 'INF' ? $value['passenger_gender'] . 'I' : $value['passenger_gender'],
                    "NameNumber" => $NameNumber
                ],
                "SegmentNumber" => "A"
            ];

            
            $advancePassenger = [
                "Document" => [
                    "IssueCountry" => $value['nationality'],
                    "NationalityCountry" => $value['nationality'],
                    "ExpirationDate" => $value['document_expiry_date'],
                    "Number" => $value['document_number'],
                    "Type" => $value['document_type']
                ],
                "PersonName" => [
                    "GivenName" =>  $value['name'] . ' ' . $value['passenger_title'],
                    "MiddleName" => '',
                    "Surname" => $value['sur_name'],
                    "DateOfBirth" => $value['dob'],
                    "DocumentHolder" => true,
                    "Gender" => $value['passenger_type'] == 'INF' ? $value['passenger_gender'] . 'I' : $value['passenger_gender'],
                    "NameNumber" => $NameNumber
                ],
                "VendorPrefs" => [
                    "Airline" => [
                        "Hosted" => false
                    ]
                ],
                "SegmentNumber" => "A"
            ];
            if($value['passenger_type'] != 'INF'){
                $ssrEmail = str_replace('@', '//', $email);

                $service = [
                    "PersonName" => [
                        "NameNumber" => $ssrNameNumber
                    ],
                    "Text" => $phone,
                    "VendorPrefs" => [
                        "Airline" => [
                            "Hosted" => false
                        ]
                    ],
                    "SegmentNumber" => "A",
                    "SSR_Code" => "CTCM"
                ];
                $service2 = [
                    "PersonName" => [
                        "NameNumber" => $ssrNameNumber
                    ],
                    "Text" => $ssrEmail,
                    "VendorPrefs" => [
                        "Airline" => [
                            "Hosted" => false
                        ]
                    ],
                    "SegmentNumber" => "A",
                    "SSR_Code" => "CTCE"
                ];
                array_push($reqService, $service);
                array_push($reqService, $service2);
            }
            if($value['passenger_type'] == 'INF'){
                $dateDOB = date('dMy', strtotime($value['dob']));
                $service3 = [
                    "PersonName" => [
                        "NameNumber" => $NameNumber
                    ],
                    "Text" => $value['sur_name'].'/'.$value['name'].' '.$value['passenger_title'].'/'.$dateDOB,
                    "VendorPrefs" => [
                        "Airline" => [
                            "Hosted" => false
                        ]
                    ],
                    "SegmentNumber" => "A",
                    "SSR_Code" => "INFT"
                ];
                array_push($reqService, $service3);
            }
            if($value['passenger_type'] == 'CNN'){
                $dateDOB = date('dMy', strtotime($value['dob']));
                $service4 = [
                    "PersonName" => [
                        "NameNumber" => $NameNumber
                    ],
                    "Text" => $dateDOB,
                    "VendorPrefs" => [
                        "Airline" => [
                            "Hosted" => false
                        ]
                    ],
                    "SegmentNumber" => "A",
                    "SSR_Code" => "CHLD"
                ];
                array_push($reqService, $service4);
            }
            array_push($reqAdvancePassenger, $advancePassenger);
            array_push($reqSecureFlight, $secureFlight);
            $value['passenger_type'] != 'INF' ? $nameNo++ : '';
        }

        if ($countADT >= 1) {
            $PassengerType = [
                "Code" => "ADT",
                "Quantity" => (string) $countADT
            ];
            array_push($reqPQPassengerType, $PassengerType);
        }
        if ($countCNN >= 1) {
            $PassengerType = [
                "Code" => "CNN",
                "Quantity" => (string) $countCNN
            ];
            array_push($reqPQPassengerType, $PassengerType);
        }
        if ($countINF >= 1) {
            $PassengerType = [
                "Code" => "INF",
                "Quantity" => (string) $countINF
            ];
            array_push($reqPQPassengerType, $PassengerType);
        }

        if ($countINF > 0) {
            if ($countINF > $countADT) {
                $NumberInParty = $countCNN + $countADT + ($countINF - $countADT);
            } else {
                $NumberInParty = $countADT + $countCNN;
            }
        } else {
            $NumberInParty = $countADT + $countCNN;
        }
        ////////////////////////////////////////////////////////////////////////
        $SegmentSelect = [];
        $segmentSelectKey = 1;
        $RphItenKey = 1;
        $startNumber = null;

        foreach ($fares['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption'] as $itnKey => $leg) {
            $totalSegments = count($leg['FlightSegment']);
            $currentSegment = 1;

            foreach ($leg['FlightSegment'] as $segmentKey => $segment) {
                //////////////////////OriginDestination/////////////////
                if(@$CommandPricing){
                    $ResBookDesigCode = $CommandPricing[$itnKey]["FareBasis"][$segmentKey]['BookingCode'];
                }else{
                    $ResBookDesigCode = $segment['ResBookDesigCode'];
                }
                $itinSegment = [
                    "DepartureDateTime" => $segment["DepartureDateTime"],
                    "ArrivalDateTime" => $segment["ArrivalDateTime"],
                    "FlightNumber" => $segment["FlightNumber"],
                    "NumberInParty" => "$NumberInParty", // You can adjust this value as needed
                    "ResBookDesigCode" => $ResBookDesigCode,
                    "Status" => "NN", // You can adjust this value as needed
                    "DestinationLocation" => [
                        "LocationCode" => $segment["ArrivalAirport"]["LocationCode"]
                    ],
                    "MarketingAirline" => [
                        "Code" => $segment["MarketingAirline"]["Code"],
                        "FlightNumber" => $segment["FlightNumber"]
                    ],
                    "MarriageGrp" => $segment["MarriageGrp"],
                    "OriginLocation" => [
                        "LocationCode" => $segment["DepartureAirport"]["LocationCode"]
                    ]
                ];

                $reqFlightSegment[] = $itinSegment;

                //////////////////////Segment Select/////////////////////
                if ($currentSegment === 1) {
                    // First segment of the leg
                    $startNumber = $segmentSelectKey;
                }

                if ($currentSegment === $totalSegments) {
                    // Last segment of the leg
                    $SegmentSelect[] = [
                        'Number' => (string)$startNumber,
                        'EndNumber' => (string)$segmentSelectKey,
                        'RPH' => (string)$RphItenKey,
                    ];
                    $startNumber = null; // Reset start number for next leg
                }

                $segmentSelectKey++;
                $currentSegment++;
            }

            $RphItenKey++;
        }
        ////////////////////////////////////////////////////////////////////////
        $agent = auth('admin')->user();
        if($agent->agency){
            $AgencyName = $agent->agency->name;
        }else{
            $AgencyName = "Indus User";
        }
        $ReceivedFrom = $agent->first_name.' '.$agent->last_name;
        
        $PricingQualifiers['PassengerType'] = $reqPQPassengerType;
        if(@$requestData['brand_ref_key']){
            $rph = 1;
            $brandCode = [];
            foreach ($flightBrands as $brand) {
                $brandCode[] = [
                    'RPH' => $rph,
                    'content' => $brand['BrandID']
                ];
                $rph++;
            }
            $PricingQualifiers['Brand'] = $brandCode;
            $PricingQualifiers['ItineraryOptions']['SegmentSelect'] = $SegmentSelect;
        }
        $requestForCurl = [
            "CreatePassengerNameRecordRQ" => [
                "version" => "2.5.0",
                "targetCity" => env('S_GROUP'),
                "haltOnAirPriceError" => true,
                "TravelItineraryAddInfo" => [
                    "AgencyInfo" => [
                        "Ticketing" => [
                            "TicketType" => "7TAW",
                            "ShortText" => "Indus"
                        ]
                    ],
                    "CustomerInfo" => [
                        "ContactNumbers" => [
                            "ContactNumber" => $reqContactNumber
                        ],
                        "PersonName" => $reqPassengerName,
                        "Email" => $reqEmail
                    ]
                ],
                "AirBook" => [
                    "RetryRebook" => [
                        "Option" => false
                    ],
                    "HaltOnStatus" => [
                        [
                            "Code" => "HL"
                        ],
                        [
                            "Code" => "KK"
                        ],
                        [
                            "Code" => "LL"
                        ],
                        [
                            "Code" => "NN"
                        ],
                        [
                            "Code" => "NO"
                        ],
                        [
                            "Code" => "UC"
                        ],
                        [
                            "Code" => "US"
                        ]
                    ],
                    "OriginDestinationInformation" => [
                        "FlightSegment" => $reqFlightSegment
                    ],
                    "RedisplayReservation" => [
                        "NumAttempts" => 10,
                        "WaitInterval" => 1500
                    ]
                ],
                "AirPrice" => [
                    [
                        "PriceRequestInformation" => [
                            "Retain" => true,
                            "OptionalQualifiers" => [
                                "FOP_Qualifiers" => [
                                    "BasicFOP" => [
                                        "Type" => $AgencyName
                                    ]
                                ],
                                "PricingQualifiers" => $PricingQualifiers
                            ],
                        ]
                    ]
                ],
                "SpecialReqDetails" => [
                    "SpecialService" => [
                        "SpecialServiceInfo" => [
                            "AdvancePassenger" => $reqAdvancePassenger,
                            "SecureFlight" => $reqSecureFlight,
                            "Service" => $reqService
                        ]
                    ]
                ],
                "PostProcessing" => [
                    "EndTransaction" => [
                        "Source" => [
                            "ReceivedFrom" => $ReceivedFrom
                        ]
                    ],
                    "PostBookingHKValidation" => [
                        "waitInterval" => 200,
                        "numAttempts" => 4
                    ],
                    "WaitForAirlineRecLoc" => [
                        "waitInterval" => 200,
                        "numAttempts" => 4
                    ],
                    "RedisplayReservation" => [
                        "waitInterval" => 1000
                    ]
                ]
            ]
        ];
        // Storage::put('Sabre/PnrRequest.json', json_encode($requestForCurl, JSON_PRETTY_PRINT));
        // dd($requestForCurl);
        /***********************************************\
         *************Create PNR API call************** |
        \***********************************************/
        Storage::put('Sabre/PNR/'.date('Y-m-d-H-i-s').'PnrRequest.json', json_encode($requestForCurl, JSON_PRETTY_PRINT));
        $requestJson = json_encode($requestForCurl, true);
        $url = 'https://api.platform.sabre.com/v2.5.0/passenger/records?mode=create';
        $type = 'POST';
        $authResp = self::sabre_auth();
        $access_token = json_decode($authResp, true);
        $key = @$access_token['access_token'];
        $apiToken = '';
        $res = self::curl_action1($type,$url,$requestJson,$key,$apiToken);
        $response = json_decode($res, true);
        Storage::put('Sabre/PNR/'.date('Y-m-d-H-i-s-u').'PnrResponse.json', json_encode($response, JSON_PRETTY_PRINT));
        // ===========Old Response from storage=============\\
            // $res = Storage::get('Sabre/PNR/2024-08-22-17-07-51-000000PnrResponse.json');
            // $res = Storage::get('Sabre/backup/Errors/2024-06-03-21-20-10-000000PnrResponse.json');
            // $response = json_decode($res, true);
        // =========== End Old Response from storage=============\\
        
        if (array_key_exists('CreatePassengerNameRecordRS', $response)) {
            $CreatePassengerNameRecordRS = $response['CreatePassengerNameRecordRS'];
            
            if($CreatePassengerNameRecordRS['ApplicationResults']['status'] == "Complete"){
                $TotalAmount = 0;
                if($CreatePassengerNameRecordRS['AirPrice']){
                    $PricedItinerary = $CreatePassengerNameRecordRS['AirPrice'][0]['PriceQuote']['PricedItinerary'];
                    $TotalAmount = $PricedItinerary['TotalAmount'];
                }
                $ApplicationResults = $CreatePassengerNameRecordRS['ApplicationResults'];
                if ($ApplicationResults['status'] === 'Incomplete' || $ApplicationResults['status'] === 'NotProcessed') {
                    return ['status' => '400', 'response' => $res];
                }
                $lastTicketingDate = '';
    
                array_walk_recursive($CreatePassengerNameRecordRS['AirPrice'], function($value, $key) use (&$lastTicketingDate) {
                    if ($key === 'LastTicketingDate') {
                        $lastTicketingDate = $value;
                    }
                });
                $pnr = '';
                $airlinePNR = '';
                if (array_key_exists('ItineraryRef', $CreatePassengerNameRecordRS)) {
                    $pnr = $CreatePassengerNameRecordRS['ItineraryRef']['ID'];
                }
                if (array_key_exists('TravelItineraryRead', $CreatePassengerNameRecordRS)) {
                    $FlightSegment = @$CreatePassengerNameRecordRS['TravelItineraryRead']['TravelItinerary']['ItineraryInfo']['ReservationItems']['Item']['0']['FlightSegment'];
                    $airlinePNR = @$FlightSegment[0]['SupplierRef']['ID'];
                }
                return ['status' => '200', 'pnr' => $pnr, 'TotalAmount' => $TotalAmount, 'airlinePNR' => $airlinePNR, 'response' => $response, 'last_ticketing_date' => @$lastTicketingDate];
            }else{
                if (array_key_exists('Warning', $CreatePassengerNameRecordRS['ApplicationResults'])){
                    $Error = $CreatePassengerNameRecordRS['ApplicationResults']['Error'];
                    $messages = array_map(function($result) {
                        return array_column($result['SystemSpecificResults'], 'Message');
                    }, $Error);
                    
                    $Warning = $CreatePassengerNameRecordRS['ApplicationResults']['Warning'];
                    $messages = array_map(function($result) {
                        return array_column($result['SystemSpecificResults'], 'Message');
                    }, $Warning);
                
                    // Flatten the messages array
                    $flattenedMessages = array_merge(...$messages);
                
                    // Check if any message contains the specific error
                    $noFlightFound = array_filter($flattenedMessages, function($messageGroup) {
                        foreach ($messageGroup as $msg) {
                            if (isset($msg['content']) && strpos($msg['content'], 'EnhancedAirBookRQ: *NO FARES/RBD/CARRIER') !== false) {
                                return true;
                            }
                        }
                        return false;
                    });
                    if(@$Error[0]['SystemSpecificResults'][0]['Message']){
                        return ['status' => '400', 'response' => json_encode($Error[0]['SystemSpecificResults'][0]['Message'])];
                    }
                    if ($noFlightFound) {
                        return ['status' => '400', 'response' => 'The fare Selected is no longer available..'];
                    } else {
                        return ['status' => '400', 'response' => json_encode($Error)];
                    }
                }else{
                    return ['status' => '400', 'response' => json_encode($CreatePassengerNameRecordRS)];
                }
            }

        } elseif (array_key_exists('errorCode', $response)) {
            return ['status' => '400', 'response' => json_encode($res)];
        }
    }
    public static function issueTicket($order){
        $customer_data = json_decode($order['customer_data'],true);
        $fetch_response = json_decode($order['fetch_response'],true);

        // $agent = auth('admin')->user();
        if(@$order->agency){
            $AgencyName = $order->agency->name;
        }else{
            $AgencyName = "Indus User";
        }

        $ReceivedFrom = $order->admin->first_name.' '.$order->admin->last_name;
        $PriceQuote = [];
        $fares = $fetch_response['fares'];

        $maxRecordId = array_reduce($fares, function ($maxId, $currentEntry) {
            if ($maxId === null || $currentEntry['recordId'] > $maxId) {
                return $currentEntry['recordId'];
            }
            return $maxId;
        }, null);
        
        if($maxRecordId > 1){
            $numberEndNumber = [
                "Number" => 1,
                "EndNumber" => (int) $maxRecordId
            ];
        }else{
            $numberEndNumber = [
                "Number" => 1,
            ];
        }
        $data = [
            "Record" => [
                $numberEndNumber
            ]
        ];

        array_push($PriceQuote, $data);

        
        $ticketing =  [
            [
                "PricingQualifiers" => [
                    "PriceQuote" => $PriceQuote
                ],
                "FOP_Qualifiers" => [
                    "BasicFOP" => [
                        "Type" => $AgencyName
                    ]
                ],
            ]
        ];

        /***************Airline commission******************/
        
        $final_data = json_decode($order['final_data'],true);
        $Carrier = $final_data['MarketingAirline']['Airline'];

        $airline_commission = AirlineDiscount::where('provider','Sabre')->where('airline',$Carrier)->first();
        if(@$airline_commission){
            if(@$airline_commission->departure_codes){
                $departure_codes = explode(",", $airline_commission->departure_codes);
                $departure = $final_data['Flights'][0]['Segments'][0]['Departure']['LocationCode'];

                if (in_array($departure, $departure_codes)) {
                    $ticketing[0]['MiscQualifiers'] = [
                        "Commission" => [
                            "Percent" => (int)$airline_commission->discount
                        ]
                    ];
                } else {
                    $ticketing[0]['MiscQualifiers'] = [
                        "Commission" => [
                            "Percent" => 0
                        ]
                    ];
                }
            }else{
                $ticketing[0]['MiscQualifiers'] = [
                    "Commission" => [
                        "Percent" => (int)$airline_commission->discount
                    ]
                ];
            }
        }else {
            $ticketing[0]['MiscQualifiers'] = [
                "Commission" => [
                    "Percent" => 0
                ]
            ];
        }

        /************************************************** */

        $requestForCurl = [
            "AirTicketRQ" => [
                "DesignatePrinter" => [
                    "Printers" => [
                        "InvoiceItinerary" => [
                            "LNIATA" => env('S_PRINTER2')
                        ],
                        "Hardcopy" => [
                            "LNIATA" => env('S_PRINTER2')
                        ],
                        "Ticket" => [
                            "CountryCode" => "PK"
                        ]
                    ]
                ],
                "Itinerary" => [
                    "ID" => $order['pnrCode']
                ],
                "Ticketing" => $ticketing,
                "PostProcessing" => [
                    "acceptPriceChanges" => true,
                    "actionOnPQExpired" => 'R',
                    "EndTransaction" => [
                        "Source" => [
                            "ReceivedFrom" => $ReceivedFrom
                        ],
                    ]
                ],
            ]
        ];
        
        if (env('S_GROUP')) {
            $requestForCurl["AirTicketRQ"]["targetCity"] = env('S_GROUP');
        }
        // dd($requestForCurl);
        /***********************************************\
         *************ISSUE TICKET API call************** |
        \***********************************************/
        Storage::put('Sabre/Ticket/'.date('Y-m-d-H-i-s').'TicketRequestPretty.json', json_encode($requestForCurl, JSON_PRETTY_PRINT));
        $requestJson = json_encode($requestForCurl, true);
        $url = env('S_URL') .'/v1.2.1/air/ticket';
        $type = 'POST';
        $authResp = self::sabre_auth();
        $access_token = json_decode($authResp, true);
        $key = @$access_token['access_token'];
        $apiToken = '';
        $res = self::curl_action1($type,$url,$requestJson,$key,$apiToken);
        $res2 = json_decode($res,true);
        Storage::put('Sabre/Ticket/'.date('Y-m-d-H-i-s-u').'TicketResponse.json', json_encode($res2, JSON_PRETTY_PRINT));
        /*************************OLD Response TICKET*********************/
        // $res = Storage::get('Sabre/backup/Ticket-11-06-2024/2024-02-28-14-58-09-000000TicketResponse2.json');
        // $res = Storage::get('Sabre/Errors/2024-02-29-11-52-05-000000PnrResponse2.json');
        // =========== End Old Response from storage=============\\

        $response = json_decode($res,true);
        // dd($response);
        if (array_key_exists('AirTicketRS', $response)) {
            $ticketData = array();

            $AirTicketRS = $response['AirTicketRS'];
            if (array_key_exists('ApplicationResults', $AirTicketRS) && $AirTicketRS['ApplicationResults']['status'] === 'Complete') {
                $AirTicketRS = $AirTicketRS['Summary'];
                foreach($AirTicketRS as $key => $ticketRS){
                    $ticketData[$key]['name'] = $ticketRS['FirstName'];
                    $ticketData[$key]['sur_name'] = $ticketRS['LastName'];
                    $ticketData[$key]['TicketNumber'] = $ticketRS['DocumentNumber'];
                }
                return ['status'=> '200' , 'msg' => json_encode($response) ,  'ticketData'=> $ticketData];
            }else{
                Log::info("***start issueTicket error***");
                Log::error($response);
                Log::info("***end issueTicket error***");
                return ['status' => '400', 'msg' => json_encode($response)];
            }
        }elseif (array_key_exists('errorCode', $response)) {
            Log::info("***start issueTicket error***");
            Log::error($response);
            Log::info("***end issueTicket erro***");
            return ['status' => '400', 'msg' => json_encode($response)];
        }

    }
    public static function fetchPNR($order){
        
        $url = env('S_URL') .'/v1/trip/orders/getBooking';
        $type = 'POST';
        $authResp = self::sabre_auth();
        $access_token = json_decode($authResp, true);
        $key = @$access_token['access_token'];
        $apiToken = '';
        $requestJson = json_encode(
            ['confirmationId' => $order['pnrCode'],
        ]);
        /***********************************************\
         **************Fetch PNR API call***************|
        \***********************************************/
            $res = self::curl_action1($type,$url,$requestJson,$key,$apiToken);
            $res2 = json_decode($res,true);
            Storage::put('Sabre/Fetch/'.$order['pnrCode'].'-fetchPNRResponse.json', json_encode($res2, JSON_PRETTY_PRINT));
        /*************************OLD Response FETCH*********************/
            // $res = Storage::get('Sabre/Fetch/FOZVUM-fetchPNRResponse.json');
        // =========== End Old Response from storage=============\\
        $response = json_decode($res,true);
        $final_data = json_decode($order['final_data'],true);

        // dd($final_data,$response);
        if (array_key_exists('bookingId', $response)) {
            $ticket = array();
            $airline = array();
            $services = array();
            $ticketStatus = '';

            if (array_key_exists('specialServices', $response)) {
                foreach($response['specialServices'] as $service){
                    if($service['code'] == 'ADTK' OR $service['code'] == 'OTHS'){
                        $services['specialServices'][] = [
                            "code" => $service['code'],
                            "message" => $service['message'],
                        ];
                        // $services['specialServices'][]['message'] = $service['message'];
                    }
                }
            }
            if (array_key_exists('flights', $response)) {
                $changeItinerarySegments = [];

                $flights = $response['flights'];
                foreach($flights as $key => $flight){
                    $airline[$key]['pnrStatus'] = $flight['flightStatusName'];
                    $airline[$key]['airlineCode'] = $flight['airlineCode'];
                    $airline[$key]['airlinePnr'] = $flight['confirmationId'];
                    $airline[$key]['departureDate'] = $flight['departureDate'];
                    $airline[$key]['departureTime'] = $flight['departureTime'];
                    
                    $changeItinerarySegments[] = [
                        'Duration' => @$flight['durationInMinutes'],
                        'airlinePnr' => $flight['confirmationId'],
                        'pnrStatus' => $flight['flightStatusName'],
                        'seatStatus' => @$flight['seats'][0]['statusName'],
                        'MarketingAirline' => [
                            'Code' => $flight['airlineCode'],
                            'FlightNumber' => $flight['flightNumber']
                        ],
                        'OperatingAirline' => [
                            'Code' => $flight['operatingAirlineCode'],
                            'FlightNumber' => $flight['operatingFlightNumber']
                        ],
                        'EquipType' => $flight['aircraftTypeCode'],
                        'Departure' => [
                            'LocationCode' => $flight['fromAirportCode'],
                            'Terminal' => $flight['departureTerminalName'] ?? null,
                            'DepartureDateTime' => "{$flight['departureDate']}T{$flight['departureTime']}"
                        ],
                        'Arrival' => [
                            'LocationCode' => $flight['toAirportCode'],
                            'Terminal' => $flight['arrivalTerminalName'] ?? null,
                            'ArrivalDateTime' => "{$flight['arrivalDate']}T{$flight['arrivalTime']}"
                        ],
                        'AvailableSeats' => 9, // Assuming a fixed number of available seats
                        'Cabin' => $flight['cabinTypeCode'],
                        'CabinClass' => $flight['cabinTypeName']
                    ];
                }
                $newFligts[0]['Segments'] = $changeItinerarySegments;
                
            }else{
                $airline[0]['pnrStatus'] = 'Cancelled';
            }
            if (array_key_exists('flightTickets', $response)) {
                $flightTickets = $response['flightTickets'];
                $ticketData = array();
                ///////////////////////////////////////////////////////////////////////////////////////
                foreach ($flightTickets as $tktKey => $flightTKT) {
                    if ($flightTKT['ticketStatusName'] == 'Issued') {
                        $ticketStatusName = 'Ticketed';
                    } else {
                        $ticketStatusName = $flightTKT['ticketStatusName'];
                    }
                    $ticketStatus = $ticketStatusName;
                
                    if (array_key_exists('travelers', $response)) {
                        $travelers = $response['travelers'];
                
                        foreach ($travelers as $travelerKey => $traveler) {
                            if ($traveler['nameAssociationId'] == $flightTKT['travelerIndex']) {
                                if (!isset($ticketData[$tktKey])) {
                                    $ticketData[$tktKey] = [
                                        'type' => $traveler['type'],
                                        'passengerCode' => $traveler['passengerCode'],
                                        'name' => $traveler['givenName'],
                                        'sur_name' => $traveler['surname'],
                                        'TicketNumber' => $flightTKT['number'],
                                        'TicketStatus' => $flightTKT['ticketStatusName'],
                                    ];
                                } else {
                                    $ticketData[$tktKey]['TicketNumber'] .= ', ' . $flightTKT['number'];
                                }
                            }
                        }
                    }
                }
                
            }elseif($order['status'] == 'Ticketed'){
                $ticketStatus = 'Cancelled';
            }else{
                $ticketStatus = 'Not Ticketed';
            }

            $getBookingResponse = [
                'status'=> '200',
                'ticket'=> $ticket,
                'ticketStatus'=> $ticketStatus,
                'ticketData'=> @$ticketData,
                'services' => $services,
                'airline' => $airline,
                'newFligts' => @$newFligts,
                'msg' => json_encode($response)
            ];
            // dd($getBookingResponse);
            return $getBookingResponse;
        }else{
            Log::info("***start FetchPNR error***");
            Log::error($response);
            Log::info("***end FetchPNR erro***");
            return ['status' => '400', 'msg' => json_encode($response)];
        }

    }
    public static function revalidatePNR($order){
        $pnrApiResponse = json_decode($order->apiResponse,true);

        $requestForCurl = [
            "UpdatePassengerNameRecordRQ" => [
                "version" => "1.1.0", 
                "targetCity" => "9H3K", 
                "haltOnAirPriceError" => false, 
                "Itinerary" => [
                    "id" => $order->pnrCode 
                ], 
                "SpecialReqDetails" => [
                    "AddRemark" => [
                        "RemarkInfo" => [
                            "Remark" => [
                                [
                                "Type" => "Historical", 
                                "Text" => "TEST REMARK" 
                                ] 
                            ] 
                        ] 
                    ] 
                ], 
                "PostProcessing" => [
                    "EndTransaction" => [
                        "Source" => [
                            "ReceivedFrom" => "Hamid Afridi" 
                        ] 
                    ] 
                ] 
            ] 
        ];
        return $requestForCurl;
        $url = env('S_URL') .'/v1.1.0/passenger/records?mode=update';
        $type = 'POST';
        $authResp = self::sabre_auth();
        $access_token = json_decode($authResp, true);
        $key = @$access_token['access_token'];
        $apiToken = '';

        /***********************************************\
         **************Fetch PNR API call***************|
        \***********************************************/
        Storage::put('Sabre/PNR-Update/'.date('Y-m-d-H-i-s').'updatePnrRequest.json', json_encode($requestForCurl, JSON_PRETTY_PRINT));
        $requestJson = json_encode($requestForCurl, true);

        $res = self::curl_action1($type,$url,$requestJson,$key,$apiToken);
        $response = json_decode($res, true);
        return $response;
        Storage::put('Sabre/PNR-Update/'.date('Y-m-d-H-i-s-u').'updatePnrResponse.json', json_encode($response, JSON_PRETTY_PRINT));
    }
    public static function cancelBookingRequest($order){
        $request = [
            "confirmationId" => $order['pnrCode'],
            "retrieveBooking" => true,
            "cancelAll" => true,
            "errorHandlingPolicy" => "ALLOW_PARTIAL_CANCEL",
            // "flightTicketOperation" => "VOID"
        ];

        $url = env('S_URL') .'/v1/trip/orders/cancelBooking';
        $type = 'POST';
        $authResp = self::sabre_auth();
        $access_token = json_decode($authResp, true);
        $key = @$access_token['access_token'];
        $apiToken = '';

        $requestJson = json_encode($request);
        /***********************************************\
         **************Fetch PNR API call***************|
        \***********************************************/
        $res = self::curl_action1($type,$url,$requestJson,$key,$apiToken);
        Storage::put('Sabre/Cancel/'.$order['pnrCode'].'-cancelPNRResponse.json', $res);

        /*************************OLD Response FETCH*********************/
        // $res = Storage::get('Sabre/Cancel/EMAKCC-cancelPNRResponse.json');
        // =========== End Old Response from storage=============\\
        $response = json_decode($res,true);
        if (array_key_exists('timestamp', $response) || array_key_exists('request', $response) || array_key_exists('booking', $response)){
            $airline[0]['pnrStatus'] = 'Cancelled';
            $ticket[0]['ticketStatus'] = 'Cancelled';
            return ['status'=> '200',  'ticket'=> $ticket, 'airline' => $airline, 'msg' => json_encode($response)];
        }else{
            Log::info("***start CancelPNR error***");
            Log::error($response);
            Log::info("***end CancelPNR erro***");
            return ['status' => '400', 'msg' => json_encode($response)];
        }
    }
    public static function voidBookingRequest($order){
        $ticketData = json_decode($order['tickets_data'],true);
        $ticketArray = $ticketData[0]['TicketNumber'];
        // $request = [
            //     "errorHandlingPolicy" => "HALT_ON_ERROR",
            //     "tickets" => [
            //         $ticketArray
            //     ],
            //     "targetPcc" => env('S_GROUP'),
            //     "designatePrinters" => [
            //         "Printers" => [
            //             "InvoiceItinerary" => [
            //                 "LNIATA" => env('S_PRINTER2')
            //             ],
            //             "Hardcopy" => [
            //                 "LNIATA" => env('S_PRINTER2')
            //             ],
            //             "Ticket" => [
            //                 "CountryCode" => "PK"
            //             ]
            //         ]
            //     ]
        // ];

        // $token = self::GetReservationRQ_Soap($order['pnrCode']);
        // $token1 = self::DesignatePrinterRQ($token);
        // $token2 = self::voidTktSoap($token1);
        // return self::voidTktSoap2($token2);
        $binaryToken = self::SessionCreateRQ_Soap();
        $ContextChangeRQ = self::ContextChangeRQ($binaryToken);
        $GetReservationRQ_Soap = self::GetReservationRQ_Soap($order['pnrCode'],$binaryToken);
        self::DesignatePrinterRQ($binaryToken);
        $voidTktSoapResp = self::voidTktSoap($binaryToken);
        self::EnhancedEndTransactionRQ_Soap($binaryToken);
        dd($voidTktSoapResp);
        /*************************************************** */
        $request = [
            "errorHandlingPolicy" => "HALT_ON_ERROR",
            "targetPcc" => env('S_GROUP'),
            "tickets" => [
                $ticketArray
            ],
            "DesignatePrinter" => [
                "Printers" => [
                    "InvoiceItinerary" => [
                        "LNIATA" => env('S_PRINTER2')
                    ],
                    "Hardcopy" => [
                        "LNIATA" => env('S_PRINTER2')
                    ],
                    "Ticket" => [
                        "CountryCode" => "PK"
                    ]
                ]
            ]
        ];
        /************************************************* */
        $url = env('S_URL') .'/v1/trip/orders/voidFlightTickets';
        $type = 'POST';
        $authResp = self::sabre_auth();
        $access_token = json_decode($authResp, true);
        $key = @$access_token['access_token'];
        $apiToken = '';

        $requestJson = json_encode($request);
        /***********************************************\
         **************Fetch PNR API call***************|
        \***********************************************/
        Storage::put('Sabre/Void/'.$order['pnrCode'].'-voidTicketRequest.json', json_encode($request, JSON_PRETTY_PRINT));
        $res = self::curl_action1($type,$url,$requestJson,$key,$apiToken);
        $response = json_decode($res,true);
        Storage::put('Sabre/Void/'.$order['pnrCode'].'-voidTicketResponse.json', json_encode($response, JSON_PRETTY_PRINT));

        /*************************OLD Response FETCH*********************/
        // $res = Storage::get('Sabre/Void/EMAKCC-voidTicketResponse.json');
        // =========== End Old Response from storage=============\\
        
        if (array_key_exists('timestamp', $response) || array_key_exists('request', $response) || array_key_exists('booking', $response)){
            $airline[0]['pnrStatus'] = 'Cancelled';
            $ticket[0]['ticketStatus'] = 'Cancelled';
            return ['status'=> '200',  'ticket'=> $ticket, 'airline' => $airline, 'msg' => json_encode($response)];
        }else{
            Log::info("***start voidTicket error***");
            Log::error($response);
            Log::info("***end voidTicket erro***");
            return ['status' => '400', 'msg' => json_encode($response)];
        }
    }
    
    /******************************************************\
     * ***************Other functions**********************
    \******************************************************/
    public static function oneWayResponse($res, $key, $request)
    {   
        $provider = Provider::where('identifier','sabre')->first();
        
        $ExcludeAirlines = json_decode($provider->exclude_airlines,true);
        $ExcludeAirlines = $ExcludeAirlines ?? [];
        
        $requestPaxCount = [
            'Adult' => $request['adults'],
            'Child' => $request['children'],
            'Infant' => $request['infants'],
        ];

        $possibleCabinValues = [
            'Y' => 'Economy',
            'S' => 'Premium Economy',
            'C' => 'Business',
            'J' => 'Premium Business',
            'F' => 'First',
            'P' => 'Premium First'
        ];

        $apiRes = json_decode($res,true);
        $res = json_decode($res);
        $finalData = array();
        $AllBrandFeaturesArray = @$apiRes['OTA_AirLowFareSearchRS']['BrandFeatures'];
        $PricedItineraryArray = $apiRes['OTA_AirLowFareSearchRS']['PricedItineraries']['PricedItinerary'];

        foreach($PricedItineraryArray as $itnIndex => $PricedItinerary){
            if (isset($PricedItinerary['TPA_Extensions']['ValidatingCarrier']['Code']) && 
            !in_array($PricedItinerary['TPA_Extensions']['ValidatingCarrier']['Code'], $ExcludeAirlines)) {
                $flights = $PricedItinerary['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption'];
                $AirItineraryPricingInfo = $PricedItinerary['AirItineraryPricingInfo'];
                $finalData[$itnIndex]['SequenceNumber'] = $PricedItinerary['SequenceNumber'];
                
                $PTC_FareBreakdown0 = $AirItineraryPricingInfo[0]['PTC_FareBreakdowns']['PTC_FareBreakdown'][0];
                foreach($flights as $flightIndex => $flight){
                    $flightsCount = count($flights);
                    $finalData[$itnIndex]['Flights'][$flightIndex]['TotalDuration'] = $flight['ElapsedTime'];
                    $finalData[$itnIndex]['Flights'][$flightIndex]['NonRefundable'] = $PTC_FareBreakdown0['Endorsements']['NonRefundableIndicator'];
                    $FareInfo = $PTC_FareBreakdown0['FareInfos']['FareInfo'];
                    $flightNndSegments = [];
                    foreach($flight['FlightSegment'] as $segKey => $segment){
                        $segmentCabin = $FareInfo[$segKey]['TPA_Extensions']['Cabin']['Cabin'];

                        $finalData[$itnIndex]['MarketingAirline']['Airline'] = $PricedItinerary['TPA_Extensions']['ValidatingCarrier']['Code'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['Duration'] = $segment['ElapsedTime'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['MarketingAirline']['Code'] = $segment['MarketingAirline']['Code'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['MarketingAirline']['FlightNumber'] = $segment['FlightNumber'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['OperatingAirline']['Code'] = $segment['OperatingAirline']['Code'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['OperatingAirline']['FlightNumber'] = $segment['OperatingAirline']['FlightNumber'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['EquipType'] = $segment['Equipment'][0]['AirEquipType'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['Departure']['LocationCode'] = $segment['DepartureAirport']['LocationCode'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['Departure']['Terminal'] = @$segment['DepartureAirport']['TerminalID'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['Arrival']['LocationCode'] = $segment['ArrivalAirport']['LocationCode'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['Arrival']['Terminal'] = @$segment['ArrivalAirport']['TerminalID'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['Arrival']['ArrivalDateTime'] = $segment['ArrivalDateTime'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['Departure']['DepartureDateTime'] = $segment['DepartureDateTime'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['AvailableSeats'] = $FareInfo[$segKey]['TPA_Extensions']['SeatsRemaining']['Number'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['Cabin'] = $FareInfo[$segKey]['TPA_Extensions']['Cabin']['Cabin'];
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Segments'][$segKey]['CabinClass'] = $possibleCabinValues[$segmentCabin];
                        $flightNndSegments[$segKey]['DepartureAirportCode'] = $segment['DepartureAirport']['LocationCode'];
                        $flightNndSegments[$segKey]['ArrivalAirportCode'] = $segment['ArrivalAirport']['LocationCode'];
                    }
                    
                    ///////////////////////////////////ItinTotalFare/////////////////////////////////////////////////
                    $ItinTotalFare = $AirItineraryPricingInfo[0]['ItinTotalFare'];
                    $PTC_FareBreakdown_basic = $AirItineraryPricingInfo[0]['PTC_FareBreakdowns']['PTC_FareBreakdown'];

                    $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['RefID'] = Str::uuid();
                    //---------------------------PassengerFares---------------//
                    $PassengerFares_basic = array();
                    $PassengerBaggage_basic = array();
                    
                    foreach($PTC_FareBreakdown_basic as $FareBreakdown){
                        $PassengerFare = $FareBreakdown['PassengerFare'];
                        $FareBasisCodes = $FareBreakdown['FareBasisCodes']['FareBasisCode'];

                        $filteredFareBasisCodes = array_filter($FareBasisCodes, function($fareBasis) use ($flightNndSegments) {
                            foreach ($flightNndSegments as $segment) {
                                if ($segment['DepartureAirportCode'] == $fareBasis['DepartureAirportCode'] &&
                                    $segment['ArrivalAirportCode'] == $fareBasis['ArrivalAirportCode']) {
                                    return true;
                                }
                            }
                            return false;
                        });
                        $PaxTypeBasic = ($FareBreakdown['PassengerTypeQuantity']['Code'] == 'ADT') ? 'Adult' : ($FareBreakdown['PassengerTypeQuantity']['Code'] == 'CNN' ? 'Child' : 'Infant');
                        $passFare = array(
                            'PaxType' => $PaxTypeBasic,
                            'Quantity' => $requestPaxCount[$PaxTypeBasic],
                            'Currency' => $PassengerFare['TotalFare']['CurrencyCode'],
                            'BaseUSD' => (int) $PassengerFare['BaseFare']['Amount'],
                            'BasePrice' => (int) $PassengerFare['EquivFare']['Amount'],
                            'Taxes' => isset($PassengerFare['Taxes']['TotalTax']['Amount']) ? (int) $PassengerFare['Taxes']['TotalTax']['Amount'] : 0,
                            'Fees' => 0,
                            'ServiceCharges' => 0,
                            'TotalPrice' => (int) $PassengerFare['TotalFare']['Amount'],
                        );
                        array_push($PassengerFares_basic, $passFare);

                        $finalData[$itnIndex]['Flights'][$flightIndex]['MultiFares'] = false;
                        if(@$PassengerFare['TPA_Extensions']['FareComponents']){
                            $firstBrandFareComponent = $PassengerFare['TPA_Extensions']['FareComponents']['FareComponent'];
                            foreach($firstBrandFareComponent as $brandKey => $firstBrand){
                                // Storage::put('Sabre/ErrorLog-'.$itnIndex.'-'.$brandKey.'.json', json_encode($firstBrand, JSON_PRETTY_PRINT));

                                if(@$firstBrand['BrandName']){
                                    if($brandKey == $flightIndex){
                                        $finalData[$itnIndex]['Flights'][$flightIndex]['MultiFares'] = true;
                                        $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['BrandID'] = $firstBrand['BrandID'];
                                        $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['Name'] = $firstBrand['BrandName'];
                                        // $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['FareBases'] = $PassengerFares_basic[0]['FareBases'];
                                        if(@$firstBrand['BrandFeatureRef']){
                                            $featureIds = collect($firstBrand['BrandFeatureRef'])->pluck('FeatureId');
                                            $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['BrandFeatures'] = collect($AllBrandFeaturesArray['BrandFeature'])
                                                ->whereIn('Id', $featureIds)
                                                ->whereIn('Application', 'F')
                                                ->pluck('CommercialName')
                                                ->toArray();
                                            $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['AdditionalBrandFeatures'] = collect($AllBrandFeaturesArray['BrandFeature'])
                                                ->whereIn('Id', $featureIds)
                                                ->whereIn('Application', 'C')
                                                ->pluck('CommercialName')
                                                ->toArray();
                                        }else{
                                            $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['BrandFeatures'] = '';
                                            $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['AdditionalBrandFeatures'] = '';
                                        }
                                    }
                                }
                            }
                        }
                        $BaggageInformation = $PassengerFare['TPA_Extensions']['BaggageInformationList']['BaggageInformation'][$flightIndex];
                        $passBaggBasic = array(
                            'PaxType' => $PaxTypeBasic,
                            'Weight' => @$BaggageInformation['Allowance'][0]['Weight'] ?? @$BaggageInformation['Allowance'][0]['Pieces'],
                            'Unit' => isset($BaggageInformation['Allowance'][0]['Weight']) ? $BaggageInformation['Allowance'][0]['Unit'] : 'Piece',
                        );
                        array_push($PassengerBaggage_basic, $passBaggBasic);

                        $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['FareBases'] = array_values($filteredFareBasisCodes);
                    }
                    //---------------------------End PassengerFares---------------//
                    $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['Currency'] = $ItinTotalFare['TotalFare']['CurrencyCode'];
                    $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['BaseUSD'] = (int) $ItinTotalFare['BaseFare']['Amount'];
                    $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['BasePrice'] = (int) $ItinTotalFare['EquivFare']['Amount'];
                    $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['Taxes'] = (int) $ItinTotalFare['Taxes']['Tax'][0]['Amount'];
                    $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['TotalFare'] = (int) $ItinTotalFare['TotalFare']['Amount'];
                    $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['BillablePrice'] = (int) $ItinTotalFare['TotalFare']['Amount'];
                    $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['SingleFlightFare'] = (int) $ItinTotalFare['TotalFare']['Amount'] / $flightsCount;
                    $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['Policies'] = '';
                    $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['PassengerFares'] = $PassengerFares_basic;
                    $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['BaggagePolicy'] = $PassengerBaggage_basic;

                    if(@$PricedItinerary['TPA_Extensions']['AdditionalFares']){
                        $AdditionalFares = $PricedItinerary['TPA_Extensions']['AdditionalFares'];
                        
                        $additionalbrandedFares = collect($AdditionalFares)
                        
                            ->filter(function ($AdditionalFare) {
                                return $AdditionalFare['AirItineraryPricingInfo']['FareReturned'];
                            })
                            ->map(function ($AdditionalFares) use ($AllBrandFeaturesArray,$flightIndex,$itnIndex,$flightsCount,$flightNndSegments,$requestPaxCount) {
                                $PTC_FareBreakdown = $AdditionalFares['AirItineraryPricingInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown'];
                                $additionalItinTotalFare = $AdditionalFares['AirItineraryPricingInfo']['ItinTotalFare'];
                                $PassengerFares = array();
                                $PassengerBaggage = array();
                                foreach($PTC_FareBreakdown as $FareBreakdown){
                                    $PassengerFare = $FareBreakdown['PassengerFare'];
                                    $PaxType = ($FareBreakdown['PassengerTypeQuantity']['Code'] == 'ADT') ? 'Adult' : ($FareBreakdown['PassengerTypeQuantity']['Code'] == 'CNN' ? 'Child' : 'Infant');
                                    $passFare = array(
                                        'PaxType' => $PaxType,
                                        'Quantity' => $requestPaxCount[$PaxType],
                                        'Currency' => $PassengerFare['TotalFare']['CurrencyCode'],
                                        'BaseUSD' => (int) $PassengerFare['BaseFare']['Amount'],
                                        'BasePrice' => (int) $PassengerFare['EquivFare']['Amount'],
                                        'Taxes' => isset($PassengerFare['Taxes']['TotalTax']['Amount']) ? (int) $PassengerFare['Taxes']['TotalTax']['Amount'] : 0,
                                        'Fees' => 0,
                                        'ServiceCharges' => 0,
                                        'TotalPrice' => (int) $PassengerFare['TotalFare']['Amount'],
                                    );
                                    array_push($PassengerFares, $passFare);

                                    $BaggageInformation = $FareBreakdown['PassengerFare']['TPA_Extensions']['BaggageInformationList']['BaggageInformation'][$flightIndex];
                                    $weight = @$BaggageInformation['Allowance'][0]['Weight'] ?? @$BaggageInformation['Allowance'][0]['Pieces'];
                                    $unit = isset($BaggageInformation['Allowance'][0]['Weight']) ? $BaggageInformation['Allowance'][0]['Unit'] : 'Pieces';

                                    $passBagg = array(
                                        'PaxType' => $PaxType,
                                        'Weight' => @$BaggageInformation['Allowance'][0]['Weight'] ?? @$BaggageInformation['Allowance'][0]['Pieces'],
                                        'Unit' => isset($BaggageInformation['Allowance'][0]['Weight']) ? $BaggageInformation['Allowance'][0]['Unit'] : 'Piece',
                                    );
                                    array_push($PassengerBaggage, $passBagg);
                                }

                                $FareComponentArray = $PTC_FareBreakdown[0]['PassengerFare']['TPA_Extensions']['FareComponents']['FareComponent'];
                                $FareBasisCode = $PTC_FareBreakdown[0]['FareBasisCodes']['FareBasisCode'];

                                $filteredFareBasisCodes2 = array_filter($FareBasisCode, function($fareBasis) use ($flightNndSegments) {
                                    foreach ($flightNndSegments as $segment) {
                                        if ($segment['DepartureAirportCode'] == $fareBasis['DepartureAirportCode'] &&
                                            $segment['ArrivalAirportCode'] == $fareBasis['ArrivalAirportCode']) {
                                            return true;
                                        }
                                    }
                                    return false;
                                });
                                
                                foreach($FareComponentArray as $componentindex => $FareComponent){
                                    // dd($FareComponent);
                                    if($componentindex == $flightIndex){
                                        if(@$FareComponent['BrandFeatureRef']){
                                            $featureIds = collect($FareComponent['BrandFeatureRef'])->pluck('FeatureId');
                                            return [
                                                'RefID' => Str::uuid(),
                                                'BrandID' => $FareComponent['BrandID'],
                                                'Name' => $FareComponent['BrandName'],
                                                'FareBases' => array_values($filteredFareBasisCodes2),
                                                'BrandFeatures' => collect($AllBrandFeaturesArray['BrandFeature'])
                                                    ->whereIn('Id', $featureIds)
                                                    ->whereIn('Application', 'F')
                                                    ->pluck('CommercialName')
                                                    ->toArray(),
                                                'AdditionalBrandFeatures' => collect($AllBrandFeaturesArray['BrandFeature'])
                                                    ->whereIn('Id', $featureIds)
                                                    ->whereIn('Application', 'C')
                                                    ->pluck('CommercialName')
                                                ->toArray(),
                                                'Currency' => $additionalItinTotalFare['TotalFare']['CurrencyCode'],
                                                'BaseUSD' => (int) $additionalItinTotalFare['BaseFare']['Amount'],
                                                'BasePrice' => (int) $additionalItinTotalFare['EquivFare']['Amount'],
                                                'Taxes' => (int) $additionalItinTotalFare['Taxes']['Tax'][0]['Amount'],
                                                'TotalFare' => (int) $additionalItinTotalFare['TotalFare']['Amount'],
                                                'BillablePrice' => $additionalItinTotalFare['TotalFare']['Amount'],
                                                'SingleFlightFare' => (int) $additionalItinTotalFare['TotalFare']['Amount'] / $flightsCount,
                                                'Policies' => '',
                                                'PassengerFares' => $PassengerFares,
                                                'BaggagePolicy' => $PassengerBaggage,
                                            ];
                                        }
                                    }
                                }
                            })->toArray();
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'] = array_merge($finalData[$itnIndex]['Flights'][$flightIndex]['Fares'],$additionalbrandedFares);
                    }
                }
                $finalData[$itnIndex]['api'] = "Sabre";
                $finalData[$itnIndex]['MarketingAirline']['FareRules'] = "NA";
                // if(auth('admin')->user()->type == "Travel Agent"){
                    $pricingEnginePrice = PricingEngineTrait::applyPricingEngine((object) $finalData[$itnIndex], $request);
                    $finalData[$itnIndex] = json_decode(json_encode($pricingEnginePrice),true);
                // }

                $apiOffer = new ApiOffer();
                $apiOffer->ref_key = Str::uuid();
                $apiOffer->api = "Sabre";
                $apiOffer->data = json_encode($PricedItinerary);
                $apiOffer->finaldata = $finalData[$itnIndex];
                $apiOffer->timestamp = time();
                $apiOffer->query = json_encode($request);
                $apiOffer->save();

                $finalData[$itnIndex]['itn_ref_key'] = $apiOffer->ref_key;
            }
        }
        return $finalData;
    }
    // ********************************************************\\
    public static function curl_action($type, $url, $data, $key = null, $apiToken = null)
    {
        if (!$key) {
            $key = self::sabre_auth($apiToken);
        }
        if ($key) {
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
            curl_setopt($curl2, CURLOPT_TIMEOUT, 600);
            curl_setopt($curl2, CURLOPT_RETURNTRANSFER, TRUE);
            $response = curl_exec($curl2);
            curl_close($curl2);
            if (curl_errno($curl2)) {
                echo 'cURL Error: ' . curl_error($curl2);
            }
            // Storage::put('Sabre/flightSearchResponse.json', $response);
            // $res = ['key' => $key, 'res' => $response];
            return json_decode($response, true);
        } else {
            return array();
        }
    }
    public static function curl_action1($type, $url, $data, $key = null, $apiToken = null)
    {
        // return 'Auth Tocken---------'. $key;
        if (!$key) {
            $key = self::sabre_auth();
            //return $key;
        }
        $conversationId  = date('Y-m-d') . '- DevStudio';
        // return $key;
        if ($key) {
            $curl2 = curl_init();
            $header = array();
            $header[] = "Authorization: Bearer " . $key;
            $header[] = "Content-Type: application/json";
            $header[] = "Conversation-ID: " . $conversationId;
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

            return $response;
        } else {
            return array();
        }
    }
    public static function getDuration($d1, $d2)
    {
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
    public static function getNameRef($type, $date)
    {
        $to = Carbon::parse($date);
        $from = Carbon::now();
        switch ($type) {
            case 'CNN':
                $diff = $to->diffInYears($from);
                $nameRef = $diff < 10 ? 'C0' . $diff : 'C' . $diff;
                break;
            case 'INF':
                $diff = $to->diffInMonths($from);
                $nameRef = $diff < 10 ? 'I0' . $diff : 'I' . $diff;
                break;
        }
        return $nameRef;
    }
    /*********************************************\
     *              Soap requests                *|
    \*********************************************/
    public static function repricePNR($order){
        $customer_data = json_decode($order->customer_data,true);
        $final_data = json_decode($order->final_data,true);

        $Itinerary = $final_data['Flights'];
        $flightBrands = [];
        if(@$customer_data['brand_ref_key']){
            foreach ($final_data['Flights'] as $flight) {
                if (isset($flight['Fares']) && is_array($flight['Fares'])) {
                    $filteredFares = array_filter($flight['Fares'], function ($fare) use ($customer_data) {
                        return in_array($fare['RefID'], $customer_data['brand_ref_key']);
                    });

                    if (!empty($filteredFares)) {
                        foreach ($filteredFares as $filteredFare) {
                            $flightBrands[] = $filteredFare;
                        }
                    }
                }
            }
        }
        $pnrCode = $order->pnrCode;
        //////////////API Call////////////////////////
            $binaryToken = self::SessionCreateRQ_Soap();
            self::GetReservationRQ_Soap($pnrCode,$binaryToken);
            self::DeletePriceQuoteRQ_Soap($binaryToken);
            $airPriceResponse = self::OTA_AirPriceRQ_Soap($binaryToken,$customer_data,$flightBrands,$Itinerary);
            self::EnhancedEndTransactionRQ_Soap($binaryToken);
            $OTA_AirPriceRS = $airPriceResponse['OTA_AirPriceRS'];
        /////////////End API Call///////////////////////
            // $airPriceResponse = Storage::get('Sabre/Soap/3-OTA_AirPriceRQResponse.json');
            // $airPriceResponse = json_decode($airPriceResponse, true);
            // $OTA_AirPriceRS = $airPriceResponse['Body']['OTA_AirPriceRS'];
            // dd($OTA_AirPriceRS);
        ///////////////////////////////////////////////
        $PassengerFaresArray = array();
        $TotalAmount = $OTA_AirPriceRS['PriceQuote']['PricedItinerary']['attributes']['TotalAmount'];
        $AirItineraryPricingInfo = $OTA_AirPriceRS['PriceQuote']['PricedItinerary']['AirItineraryPricingInfo'];

        if(!array_key_exists(0,$AirItineraryPricingInfo)){
            $AirItineraryPricingInfo[0] = $AirItineraryPricingInfo;
        }
        $passFare = '';
        foreach($AirItineraryPricingInfo as $PricingInfo){
            $PaxQuantity = $PricingInfo['PassengerTypeQuantity']['attributes']['Quantity'];
            $PaxTypeCode = $PricingInfo['PassengerTypeQuantity']['attributes']['Code'];
            if($PaxTypeCode == 'ADT'){
                $PaxTypeText = 'Adult';
            }elseif($PaxTypeCode == 'CNN'){
                $PaxTypeText = 'Child';
            }else{
                $PaxTypeText = 'Infant';
            }
            $passFare = [
                'PaxType' => $PaxTypeText,
                'Quantity' => $PaxQuantity,
                'Currency' => 'PKR',
                'BasePrice' => $PricingInfo['ItinTotalFare']['EquivFare']['attributes']['Amount'],
                'Taxes' => $PricingInfo['ItinTotalFare']['Taxes']['attributes']['TotalAmount'],
                'Fees' => 0,
                'ServiceCharges' => 0,
                'TotalPrice' => (int) $PricingInfo['ItinTotalFare']['EquivFare']['attributes']['Amount'] + (int) $PricingInfo['ItinTotalFare']['Taxes']['attributes']['TotalAmount'],
            ];
            array_push($PassengerFaresArray,$passFare);
        }
        // dd($PassengerFaresArray);

        $final_data['Flights'][0]['Fares'][0]['TotalFare'] = $TotalAmount;
        $final_data['Flights'][0]['Fares'][0]['BillablePrice'] = $TotalAmount;
        $final_data['Flights'][0]['Fares'][0]['SingleFlightFare'] = $TotalAmount;

        $final_data['Flights'][0]['Fares'][0]['PassengerFares'] = $PassengerFaresArray;
        // dd($final_data);
        // return ['status'=> '200', 'msg' => json_encode($OTA_AirPriceRS), 'final_data' => $final_data];

        if (array_key_exists('Fault', $airPriceResponse)) {
            return ['status' => '400', 'msg' => json_encode($airPriceResponse)];
        } elseif(array_key_exists('OTA_AirPriceRS', $airPriceResponse)) {
            return ['status'=> '200', 'msg' => json_encode($OTA_AirPriceRS), 'final_data' => $final_data];
        }

    }
    public static function DeletePriceQuoteRQ_Soap($binaryToken)
    {
        $body = '<DeletePriceQuoteRQ ReturnHostCommand="true" xmlns="http://webservices.sabre.com/sabreXML/2011/10" Version="2.1.0">
                    <AirItineraryPricingInfo>
                        <Record All="true"/>
                    </AirItineraryPricingInfo>
                </DeletePriceQuoteRQ>';

        $soap_token_attr = '<wsse:BinarySecurityToken valueType="String">' . $binaryToken . '</wsse:BinarySecurityToken>';
        $soap_message = self::soap_request('DeletePriceQuoteLLSRQ', $soap_token_attr, $body);
        $response = self::soap_curl_action($soap_message);
        $response = self::prettyPrint($response);
        Storage::put('Sabre/Soap/2-DeletePriceQuoteResponse.xml', $response);

        $jsonResponse = self::removeNamespaceFromXMLAndConvertToArray($response);
        // dd($jsonResponse);
        $BinarySecurityToken = $jsonResponse['Header']['Security']['BinarySecurityToken'];
        return $BinarySecurityToken;
    }
    public static function OTA_AirPriceRQ_Soap($binaryToken,$customer_data,$flightBrands,$Itinerary)
    {
        $SegmentSelect = '';
        $segmentSelectKey = 1;
        $RphItenKey = 1;
        $startNumber = null;

        foreach($Itinerary as $itnKey => $leg){
            $totalSegments = count($leg['Segments']);
            $currentSegment = 1;
            foreach ($leg['Segments'] as $segmentKey => $segment) {
                //////////////////////Segment Select/////////////////////
                if ($currentSegment === 1) {
                    $startNumber = $segmentSelectKey;
                }

                if ($currentSegment === $totalSegments) {
                    $SegmentSelect .= '<SegmentSelect EndNumber="'.$segmentSelectKey.'" Number="'.$startNumber.'" RPH="'.$RphItenKey.'"/>';
                    $startNumber = null;
                }

                $segmentSelectKey++;
                $currentSegment++;
            }
            $RphItenKey++;
        } 
        $ItineraryOptions = '<ItineraryOptions>
                                '.$SegmentSelect.'
                            </ItineraryOptions>';
        ////////////////////////////////////////////


        $passengersCounts = array_reduce($customer_data['passengers'], function($carry, $passenger) {
            $type = $passenger['passenger_type'];
            if (!isset($carry[$type])) {
                $carry[$type] = 0;
            }
            $carry[$type]++;
            return $carry;
        }, []);
        
        $PassengerTypeArray = '';
        
        foreach($passengersCounts as $passKey => $passQuantity){
            $PassengerTypeArray .= '<PassengerType Code="'.$passKey.'" Quantity="'.$passQuantity.'"/>';
        }

        $brands = '';
        $SpecificFare = '';
        if(!empty($flightBrands)){
            $rph = 1;
            $FareBases = '';
            if(count($flightBrands) > 1){
                foreach($flightBrands as $key => $brand){
                    // $brands = '<Brand RPH="1">'.$brand["BrandID"].'</Brand>';
                    // $FareBases .= '<FareBasis RPH="'.$rph.'">'.$brand["FareBases"][0]["content"].'</FareBasis>';
                    $FareBases .= '<SpecificFare RPH="'.$rph.'"><FareBasis>'.$brand["FareBases"][0]["content"].'</FareBasis></SpecificFare>';
                    $rph++;
                }
            }else{
                $FareBases .= '<SpecificFare><FareBasis>'.$flightBrands[0]["FareBases"][0]["content"].'</FareBasis></SpecificFare>';
            }
            // $SpecificFare = '<SpecificFare>'.$FareBases.'</SpecificFare>';
            $SpecificFare = $FareBases;
        }
        
        $body = '<OTA_AirPriceRQ ReturnHostCommand="true" xmlns="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Version="2.17.0">
                    <PriceRequestInformation Retain="true">
                        <OptionalQualifiers>
                            <PricingQualifiers>
                                '.$PassengerTypeArray.'
                                '.$SpecificFare.'
                            </PricingQualifiers>
                        </OptionalQualifiers>
                    </PriceRequestInformation>
                    
                </OTA_AirPriceRQ>';
        // dd($body);
        $soap_token_attr = '<wsse:BinarySecurityToken valueType="String">' . $binaryToken . '</wsse:BinarySecurityToken>';
        $soap_message = self::soap_request('OTA_AirPriceLLSRQ', $soap_token_attr, $body);
        $response = self::soap_curl_action($soap_message);
        $response = self::prettyPrint($response);
        Storage::put('Sabre/Soap/3-OTA_AirPriceRQResponse.xml', $response);

        $jsonResponse = self::removeNamespaceFromXMLAndConvertToArray($response);
        // dd($jsonResponse);
        Storage::put('Sabre/Soap/3-OTA_AirPriceRQResponse.json', json_encode($jsonResponse, JSON_PRETTY_PRINT));
        // $BinarySecurityToken = $jsonResponse['Header']['Security']['BinarySecurityToken'];
        return $jsonResponse['Body'];
    }
    public static function EnhancedEndTransactionRQ_Soap($binaryToken)
    {
        $body = '<EndTransactionRQ Version="2.2.0" xmlns="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <EndTransaction Ind="true"/>
                    <Source ReceivedFrom="API TEST"/>
                </EndTransactionRQ>';

        $soap_token_attr = '<wsse:BinarySecurityToken valueType="String">' . $binaryToken . '</wsse:BinarySecurityToken>';
        $soap_message = self::soap_request('EndTransactionLLSRQ', $soap_token_attr, $body);
        $response = self::soap_curl_action($soap_message);
        $response = self::prettyPrint($response);
        Storage::put('Sabre/Soap/4-EndTransactionRQResponse.xml', $response);

        $jsonResponse = self::removeNamespaceFromXMLAndConvertToArray($response);
        $BinarySecurityToken = $jsonResponse['Header']['Security']['BinarySecurityToken'];
        $sessionCloseResponse =  self::SessionCloseRQ_Soap($BinarySecurityToken);
        return $sessionCloseResponse;
        // dd($sessionCloseResponse);
    }
    //////////////////////////////////////////////////////
    public static function ContextChangeRQ($binaryToken){
        $body = '<ContextChangeRQ ReturnHostCommand="true" Version="2.0.3" xmlns="http://webservices.sabre.com/sabreXML/2011/10">
                    <ChangeAAA PseudoCityCode="9H3K"/>
                </ContextChangeRQ>';
        /***********************************************\
         **************Voi tkt soap*********************|
        \***********************************************/
        // Storage::put('sabre/Soap/voidTktReq.xml', $body);
        $soap_token_attr = '<wsse:BinarySecurityToken valueType="String" EncodingType="wsse:Base64Binary">'.$binaryToken.'</wsse:BinarySecurityToken>';
        $soap_message = self::soap_request('ContextChangeLLSRQ', $soap_token_attr, $body);
        // $soap_message = self::soap_request('ContextChangeLLSRQ', self::soap_credentials(), $body);
        $response = self::soap_curl_action($soap_message);
        $response = self::prettyPrint($response);
        Storage::put('sabre/Soap/ContextChangeLLSRQ-Resp.xml', $response);
        /*************************OLD Response Rule*********************/
        // $response = Storage::get('sabre/Soap/FareRulesRes1.xml');
        /*************************End OLD Response*********************/
        $jsonResponse = self::removeNamespaceFromXMLAndConvertToArray($response);
        // $BinarySecurityToken = $jsonResponse['Header']['Security']['BinarySecurityToken'];
        return $jsonResponse;
    }
    public static function DesignatePrinterRQ($binaryToken){
        // $body = '<DesignatePrinterRQ Version="2.0.2" xmlns="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        //             <Hardcopy LNIATA="69FDCF"/>
        //             <InvoiceItinerary LNIATA="69FDCF"/>
        //             <Ticket CountryCode="PK" LNIATA="69FDCF"/>
        //             <Profile Number="1"/>
        //         </DesignatePrinterRQ>';
        $body = '<DesignatePrinterRQ Version="2.0.2" xmlns="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <Printers>
                        <Hardcopy LNIATA="69FDCF"/>
                    </Printers>
                </DesignatePrinterRQ>
                <DesignatePrinterRQ Version="2.0.2" xmlns="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <Printers>
                        <Ticket CountryCode="PK" LNIATA="69FDCF"/>
                    </Printers>
                </DesignatePrinterRQ>
                <DesignatePrinterRQ Version="2.0.2" xmlns="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <Profile Number="1"/>
                </DesignatePrinterRQ>';
        /***********************************************\
         **************Voidtkt soap*********************|
        \***********************************************/
        Storage::put('sabre/Soap/voidTktReq.xml', $body);
        $soap_token_attr = '<wsse:BinarySecurityToken valueType="String" EncodingType="wsse:Base64Binary">' . $binaryToken. '</wsse:BinarySecurityToken>';
        $soap_message = self::soap_request('DesignatePrinterLLSRQ', $soap_token_attr, $body);
        $response = self::soap_curl_action($soap_message);
        $response = self::prettyPrint($response);
        Storage::put('sabre/Soap/DesignatePrinterRQ-Resp.xml', $response);
        /*************************OLD Response Rule*********************/
        // $response = Storage::get('sabre/Soap/FareRulesRes1.xml');
        /*************************End OLD Response*********************/

        $jsonResponse = self::removeNamespaceFromXMLAndConvertToArray($response);
        $BinarySecurityToken = $jsonResponse['Header']['Security']['BinarySecurityToken'];
        return $BinarySecurityToken;
    }
    public static function voidTktSoap($binaryToken){
        // $body = '<VoidTicketRQ Version="2.1.0" xmlns="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        //             <Ticketing RPH="2"/>
        //         </VoidTicketRQ>';
                // $body = '<VoidTicketRQ Version="2.1.0" xmlns="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                //             <Ticketing eTicketNumber="2146078980226"/>
                //         </VoidTicketRQ>';
        $body = '<VoidTicketRQ ReturnHostCommand="true" Version="2.1.0" xmlns="http://webservices.sabre.com/sabreXML/2011/10">
                    <Ticketing RPH="2"/>
                </VoidTicketRQ>
                <VoidTicketRQ Version="2.1.0" xmlns="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <Ticketing eTicketNumber="2146003791343"/>
                </VoidTicketRQ>';
        /***********************************************\
         **************Voi tkt soap*********************|
        \***********************************************/
        // Storage::put('sabre/Soap/voidTktReq.xml', $body);
        $soap_token_attr = '<wsse:BinarySecurityToken valueType="String" EncodingType="wsse:Base64Binary">'.$binaryToken.'</wsse:BinarySecurityToken>';
        $soap_message = self::soap_request('VoidTicketLLSRQ', $soap_token_attr, $body);
        $response = self::soap_curl_action($soap_message);
        $response = self::prettyPrint($response);
        Storage::put('sabre/Soap/VoidTicketLLSRQ-Resp.xml', $response);
        /*************************OLD Response Rule*********************/
        // $response = Storage::get('sabre/Soap/FareRulesRes1.xml');
        /*************************End OLD Response*********************/
        $jsonResponse = self::removeNamespaceFromXMLAndConvertToArray($response);
        $BinarySecurityToken = $jsonResponse['Header']['Security']['BinarySecurityToken'];
        return $BinarySecurityToken;
    }
    public static function voidTktSoap2($binaryToken){
        $body = '<VoidTicketRQ Version="2.1.0" xmlns="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <Ticketing RPH="2"/>
                </VoidTicketRQ>';
        // $body = '<VoidTicketRQ Version="2.1.0" xmlns="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        //             <Ticketing eTicketNumber="2146078980226"/>
        //         </VoidTicketRQ>';
        /***********************************************\
         **************Voi tkt soap*********************|
        \***********************************************/
        // Storage::put('sabre/Soap/voidTktReq.xml', $body);
        $soap_token_attr = '<wsse:BinarySecurityToken valueType="String" EncodingType="wsse:Base64Binary">'.$binaryToken.'</wsse:BinarySecurityToken>';
        $soap_message = self::soap_request('VoidTicketLLSRQ', $soap_token_attr, $body);
        $response = self::soap_curl_action($soap_message);
        $response = self::prettyPrint($response);
        Storage::put('sabre/Soap/VoidTicketLLSRQ-Resp2.xml', $response);
        /*************************OLD Response Rule*********************/
        // $response = Storage::get('sabre/Soap/FareRulesRes1.xml');
        /*************************End OLD Response*********************/
        self::SessionCloseRQ_Soap($binaryToken);
        
        $jsonResponse = self::removeNamespaceFromXMLAndConvertToArray($response);
        dd($jsonResponse);
    }
    public static function GetReservationRQ_Soap($pnr,$binaryToken)
    {
        $body = '<GetReservationRQ xmlns="http://webservices.sabre.com/pnrbuilder/v1_19" Version="1.19.0">
                    <Locator>' . $pnr . '</Locator>
                    <RequestType>Statefull</RequestType>
                    <ReturnOptions PriceQuoteServiceVersion="3.2.0">
                        <SubjectAreas>
                        <SubjectArea>ACTIVE</SubjectArea>
                        <SubjectArea>PRICE_QUOTE</SubjectArea>
                        <SubjectArea>ADD_TKT_NAME_SEG_ASSOC</SubjectArea>
                        </SubjectAreas>
                        <ViewName>Full</ViewName>
                    </ReturnOptions>
                </GetReservationRQ>';
        $soap_token_attr = '<wsse:BinarySecurityToken valueType="String">' . $binaryToken . '</wsse:BinarySecurityToken>';
        $soap_message = self::soap_request('GetReservationRQ', $soap_token_attr, $body);
        $response = self::soap_curl_action($soap_message);
        $response = self::prettyPrint($response);
        Storage::put('Sabre/Soap/1-GetReservationResponse.xml', $response);

        $jsonResponse = self::removeNamespaceFromXMLAndConvertToArray($response);
        // dd($jsonResponse);
        $BinarySecurityToken = $jsonResponse['Header']['Security']['BinarySecurityToken'];
        return $BinarySecurityToken;
    }
    public static function airRulesRQ($finaldata, $brand_ref_key = null) {
        $MarketingAirline = $finaldata['MarketingAirline']['Airline'];
        $flightArray = [];
        if($brand_ref_key){
            foreach($finaldata['Flights'] as $key => $flight){
                // if($key == 1){

                    if (isset($flight['Fares']) && is_array($flight['Fares'])) {
                        $filteredFares = array_filter($flight['Fares'], function ($fare) use ($brand_ref_key,$key) {
                            return $fare['RefID'] == $brand_ref_key[$key];
                        });
                    }
                    $filteredFares = array_values($filteredFares);
                    // dd($filteredFares);
                    $FareBasisCode = $filteredFares[0]['FareBases'][0]['content'];
                    
                    $flights = $finaldata['Flights'][0];
                    $Segments = $flights['Segments'];
                    $Date = $Segments[0]['Departure']['DepartureDateTime'];
                    $departureCode = $Segments[0]['Departure']['LocationCode'];
                    $arrivalCode = end($Segments)['Arrival']['LocationCode'];

                    $body = '<ns3:OTA_AirRulesRQ ReturnHostCommand="true" Version="2.3.0" xmlns:ns3="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                                    <ns3:OriginDestinationInformation>
                                        <ns3:FlightSegment DepartureDateTime="'.date('m-d',strtotime($Date)).'">
                                        <ns3:DestinationLocation LocationCode="'.$arrivalCode.'"/>
                                        <ns3:MarketingCarrier Code="'.$MarketingAirline.'"/>
                                        <ns3:OriginLocation LocationCode="'.$departureCode.'"/>
                                        </ns3:FlightSegment>
                                    </ns3:OriginDestinationInformation>
                                    <ns3:RuleReqInfo>
                                        <ns3:FareBasis Code="'.$FareBasisCode.'" DisplayRouting="true"/>
                                    </ns3:RuleReqInfo>
                                </ns3:OTA_AirRulesRQ>';
                    /***********************************************\
                     **************Fetch Fare Rules*****************|
                    \***********************************************/
                    $binaryToken = self::SessionCreateRQ_Soap();
                    $soap_token_attr = '<wsse:BinarySecurityToken valueType="String" EncodingType="wsse:Base64Binary">' . $binaryToken . '</wsse:BinarySecurityToken>';
                    $soap_message = self::soap_request('OTA_AirRulesLLSRQ', $soap_token_attr, $body);
                    $response = self::soap_curl_action($soap_message);
                    
                    $response = self::prettyPrint($response);
                    Storage::put('sabre/Soap/OTA_AirRulesLLS-Res-'.$key.'.xml', $response);
                    /*************************OLD Response Rule*********************/
                    // $response = Storage::get('sabre/Soap/FareRulesRes1.xml');
                    /*************************End OLD Response*********************/

                    $jsonResponse = self::removeNamespaceFromXMLAndConvertToArray($response);
                    if(array_key_exists('DuplicateFareInfo', $jsonResponse['Body']['OTA_AirRulesRS'])){
                        $jsonResponse = self::DuplicateFareInfo($FareBasisCode,$binaryToken);   
                    }
                    $resp = self::parseFareRuleResponse($jsonResponse);
                    
                    array_push($flightArray, $resp);
                // }
                self::SessionCloseRQ_Soap($binaryToken);
            }
        }else{
            foreach($finaldata['Flights'] as $key => $flight){
                // if($key == 0){
                    $flights = $finaldata['Flights'][0];
                    $Segments = $flights['Segments'];
                    $Date = $Segments[0]['Departure']['DepartureDateTime'];
                    $departureCode = $Segments[0]['Departure']['LocationCode'];
                    $arrivalCode = end($Segments)['Arrival']['LocationCode'];

                    $FareBasisCode = $flight['Fares'][0]['FareBases'][0]['content'];

                    $body = '<ns3:OTA_AirRulesRQ ReturnHostCommand="true" Version="2.3.0" xmlns:ns3="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                                <ns3:OriginDestinationInformation>
                                    <ns3:FlightSegment DepartureDateTime="'.date('m-d',strtotime($Date)).'">
                                    <ns3:DestinationLocation LocationCode="'.$arrivalCode.'"/>
                                    <ns3:MarketingCarrier Code="'.$MarketingAirline.'"/>
                                    <ns3:OriginLocation LocationCode="'.$departureCode.'"/>
                                    </ns3:FlightSegment>
                                </ns3:OriginDestinationInformation>
                                <ns3:RuleReqInfo>
                                    <ns3:FareBasis Code="'.$FareBasisCode.'"/>
                                </ns3:RuleReqInfo>
                            </ns3:OTA_AirRulesRQ>';
                        Storage::put('sabre/Soap/OTA_AirRulesRQ-'.$key.'.xml', $body);
                    /***********************************************\
                     **************Fetch Fare Rules*****************|
                    \***********************************************/
                    $binaryToken = self::SessionCreateRQ_Soap();
                    $soap_token_attr = '<wsse:BinarySecurityToken valueType="String" EncodingType="wsse:Base64Binary">' . $binaryToken . '</wsse:BinarySecurityToken>';
                    $soap_message = self::soap_request('OTA_AirRulesLLSRQ', $soap_token_attr, $body);
                    $response = self::soap_curl_action($soap_message);
                    self::SessionCloseRQ_Soap($binaryToken);
                    $response = self::prettyPrint($response);
                    Storage::put('sabre/Soap/OTA_AirRulesLLS-Res-'.$key.'.xml', $response);
                    /*************************OLD Response Rule*********************/
                    // $response = Storage::get('sabre/Soap/FareRulesRes1.xml');
                    /*************************End OLD Response*********************/
    
                    $jsonResponse = self::removeNamespaceFromXMLAndConvertToArray($response);
                    $jsonResponse = self::removeNamespaceFromXMLAndConvertToArray($response);
                    if(array_key_exists('DuplicateFareInfo', $jsonResponse['Body']['OTA_AirRulesRS'])){
                        $jsonResponse = self::DuplicateFareInfo($FareBasisCode,$binaryToken);   
                    }
                    $resp = self::parseFareRuleResponse($jsonResponse);
                    array_push($flightArray, $resp);
                // }
            }
        }

        $finalRule = [
            'status'=> '200',
            'Rules'=> $flightArray,
        ];
        return $finalRule;
    }
    public static function DuplicateFareInfo($FareBasisCode, $binaryToken){
        $body = '<ns3:OTA_AirRulesRQ ReturnHostCommand="true" Version="2.3.0" xmlns:ns3="http://webservices.sabre.com/sabreXML/2011/10" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <ns3:RuleReqInfo RPH="1"/>
                </ns3:OTA_AirRulesRQ>';
                    
        $soap_token_attr = '<wsse:BinarySecurityToken valueType="String" EncodingType="wsse:Base64Binary">' . $binaryToken . '</wsse:BinarySecurityToken>';
        $soap_message = self::soap_request('OTA_AirRulesLLSRQ', $soap_token_attr, $body);
        $response = self::soap_curl_action($soap_message);
        
        $response = self::prettyPrint($response);
        Storage::put('sabre/Soap/OTA_AirRulesLLS-Res-Dup.xml', $response);
        return self::removeNamespaceFromXMLAndConvertToArray($response);
    }
    public static function parseFareRuleResponse($jsonResponse){
        $jsonResponse = $jsonResponse['Body']['OTA_AirRulesRS'];
        if (array_key_exists('FareRuleInfo', $jsonResponse)){
            $FareRuleInfo = $jsonResponse['FareRuleInfo'];
            if (array_key_exists('Rules', $FareRuleInfo)){
                $Rules = $FareRuleInfo['Rules']['Paragraph'];
            }else{
                $Rules[0]['Text'] = 'Not found.....!';
            }

        }elseif(array_key_exists('DuplicateFareInfo', $jsonResponse)){
            
            $Rules[0] = $jsonResponse['DuplicateFareInfo'];
        }else{
            $Rules[0]['Text'] = 'Not found.....!';
        }
        
        return $Rules;
    }
    public static function soap_request($RQ, $security, $body)
    {
        $conversationId = Str::uuid();
        $request = '<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eb="http://www.ebxml.org/namespaces/messageHeader" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsd="http://www.w3.org/1999/XMLSchema">
                <soap-env:Header>
                    <eb:MessageHeader soap-env:mustUnderstand="1" eb:version="1.0.0">
                        <eb:From>
                            <eb:PartyId eb:type="urn:x12.org:IO5:01">from</eb:PartyId>
                        </eb:From>
                        <eb:To>
                            <eb:PartyId eb:type="urn:x12.org:IO5:01">ws</eb:PartyId>
                        </eb:To>
                        <eb:CPAId>' . env('S_GROUP') . '</eb:CPAId>
                        <eb:ConversationId>' . $conversationId . '</eb:ConversationId>
                        <eb:Service>' . $RQ . '</eb:Service>
                        <eb:Action>' . $RQ . '</eb:Action>
                        <eb:MessageData>
                            <eb:MessageId>1000-' . $conversationId . '</eb:MessageId>
                            <eb:Timestamp>' . now() . '</eb:Timestamp>
                        </eb:MessageData>
                    </eb:MessageHeader>
                    <wsse:Security xmlns:wsse="http://schemas.xmlsoap.org/ws/2002/12/secext">
                        <wsse:BinarySecurityToken valueType="String" EncodingType="wsse:Base64Binary">' . $security . '</wsse:BinarySecurityToken>
                        ' . $security . '
                    </wsse:Security>
                </soap-env:Header>
                <soap-env:Body>
                    ' . $body . '
                </soap-env:Body>
            </soap-env:Envelope>';
        Storage::put('sabre/Soap/'.$RQ.'-Req.xml', $request);
        return $request;
    }
    public static function soap_curl_action($soap_message)
    {
        $header = array(
            "Content-Type: text/xml"
        );
        $init = curl_init(self::$soap_link);
        curl_setopt($init, CURLOPT_POST, true);
        curl_setopt($init, CURLOPT_POSTFIELDS, $soap_message);
        curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($init, CURLOPT_ENCODING, '');
        curl_setopt($init, CURLOPT_MAXREDIRS, 10);
        curl_setopt($init, CURLOPT_TIMEOUT, 0);
        curl_setopt($init, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($init, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($init, CURLOPT_HTTPHEADER, $header);
        $return = curl_exec($init);
        //$status_code = curl_getinfo($init, CURLINFO_HTTP_CODE);
        curl_close($init);
        return $return;
    }
    public static function prettyPrint($result)
    {
        $dom = new \DOMDocument;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($result);
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
    public static function soap_credentials()
    {
        return '<wsse:UsernameToken>
                    <wsse:Username>' . env('S_USERID') . '</wsse:Username>
                    <wsse:Password>' . env('S_PASSWORD') . '</wsse:Password>
                    <Organization>' . env('S_GROUP') . '</Organization>
                    <Domain>' . env('S_DOMAIN') . '</Domain>
                </wsse:UsernameToken>';
    }
    public static function SessionCreateRQ_Soap()
    {
        $body = '<SessionCreateRQ>
                    ' . self::soap_pos() . '
                </SessionCreateRQ>';

        $soap_message = self::soap_request('SessionCreateRQ', self::soap_credentials(), $body);
        // Storage::put('sabre/' . auth()->id() . '/SessionCreateRQ.xml', $this->prettyPrint($soap_message));
        $response = self::soap_curl_action($soap_message);

        $response = self::prettyPrint($response);
        Storage::put('sabre/Soap/SessionCreateRS.xml', $response);
        $create = self::removeNamespaceFromXMLAndConvertToArray($response);
        $BinarySecurityToken = $create['Header']['Security']['BinarySecurityToken'];
        return $BinarySecurityToken;
    }
    public static function SessionCloseRQ_Soap($token)
    {
        $body = '<SessionCloseRQ>
                    ' . self::soap_pos() . '
                </SessionCloseRQ>';

        $soap_message = self::soap_request('SessionCloseRQ',$token, $body);
        // Storage::put('sabre/' . auth()->id() . '/SessionCloseRQ.xml', $this->prettyPrint($this->soap_message));
        $response = self::soap_curl_action($soap_message);

        $response = self::prettyPrint($response);
        Storage::put('sabre/Soap/SessionCloseRS.xml', $response);
        return $response;
    }
    public static function soap_pos()
    {
        return '<POS>
                    <Source PseudoCityCode="' . env('S_GROUP') . '" />
                </POS>';
    }
    public static function removeNamespaceFromXMLAndConvertToArray($xml)
    {
        $toRemove = ['soap-env', 'eb', 'wsse', 'stl19', 'or114'];
        // This is part of a regex I will use to remove the namespace declaration from string
        $nameSpaceDefRegEx = '(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?';
        // Cycle through each namespace and remove it from the XML string
        foreach ($toRemove as $remove) {
            // First remove the namespace from the opening of the tag
            $xml = str_replace('<' . $remove . ':', '<', $xml);
            // Now remove the namespace from the closing of the tag
            $xml = str_replace('</' . $remove . ':', '</', $xml);
            // This XML uses the name space with CommentText, so remove that too
            $pattern = "/xmlns:{$remove}{$nameSpaceDefRegEx}/";
            // Remove the actual namespace declaration using the Pattern
            $xml = preg_replace($pattern, '', $xml, 1);
        }
        // Return sanitized and cleaned up XML with no namespaces
        $cleanedXml = $xml;

        //Removing body tag and converting response to array
        $cleanedXml = new SimpleXMLElement($cleanedXml);
        // $cleanedXml = $cleanedXml->xpath('Body')[0];
        // $array = json_decode(json_encode((array)$cleanedXml), TRUE);
        $array = json_decode(str_replace('@', '', json_encode((array)$cleanedXml)), TRUE);
        return $array;
    }
}
