<?php

namespace App\Http\Traits;

use App\Models\AirlineDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ApiOffer;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait SabreTrait
{
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
    public static function search2($requestData)
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
        $parserResponse = self::oneWayResponse2(json_encode($apiResponse2), $key, $requestData);
        
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
            $NameNumber = $nameNo . ".1";
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

            $NameNumber = $value['passenger_type'] != 'INF' ?  $nameNo . ".1" : $countINF . ".1";
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
                ]
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
                    "SSR_Code" => "INFT"
                ];
                array_push($reqService, $service3);
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
                        "Option" => true
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
            // $res = Storage::get('Sabre/PNR/2024-05-13-15-02-11-000000PnrResponse.json');
            // $response = json_decode($res, true);
        // =========== End Old Response from storage=============\\
        
        
        if (array_key_exists('CreatePassengerNameRecordRS', $response)) {
            $CreatePassengerNameRecordRS = $response['CreatePassengerNameRecordRS'];
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

        } elseif (array_key_exists('errorCode', $response)) {
            return ['status' => '400', 'response' => $res];
        }
    }
    public static function issueTicket($order){
        $customer_data = json_decode($order['customer_data'],true);

        $total_passenger = count($customer_data['passengers']);
        $agent = auth('admin')->user();
        $ReceivedFrom = $agent->first_name.' '.$agent->last_name;

        $PriceQuote = [];
        for($i=1; $i<=$total_passenger; $i++ ){
            $data = [
                "Record" => [
                        [
                            "Number" => $i,
                            "Reissue" => false
                        ]
                    ]
            ];
            array_push($PriceQuote, $data);
        }

        
        $ticketing =  [
            [
                "PricingQualifiers" => [
                    "PriceQuote" => $PriceQuote
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
                $departure = $final_data['LowFareSearch'][0]['Segments'][0]['Departure']['LocationCode'];

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
                ]
            ]
        ];
        
        if (env('S_GROUP')) {
            $requestForCurl["AirTicketRQ"]["targetCity"] = env('S_GROUP');
        }
        
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
        // $res = Storage::get('Sabre/Ticket/2024-02-28-14-58-09-000000TicketResponse2.json');
        // $res = Storage::get('Sabre/Errors/2024-02-29-11-52-05-000000PnrResponse2.json');
        // =========== End Old Response from storage=============\\

        $response = json_decode($res,true);

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

        /***********************************************\
         **************Fetch PNR API call***************|
        \***********************************************/
        $requestJson = json_encode(
            ['confirmationId' => $order['pnrCode'],
        ]);
        $res = self::curl_action1($type,$url,$requestJson,$key,$apiToken);
        $res2 = json_decode($res,true);
        Storage::put('Sabre/Fetch/'.$order['pnrCode'].'-fetchPNRResponse.json', json_encode($res2, JSON_PRETTY_PRINT));
        /*************************OLD Response FETCH*********************/
            // $res = Storage::get('Sabre/Fetch/HQWJEP-fetchPNRResponse.json');
        // =========== End Old Response from storage=============\\

        $response = json_decode($res,true);
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
                $flights = $response['flights'];
                foreach($flights as $key => $flight){
                    $airline[$key]['pnrStatus'] = $flight['flightStatusName'];
                    $airline[$key]['airlineCode'] = $flight['airlineCode'];
                    $airline[$key]['airlinePnr'] = $flight['confirmationId'];
                    $airline[$key]['departureDate'] = $flight['departureDate'];
                    $airline[$key]['departureTime'] = $flight['departureTime'];
                }
            }else{
                $airline[0]['pnrStatus'] = 'Cancelled';
            }
            if (array_key_exists('flightTickets', $response)) {
                $flightTickets = $response['flightTickets'];
                $ticketData = array();
                
                foreach($flightTickets as $tktKey => $flightTKT){
                    if($flightTKT['ticketStatusName'] == 'Issued'){
                        $ticketStatusName = 'Ticketed';
                    }else{
                        $ticketStatusName = $flightTKT['ticketStatusName'];
                    }
                    $ticketStatus = $ticketStatusName;
                    if (array_key_exists('travelers', $response)) {
                        $travelers = $response['travelers'];
                        $ticketData[$tktKey]['type'] = $travelers[$tktKey]['type'];
                        $ticketData[$tktKey]['passengerCode'] = $travelers[$tktKey]['passengerCode'];
                        $ticketData[$tktKey]['name'] = $travelers[$tktKey]['givenName'];
                        $ticketData[$tktKey]['sur_name'] = $travelers[$tktKey]['surname'];
                        $ticketData[$tktKey]['TicketNumber'] = $flightTKT['number'];
                        $ticketData[$tktKey]['TicketStatus'] = $flightTKT['ticketStatusName'];
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
                'ticketData'=> $ticketData,
                'services' => $services,
                'airline' => $airline,
                'msg' => json_encode($response)
            ];
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
        $customerData = json_decode($order['customer_data'],true);
        $ticketData = $customerData['ticketsData'];
        $ticketArray = $ticketData[0]['TicketNumber'];
        
        // $request = [
        //     "errorHandlingPolicy" => "HALT_ON_ERROR",
        //     "targetPcc" => env('S_GROUP'),
        //     "confirmationId" => $order['pnrCode'],
        // ];
        /*************************************************** */
        $request = [
            "errorHandlingPolicy" => "HALT_ON_ERROR",
            "targetPcc" => env('S_GROUP'),
            "DesignatePrinter" => [
                "Printers" => [
                    "InvoiceItinerary" => [
                        "LNIATA" => env('S_PRINTER')
                    ],
                    "Hardcopy" => [
                        "LNIATA" => env('S_PRINTER')
                    ],
                    "Ticket" => [
                        "CountryCode" => "PK"
                    ]
                ]
            ],
            "confirmationId" => $order['pnrCode']
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
    public static function oneWayResponse2($res, $key, $request)
    {
        
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

        $ExcludeAirlines = ['XY'];
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
                            'Taxes' => (int) $PassengerFare['Taxes']['TotalTax']['Amount'],
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
                    $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'][0]['BaseFare'] = (int) $ItinTotalFare['EquivFare']['Amount'];
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
                                        'Taxes' => (int) $PassengerFare['Taxes']['TotalTax']['Amount'],
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
                            })->toArray();
                        $finalData[$itnIndex]['Flights'][$flightIndex]['Fares'] = array_merge($finalData[$itnIndex]['Flights'][$flightIndex]['Fares'],$additionalbrandedFares);
                    }
                }
                $finalData[$itnIndex]['api'] = "Sabre";
                $finalData[$itnIndex]['MarketingAirline']['FareRules'] = "NA";

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
}
