<?php

namespace App\Http\Traits;

use App\Models\PricingEngineTravelAgent;
use App\Models\Provider;
use Illuminate\Support\Facades\Auth;


trait PricingEngineTrait
{
    public static function applyPricingEngine($apiResponse, $request)
    {
        $provider = Provider::where('identifier', 'sabre')->first();
        $marketingAirline = $apiResponse->MarketingAirline['Airline'];

        $pricingEngine = PricingEngineTravelAgent::where('api_id', $provider->id)
            ->where('pricing_group_id', auth('admin')->user()->agency->pricing_group_id)
            ->where('airline', $marketingAirline)
            ->where('status', PricingEngineTravelAgent::$statusCast['Active'])
            ->get();

        if ($pricingEngine->isEmpty()) {
            $pricingEngine = PricingEngineTravelAgent::where('api_id', $provider->id)
                ->where('pricing_group_id', auth('admin')->user()->agency->pricing_group_id)
                ->where('airline', '')
                ->where('status', PricingEngineTravelAgent::$statusCast['Active'])
                ->get();
        }
        $getType = gettype($apiResponse);
        if ($getType == 'object') {
            $pricingEngineArray = $pricingEngine->toArray();
            // dd($marketingAirline,$pricingEngineArray);
            if (isset($apiResponse->Flights) && is_array($apiResponse->Flights)) {
                
                foreach ($apiResponse->Flights as &$flight) {
                    if (is_array($flight)) {
                        $flight = (object)$flight;
                    }
                    
                    if (isset($flight->Segments) && is_array($flight->Segments) && count($flight->Segments) > 0) {
                        // $departureLocationCode = $flight->Segments[0]['Departure']['LocationCode'] ?? '';
                        // $arrivalLocationCode = $flight->Segments[0]['Arrival']['LocationCode'] ?? '';
                        $departureLocationCode = $apiResponse->Flights[0]->Segments[0]['Departure']['LocationCode'] ?? '';
                        $arrivalLocationCode = $apiResponse->Flights[0]->Segments[0]['Arrival']['LocationCode'] ?? '';
                        
                        if (isset($flight->Fares) && is_array($flight->Fares)) {
                            foreach ($flight->Fares as &$fare) {
                                if (is_array($fare)) {
                                    $fare = (object)$fare;
                                }
                                
                                $appliedRule = null;
                                foreach ($pricingEngineArray as $pricingRule) {
                                    if (!empty($pricingRule['airline']) && $pricingRule['airline'] === $marketingAirline) {
                                        if ($pricingRule['data']['isAllOrigins'] == 0 || $pricingRule['data']['isAllDestinations'] == 0) {
                                            $origins = $pricingRule['data']['origins'] ?? [];
                                            $destinations = $pricingRule['data']['destinations'] ?? [];
                                            if ($pricingRule['data']['isAllOrigins'] != 1) {
                                                if (is_array($origins) && in_array($departureLocationCode, $origins)) {
                                                    $appliedRule = $pricingRule;
                                                    break;
                                                }
                                            } elseif ($pricingRule['data']['isAllDestinations'] != 1) {
                                                if (is_array($destinations) && in_array($arrivalLocationCode, $destinations)) {
                                                    $appliedRule = $pricingRule;
                                                    break;
                                                }
                                            }
                                        }
                                        else {
                                            $appliedRule = $pricingRule;
                                        }
                                        
                                    }
                                }
                                
                                
                                if (!$appliedRule) {
                                    
                                    foreach ($pricingEngineArray as $pricingRule) {
                                        if (empty($pricingRule['airline'])) {
                                            $appliedRule = $pricingRule;
                                            break;
                                        }
                                    }
                                }
                                if ($appliedRule) {
                                    if($appliedRule['rule'] == 'Mark Up'){
                                        $totalAmountToAdd = 0;
                                        $amountToAdd = 0;
                                        $percent = 0;
    
                                        if (isset($fare->PassengerFares) && is_array($fare->PassengerFares)) {
                                            foreach ($fare->PassengerFares as &$passengerFare) {
                                                if (is_array($passengerFare)) {
                                                    $passengerFare = (object)$passengerFare;
                                                }
                                                if ($passengerFare->PaxType != 'Infant') {

                                                    if ($appliedRule['type'] === 'Fixed') {
                                                        $amountToAdd = (float)$appliedRule['amount'];
                                                        $percent = 0;
                                                    } elseif ($appliedRule['type'] === 'Percentage' && property_exists($fare, 'BasePrice')) {
                                                        $amountToAdd = ($passengerFare->BasePrice * (float)$appliedRule['amount']) / 100;
                                                        $percent = $appliedRule['amount'];
                                                    }

                                                    $totalpassFare = $amountToAdd * $passengerFare->Quantity;
                                                    $passengerFare->Markup = $amountToAdd;
                                                    $passengerFare->Type = $appliedRule['type'];
                                                    $passengerFare->Percentage = $percent;
                                                    $passengerFare->TotalPrice += $amountToAdd;
                                                    $totalAmountToAdd += $totalpassFare;
                                                }
                                            }
                                        }
                                        $fare->Markup = $amountToAdd;
                                        $fare->Type = $appliedRule['type'];
                                        $fare->Percentage = $percent;
                                        $fare->Discount = 0;
    
                                        $fare->BillablePrice += $totalAmountToAdd;
                                    } elseif($appliedRule['rule'] == 'Discount'){
                                        $totalAmountToDeduct = 0;
                                        $amountToDeduct = 0;
                                        $percent = 0;
                                        
                                        $segmentCount = count($flight->Segments);
                                        if($segmentCount > 1){
                                            foreach ($flight->Segments as $segment) {
                                                if (is_array($segment)) {
                                                    $segment = (object)$segment;
                                                }
                                                if (isset($segment->MarketingAirline)) {
                                                    $marketingAirlineObj = $segment->MarketingAirline;
                                                    $marketingAirline = '';
                                                    if (is_array($marketingAirlineObj)) {
                                                        $marketingAirline = $marketingAirlineObj['Code'] ?? '';
                                                    } elseif (is_object($marketingAirlineObj)) {
                                                        $marketingAirline = $marketingAirlineObj->Code ?? '';
                                                    }
                                                    if ($marketingAirline) {
                                                        $marketingAirlines[] = $marketingAirline;
                                                    }
                                                }
                                            }

                                            $allEqual = array_filter($marketingAirlines, function($airline) use ($marketingAirline) {
                                                return $airline !== $marketingAirline;
                                            });
                                            
                                            if (empty($allEqual)) {
                                                if (isset($fare->PassengerFares) && is_array($fare->PassengerFares)) {
                                                    foreach ($fare->PassengerFares as &$passengerFare) {
                                                        if (is_array($passengerFare)) {
                                                            $passengerFare = (object)$passengerFare;
                                                        }
                                                        if ($passengerFare->PaxType != 'Infant') {
                                                            if ($appliedRule['type'] === 'Fixed') {
                                                                $amountToDeduct = (float)$appliedRule['amount'];
                                                            } elseif ($appliedRule['type'] === 'Percentage' && property_exists($fare, 'BasePrice')) {
                                                                $amountToDeduct = ($passengerFare->BasePrice * (float)$appliedRule['amount']) / 100;
                                                                $percent = $appliedRule['amount'];
                                                            }
                                                            $totalpassFare = $amountToDeduct * $passengerFare->Quantity;
                                                            $passengerFare->Discount = $amountToDeduct;
                                                            $passengerFare->Type = $appliedRule['type'];
                                                            $passengerFare->Percentage = $percent;
                                                            $passengerFare->TotalPrice -= $amountToDeduct;
                                                            $totalAmountToDeduct -= $totalpassFare;
                                                        }
                                                    }
                                                }
                                                $fare->Discount = $amountToDeduct;
                                                $fare->Type = $appliedRule['type'];
                                                $fare->RuleType = $appliedRule['rule'];
                                                $fare->Percentage = $percent;
                                                $fare->Markup = 0;
                                                $fare->BillablePrice += $totalAmountToDeduct;
                                            }
                                        }else{
                                            if (isset($fare->PassengerFares) && is_array($fare->PassengerFares)) {
                                                foreach ($fare->PassengerFares as &$passengerFare) {
                                                    if (is_array($passengerFare)) {
                                                        $passengerFare = (object)$passengerFare;
                                                    }
                                                    if ($passengerFare->PaxType != 'Infant') {
                                                        if ($appliedRule['type'] === 'Fixed') {
                                                            $amountToDeduct = (float)$appliedRule['amount'];
                                                        } elseif ($appliedRule['type'] === 'Percentage' && property_exists($fare, 'BasePrice')) {
                                                            $amountToDeduct = ($passengerFare->BasePrice * (float)$appliedRule['amount']) / 100;
                                                            $percent = $appliedRule['amount'];
                                                        }
                                                        $totalpassFare = $amountToDeduct * $passengerFare->Quantity;
                                                        $passengerFare->Discount = $amountToDeduct;
                                                        $passengerFare->Type = $appliedRule['type'];
                                                        $passengerFare->RuleType = $appliedRule['rule'];
                                                        $passengerFare->Percentage = $percent;
                                                        $passengerFare->TotalPrice -= $amountToDeduct;
                                                        $totalAmountToDeduct -= $totalpassFare;
                                                    }
                                                }
                                            }
                                            $fare->Discount = $amountToDeduct;
                                            $fare->Type = $appliedRule['type'];
                                            $fare->RuleType = $appliedRule['rule'];
                                            $fare->Percentage = $percent;
                                            $fare->Markup = 0;
                                            $fare->BillablePrice += $totalAmountToDeduct;
                                        }
                                        

                                    }
                                }
                            }
                        }
                    }
                }
            }
            // dd($apiResponse);
            return $apiResponse;
        }

        return $pricingEngine;
    }
}
