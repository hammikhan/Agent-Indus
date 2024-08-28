<?php

public static function voidBookingRequest($order){
    $order['pnrCode'] = 'HZFIEG';
    $ticketArray = "2146078980183";
    $authResp = self::sabre_auth();
    $access_token = json_decode($authResp, true);
    /*************************************************************** */
    $url = env('S_URL') . '/v1/trip/orders/cancelBooking';
    // $request = [
    //     "errorHandlingPolicy" => "HALT_ON_ERROR",
    //     "targetPcc" => env('S_GROUP'),
    //     "DesignatePrinter" => [
    //         "Printers" => [
    //             "InvoiceItinerary" => [
    //                 "LNIATA" => env('S_PRINTER')
    //             ],
    //             "Hardcopy" => [
    //                 "LNIATA" => env('S_PRINTER')
    //             ],
    //             "Ticket" => [
    //                 "LNIATA" => env('S_PRINTER'),
    //                 "CountryCode" => "PK"
    //             ]
    //         ]
    //     ],
    //     "confirmationId" => $order['pnrCode'],
    // ];
    $request = [
        "errorHandlingPolicy" => "HALT_ON_ERROR",
        "targetPcc" => env('S_GROUP'),
        "tickets" => [
            $ticketArray
        ],
        "confirmationId" => $order['pnrCode'],
    ];
    $headers = [
        'Content-Type: application/json',
        'Conversation-ID: 2021.01.DevStudio',
        'Authorization: Bearer ' . $access_token['access_token'],
    ];
    
    Storage::put('Sabre/Void/'.$order['pnrCode'].'-voidTicketRequest.json', json_encode($request, JSON_PRETTY_PRINT));
    $response = self::fetch($url, json_encode($request), $headers);
    Storage::put('Sabre/Void/'.$order['pnrCode'].'-voidTicketResponse.json', json_encode($response, JSON_PRETTY_PRINT));
    dd($response);
    /*************************************************************** */
    // $customerData = json_decode($order['customer_data'],true);
    // $ticketData = $customerData['ticketsData'];
    // $ticketArray = $ticketData[0]['TicketNumber'];

    $ticketArray = "2146078980183";
    $order['pnrCode'] = 'HZFIEG';
    
    // $request = [
    //     "errorHandlingPolicy" => "HALT_ON_ERROR",
    //     "targetPcc" => env('S_GROUP'),
    //     "tickets" => [
    //         $ticketArray
    //     ],
    //     "confirmationId" => $order['pnrCode'],
    // ];
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
                    "LNIATA" => env('S_PRINTER'),
                    "CountryCode" => "PK"
                ]
            ]
        ],
        "confirmationId" => $order['pnrCode'],
    ];
    
    
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
    
    dd($response);
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

public static function fetch($url, $params, $headers, $method = 'POST')
    {
        $curl   = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYPEER => TRUE,
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_HTTPHEADER     => $headers,
            // CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30000,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => $params,
            CURLOPT_COOKIE         => '',
            // CURLOPT_CAINFO         => base_path('cacert_letest.pem')
        ]);

        $resp       = curl_exec($curl);
        if (curl_errno($curl)) {
            Log::info('Error in curl call');
            Log::info($resp);
            $error_msg = curl_error($curl);
            Log::error($error_msg);
        }
        // dd($resp);
        $response   = json_decode($resp, true);

        curl_close($curl);
        return $response;
    }