<?php

namespace App\Http\Traits;

use App\Models\PricingEngineTravelAgent;
use Illuminate\Support\Facades\Auth;


trait PricingEngineTrait
{
    public static function applyPricingEngine($apiResponse, $origin, $destination, $totalPassenger, $api = null)
    {
        // return $totalPassenger;
        // dd($apiResponse);

        // $travelagent_id = Auth::guard('admin')->user()->id;
        $travelagent_id = 9;
        $pricingEngine = PricingEngineTravelAgent::where('api_id', $api->id)
            ->where('travel_agent_id', $travelagent_id)
            ->where('status', PricingEngineTravelAgent::$statusCast['Active'])
            ->where('rule', '!=', ['API Ranking', 'Allowed Airlines'])
            ->get();
                        
        // return $pricingEngine;
        $getType = gettype($apiResponse);
        if ($getType == 'object') {

            $apiResponse->Fares->OriginalPrice = $apiResponse->Fares->TotalPrice;
            $grandTotal = $apiResponse->Fares->TotalPrice;
            $airline = $apiResponse->MarketingAirline->Airline;

            $baseFareAdult = 0;
            $baseFareChild = 0;
            $baseFareInfant = 0;
            if (property_exists($apiResponse->Fares->fareBreakDown, 'ADT')) {
                $baseFareAdult = $apiResponse->Fares->fareBreakDown->ADT->BaseFare;
            }
            if (property_exists($apiResponse->Fares->fareBreakDown, 'CNN')) {
                $baseFareChild =  $apiResponse->Fares->fareBreakDown->CNN->BaseFare;
            }
            if (property_exists($apiResponse->Fares->fareBreakDown, 'INF')) {
                $baseFareInfant = $apiResponse->Fares->fareBreakDown->INF->BaseFare;
            }

            foreach ($pricingEngine as $engine) {
                $piceData = $engine->data;
                // return $piceData;
                // $api_name = apiNameById($engine->api_id)->name;
                // dd($api->identifierName );
                // if($api->identifierName == $apiResponse->ApiIdentifier){
                if ($engine->api_id == $api->id) {
                    if ($piceData['type'] == 'Fixed') {
                        if ($engine->rule == 'Mark Up') {
                            $markup = $piceData['amount'];
                            $grandTotal += $markup * $totalPassenger;

                            $baseFareAdult += $markup;
                            $baseFareChild += $markup;
                            $baseFareInfant += $markup;
                        } else if ($engine->rule == 'Discount') {
                            $discount = $piceData['amount'] * $totalPassenger;
                            $grandTotal -= $discount;

                            $baseFareAdult -= $discount;
                            $baseFareChild -= $discount;
                            $baseFareInfant -= $discount;
                        } else if ($engine->rule == 'Route Mark Up') {
                            if ($piceData['origin'] == $origin && $piceData['destination'] == $destination) {

                                if (isset($piceData['airline']) && @$piceData['airline'] != "") {
                                    if (@$piceData['airline'] == $airline) {
                                        $routeMarkup = $piceData['amount'];
                                        $grandTotal += $routeMarkup * $totalPassenger;

                                        $baseFareAdult += $routeMarkup;
                                        $baseFareChild += $routeMarkup;
                                        $baseFareInfant += $routeMarkup;
                                    }
                                } else {
                                    $routeMarkup = $piceData['amount'];
                                    $grandTotal += $routeMarkup * $totalPassenger;

                                    $baseFareAdult += $routeMarkup;
                                    $baseFareChild += $routeMarkup;
                                    $baseFareInfant += $routeMarkup;
                                }
                            }
                        } else if ($engine->rule == 'Route Discount') {
                            if ($piceData['origin'] == $origin && $piceData['destination'] == $destination) {
                                if (isset($piceData['airline']) && @$piceData['airline'] != "") {
                                    if (@$piceData['airline'] == $airline) {
                                        $routeDiscount = $piceData['amount'];
                                        $grandTotal -= $routeDiscount * $totalPassenger;

                                        $baseFareAdult -= $routeDiscount;
                                        $baseFareChild -= $routeDiscount;
                                        $baseFareInfant -= $routeDiscount;
                                    }
                                } else {
                                    $routeDiscount = $piceData['amount'];
                                    $grandTotal -= $routeDiscount * $totalPassenger;

                                    $baseFareAdult -= $routeDiscount;
                                    $baseFareChild -= $routeDiscount;
                                    $baseFareInfant -= $routeDiscount;
                                }
                            }
                        }
                        // else if($engine->rule == 'API Ranking'){

                        // }
                    } else {
                        if ($engine->rule == 'Mark Up') {
                            $markup = ($piceData['amount'] / 100) * $grandTotal;
                            $grandTotal += $markup * $totalPassenger;

                            $baseFareAdult += $markup;
                            $baseFareChild += $markup;
                            $baseFareInfant += $markup;
                        } else if ($engine->rule == 'Discount') {
                            $discount = ($piceData['amount'] / 100) * $grandTotal;
                            $grandTotal -= $discount * $totalPassenger;

                            $baseFareAdult -= $discount;
                            $baseFareChild -= $discount;
                            $baseFareInfant -= $discount;
                        } else if ($engine->rule == 'Route Mark Up') {
                            if ($piceData['origin'] == $origin && $piceData['destination'] == $destination) {
                                if (isset($piceData['airline']) && @$piceData['airline'] != "") {
                                    if (@$piceData['airline'] == $airline) {
                                        $routeMarkup = ($piceData['amount'] / 100) * $grandTotal;
                                        $grandTotal += $routeMarkup * $totalPassenger;

                                        $baseFareAdult += $routeMarkup;
                                        $baseFareChild += $routeMarkup;
                                        $baseFareInfant += $routeMarkup;
                                    }
                                } else {
                                    $routeMarkup = ($piceData['amount'] / 100) * $grandTotal;
                                    $grandTotal += $routeMarkup * $totalPassenger;

                                    $baseFareAdult += $routeMarkup;
                                    $baseFareChild += $routeMarkup;
                                    $baseFareInfant += $routeMarkup;
                                }
                            }
                        } else if ($engine->rule == 'Route Discount') {
                            if ($piceData['origin'] == $origin && $piceData['destination'] == $destination) {
                                if (isset($piceData['airline']) && @$piceData['airline'] != "") {
                                    if (@$piceData['airline'] == $airline) {
                                        $routeDiscount = ($piceData['amount'] / 100) * $grandTotal;
                                        $grandTotal -= $routeDiscount * $totalPassenger;

                                        $baseFareAdult -= $routeDiscount;
                                        $baseFareChild -= $routeDiscount;
                                        $baseFareInfant -= $routeDiscount;
                                    }
                                } else {
                                    $routeDiscount = ($piceData['amount'] / 100) * $grandTotal;
                                    $grandTotal -= $routeDiscount * $totalPassenger;

                                    $baseFareAdult -= $routeDiscount;
                                    $baseFareChild -= $routeDiscount;
                                    $baseFareInfant -= $routeDiscount;
                                }
                            }
                        }
                    }
                }
            }

            $apiResponse->Fares->TotalPrice = $grandTotal;
            if (property_exists($apiResponse->Fares->fareBreakDown, 'ADT')) {
                $apiResponse->Fares->fareBreakDown->ADT->BaseFare =  $baseFareAdult;
                $apiResponse->Fares->fareBreakDown->ADT->TotalFare =  ($baseFareAdult + $apiResponse->Fares->fareBreakDown->ADT->TotalTax);
            }
            if (property_exists($apiResponse->Fares->fareBreakDown, 'CNN')) {
                $apiResponse->Fares->fareBreakDown->CNN->BaseFare =  $baseFareChild;
                $apiResponse->Fares->fareBreakDown->CNN->TotalFare =  $baseFareChild + $apiResponse->Fares->fareBreakDown->CNN->TotalTax;
            }
            if (property_exists($apiResponse->Fares->fareBreakDown, 'INF')) {
                $apiResponse->Fares->fareBreakDown->INF->BaseFare = $baseFareInfant;
                $apiResponse->Fares->fareBreakDown->INF->TotalFare =  $baseFareInfant + $apiResponse->Fares->fareBreakDown->INF->TotalTax;
            }
            return $apiResponse;
        } else {
            $respWithMark = array();
            foreach ($apiResponse as $apiRes) {
                $airline = $apiRes->MarketingAirline->Airline;
                $apiRes->Fares->OriginalPrice = $apiRes->Fares->TotalPrice;
                $grandTotal = $apiRes->Fares->TotalPrice;
                $apiRes->ApiIdentifier = @$api->identifierName;

                //start
                $baseFareAdult = 0;
                $baseFareChild = 0;
                $baseFareInfant = 0;
                if (property_exists($apiRes->Fares->fareBreakDown, 'ADT')) {
                    $baseFareAdult = $apiRes->Fares->fareBreakDown->ADT->BaseFare;
                }
                if (property_exists($apiRes->Fares->fareBreakDown, 'CNN')) {
                    $baseFareChild =  $apiRes->Fares->fareBreakDown->CNN->BaseFare;
                }
                if (property_exists($apiRes->Fares->fareBreakDown, 'INF')) {
                    $baseFareInfant = $apiRes->Fares->fareBreakDown->INF->BaseFare;
                }
                //end

                foreach ($pricingEngine as $engine) {
                    $piceData = $engine->data;
                    // return $piceData['origins'];
                    // $api_name = apiNameById($engine->api_id)->name;
                    if ($api->identifierName == $apiRes->ApiIdentifier) {
                        if (@$piceData->type == 'Fixed') {
                            if ($engine->rule == 'Mark Up') {
                                if (@$piceData->isAllOrigins && @$piceData->isAllDestinations) {
                                    $markup = $piceData->amount;
                                    $grandTotal += $markup * $totalPassenger;

                                    $baseFareAdult += $markup;
                                    $baseFareChild += $markup;
                                    $baseFareInfant += $markup;
                                } 
                                else if (in_array($origin, $piceData->origins) && in_array($destination, $piceData->destinations)) {
                                    $markup = $piceData->amount;
                                    $grandTotal += $markup * $totalPassenger;

                                    $baseFareAdult += $markup;
                                    $baseFareChild += $markup;
                                    $baseFareInfant += $markup;
                                }
                                else if (@$piceData->isAllOrigins && in_array($destination, $piceData->destinations)) {
                                    $markup = $piceData->amount;
                                    $grandTotal += $markup * $totalPassenger;

                                    $baseFareAdult += $markup;
                                    $baseFareChild += $markup;
                                    $baseFareInfant += $markup;
                                }
                                else if (in_array($origin, $piceData->origins) && @$piceData->isAllDestinations) {
                                    $markup = $piceData->amount;
                                    $grandTotal += $markup * $totalPassenger;

                                    $baseFareAdult += $markup;
                                    $baseFareChild += $markup;
                                    $baseFareInfant += $markup;
                                }
                            } else if ($engine->rule == 'Discount') {
                                if (@$piceData->isAllOrigins && @$piceData->isAllDestinations) {
                                    $discount = $piceData->amount;
                                    $grandTotal -= $discount * $totalPassenger;

                                    $baseFareAdult -= $discount;
                                    $baseFareChild -= $discount;
                                    $baseFareInfant -= $discount;
                                }
                                else if (in_array($origin, $piceData->origins) && in_array($destination, $piceData->destinations)) {
                                    $discount = $piceData->amount;
                                    $grandTotal -= $discount * $totalPassenger;

                                    $baseFareAdult -= $discount;
                                    $baseFareChild -= $discount;
                                    $baseFareInfant -= $discount;
                                }
                                else if (@$piceData->isAllOrigins && in_array($destination, $piceData->destinations)) {
                                    $discount = $piceData->amount;
                                    $grandTotal -= $discount * $totalPassenger;

                                    $baseFareAdult -= $discount;
                                    $baseFareChild -= $discount;
                                    $baseFareInfant -= $discount;
                                } else if (in_array($origin, $piceData->origins) && @$piceData->isAllDestinations) {
                                    $discount = $piceData->amount;
                                    $grandTotal -= $discount * $totalPassenger;

                                    $baseFareAdult -= $discount;
                                    $baseFareChild -= $discount;
                                    $baseFareInfant -= $discount;
                                }
                                
                            } else if ($engine->rule == 'Route Mark Up') {
                                if ($piceData->origin == $origin && $piceData->destination == $destination) {
                                    if (isset($piceData->airline) && @$piceData->airline != "") {
                                        if (@$piceData->airline == $airline) {
                                            $routeMarkup = $piceData->amount;
                                            $grandTotal += $routeMarkup * $totalPassenger;
                                            $baseFareAdult += $routeMarkup;
                                            $baseFareChild += $routeMarkup;
                                            $baseFareInfant += $routeMarkup;
                                        }
                                    } else {
                                        $routeMarkup = $piceData->amount;
                                        $grandTotal += $routeMarkup * $totalPassenger;
                                        $baseFareAdult += $routeMarkup;
                                        $baseFareChild += $routeMarkup;
                                        $baseFareInfant += $routeMarkup;
                                    }
                                }
                            } else if ($engine->rule == 'Route Discount') {
                                if ($piceData->origin == $origin && $piceData->destination == $destination) {
                                    if (isset($piceData->airline) && @$piceData->airline != "") {
                                        if (@$piceData->airline == $airline) {
                                            $routeDiscount = $piceData->amount;
                                            $grandTotal -= $routeDiscount * $totalPassenger;

                                            $baseFareAdult -= $routeDiscount;
                                            $baseFareChild -= $routeDiscount;
                                            $baseFareInfant -= $routeDiscount;
                                        }
                                    } else {
                                        $routeDiscount = $piceData->amount;
                                        $grandTotal -= $routeDiscount * $totalPassenger;

                                        $baseFareAdult -= $routeDiscount;
                                        $baseFareChild -= $routeDiscount;
                                        $baseFareInfant -= $routeDiscount;
                                    }
                                }
                            }
                            // else if($engine->rule == 'API Ranking'){

                            // }
                        } else {
                            if ($engine->rule == 'Mark Up') {
                                if (@$piceData->isAllOrigins && @$piceData->isAllDestinations) {
                                    $markup = ($piceData->amount / 100) * $grandTotal;
                                    $grandTotal += $markup * $totalPassenger;
                                    $baseFareAdult += $markup;
                                    $baseFareChild += $markup;
                                    $baseFareInfant += $markup;
                                }
                                else if (in_array($origin, $piceData['origins']) && in_array($destination, $piceData['destinations'])) {
                                    $markup = ($piceData['amount'] / 100) * $grandTotal;
                                    $grandTotal += $markup * $totalPassenger;
                                    $baseFareAdult += $markup;
                                    $baseFareChild += $markup;
                                    $baseFareInfant += $markup;
                                }
                                else if (@$piceData['isAllOrigins'] && in_array($destination, $piceData['destinations'])) {
                                    $markup = ($piceData['amount'] / 100) * $grandTotal;
                                    $grandTotal += $markup * $totalPassenger;
                                    $baseFareAdult += $markup;
                                    $baseFareChild += $markup;
                                    $baseFareInfant += $markup;
                                }
                                else if (in_array($origin, $piceData['origins']) && @$piceData['isAllDestinations']) {
                                    $markup = ($piceData['amount'] / 100) * $grandTotal;
                                    $grandTotal += $markup * $totalPassenger;
                                    $baseFareAdult += $markup;
                                    $baseFareChild += $markup;
                                    $baseFareInfant += $markup;
                                }
                                
                                
                            } else if ($engine->rule == 'Discount') {
                                if (@$piceData['isAllOrigins'] && @$piceData['isAllDestinations']) {
                                    $discount = ($piceData['amount'] / 100) * $grandTotal;
                                    $grandTotal -= $discount * $totalPassenger;

                                    $baseFareAdult -= $discount;
                                    $baseFareChild -= $discount;
                                    $baseFareInfant -= $discount;
                                } 
                                else if (in_array($origin, $piceData['origins']) && in_array($destination, $piceData['destinations'])) {
                                    $discount = ($piceData['amount'] / 100) * $grandTotal;
                                    $grandTotal -= $discount * $totalPassenger;

                                    $baseFareAdult -= $discount;
                                    $baseFareChild -= $discount;
                                    $baseFareInfant -= $discount;
                                }
                                else if (@$piceData['isAllOrigins'] && in_array($destination, $piceData['destinations'])) {
                                    $discount = ($piceData['amount'] / 100) * $grandTotal;
                                    $grandTotal -= $discount * $totalPassenger;

                                    $baseFareAdult -= $discount;
                                    $baseFareChild -= $discount;
                                    $baseFareInfant -= $discount;
                                }
                                else if (in_array($origin, $piceData['origins']) && @$piceData['isAllDestinations']) {
                                    $discount = ($piceData['amount'] / 100) * $grandTotal;
                                    $grandTotal -= $discount * $totalPassenger;

                                    $baseFareAdult -= $discount;
                                    $baseFareChild -= $discount;
                                    $baseFareInfant -= $discount;
                                }
                                
                                
                            } else if ($engine->rule == 'Route Mark Up') {
                                if ($piceData['origin'] == $origin && $piceData['destination'] == $destination) {
                                    if (isset($piceData['airline']) && @$piceData['airline'] != "") {
                                        if (@$piceData['airline'] == $airline) {
                                            $routeMarkup = ($piceData['amount'] / 100) * $grandTotal;
                                            $grandTotal += $routeMarkup * $totalPassenger;
                                            $baseFareAdult += $routeMarkup;
                                            $baseFareChild += $routeMarkup;
                                            $baseFareInfant += $routeMarkup;
                                        }
                                    } else {
                                        $routeMarkup = ($piceData['amount'] / 100) * $grandTotal;
                                        $grandTotal += $routeMarkup * $totalPassenger;
                                        $baseFareAdult += $routeMarkup;
                                        $baseFareChild += $routeMarkup;
                                        $baseFareInfant += $routeMarkup;
                                    }
                                }
                            } else if ($engine->rule == 'Route Discount') {
                                if ($piceData['origin'] == $origin && $piceData['destination'] == $destination) {
                                    if (isset($piceData['airline']) && @$piceData['airline'] != "") {
                                        if (@$piceData['airline'] == $airline) {
                                            $routeDiscount = ($piceData['amount'] / 100) * $grandTotal;
                                            $grandTotal -= $routeDiscount * $totalPassenger;
                                            $baseFareAdult -= $routeDiscount;
                                            $baseFareChild -= $routeDiscount;
                                            $baseFareInfant -= $routeDiscount;
                                        }
                                    } else {
                                        $routeDiscount = ($piceData['amount'] / 100) * $grandTotal;
                                        $grandTotal -= $routeDiscount * $totalPassenger;
                                        $baseFareAdult -= $routeDiscount;
                                        $baseFareChild -= $routeDiscount;
                                        $baseFareInfant -= $routeDiscount;
                                    }
                                }
                            }
                            // if($engine->rule == 'API Ranking'){

                            // }

                        }
                    }
                }
                if (property_exists($apiRes->Fares->fareBreakDown, 'ADT')) {
                    $apiRes->Fares->fareBreakDown->ADT->BaseFare = $baseFareAdult;
                    $apiRes->Fares->fareBreakDown->ADT->TotalFare = ($baseFareAdult + $apiRes->Fares->fareBreakDown->ADT->TotalTax);
                }
                if (property_exists($apiRes->Fares->fareBreakDown, 'CNN')) {
                    $apiRes->Fares->fareBreakDown->CNN->BaseFare = $baseFareChild;
                    $apiRes->Fares->fareBreakDown->CNN->TotalFare = ($baseFareChild + $apiRes->Fares->fareBreakDown->CNN->TotalTax);
                }
                if (property_exists($apiRes->Fares->fareBreakDown, 'INF')) {
                    $apiRes->Fares->fareBreakDown->INF->BaseFare =  $baseFareInfant;
                    $apiRes->Fares->fareBreakDown->INF->TotalFare = ($baseFareInfant + $apiRes->Fares->fareBreakDown->INF->TotalTax);
                }
                $apiRes->Fares->TotalPrice = $grandTotal;
                $apiRes->Fares->PricingEnginePrice = $grandTotal;
                $respWithMark[] = $apiRes;
            }

            return $respWithMark;
        }
    }
    public function applyPricingEngineOta($apiResponse, $origin, $destination, $totalPassenger, $api = null, $travel_agent_id)
    {
        // return $totalPassenger;
        $travelagent_id = $travel_agent_id;
        $pricingEngine = PricingEngineTravelAgent::where('travel_agent_id', $travelagent_id)->where('status', 'Active')->where('rule', '!=', ['API Ranking', 'Allowed Airlines'])->get();
        $getType = gettype($apiResponse);
        if ($getType == 'object') {
            $apiResponse->Fares->OriginalPrice = $apiResponse->Fares->TotalPrice;
            $grandTotal = $apiResponse->Fares->TotalPrice;
            $baseFareAdult = 0;
            $baseFareChild = 0;
            $baseFareInfant = 0;
            if (property_exists($apiResponse->Fares->fareBreakDown, 'ADT')) {
                $baseFareAdult = $apiResponse->Fares->fareBreakDown->ADT->BaseFare;
            }
            if (property_exists($apiResponse->Fares->fareBreakDown, 'CNN')) {
                $baseFareChild =  $apiResponse->Fares->fareBreakDown->CNN->BaseFare;
            }
            if (property_exists($apiResponse->Fares->fareBreakDown, 'INF')) {
                $baseFareInfant = $apiResponse->Fares->fareBreakDown->INF->BaseFare;
            }

            foreach ($pricingEngine as $engine) {
                $piceData = json_decode($engine->data);

                $api_name = apiNameById($engine->api_id)->name;
                if ($api_name == $apiResponse->api) {
                    // if($api->identifierName == $apiResponse->ApiIdentifier){

                    if ($piceData->type == 'Fixed') {
                        if ($engine->rule == 'Mark Up') {
                            $markup = $piceData->amount;
                            $grandTotal += $markup * $totalPassenger;

                            $baseFareAdult += $markup;
                            $baseFareChild += $markup;
                            $baseFareInfant += $markup;
                        } else if ($engine->rule == 'Discount') {
                            $discount = $piceData->amount * $totalPassenger;
                            $grandTotal -= $discount;

                            $baseFareAdult -= $discount;
                            $baseFareChild -= $discount;
                            $baseFareInfant -= $discount;
                        } else if ($engine->rule == 'Route Mark Up') {
                            if ($piceData->origin == $origin && $piceData->destination == $destination) {
                                $routeMarkup = $piceData->amount;
                                $grandTotal += $routeMarkup * $totalPassenger;

                                $baseFareAdult += $routeMarkup;
                                $baseFareChild += $routeMarkup;
                                $baseFareInfant += $routeMarkup;
                            }
                        } else if ($engine->rule == 'Route Discount') {
                            if ($piceData->origin == $origin && $piceData->destination == $destination) {
                                $routeDiscount = $piceData->amount;
                                $grandTotal -= $routeDiscount * $totalPassenger;

                                $baseFareAdult -= $routeDiscount;
                                $baseFareChild -= $routeDiscount;
                                $baseFareInfant -= $routeDiscount;
                            }
                        }
                        // else if($engine->rule == 'API Ranking'){

                        // }
                    } else {
                        if ($engine->rule == 'Mark Up') {
                            $markup = ($piceData->amount / 100) * $grandTotal;
                            $grandTotal += $markup * $totalPassenger;

                            $baseFareAdult += $markup;
                            $baseFareChild += $markup;
                            $baseFareInfant += $markup;
                        } else if ($engine->rule == 'Discount') {
                            $discount = ($piceData->amount / 100) * $grandTotal;
                            $grandTotal -= $discount * $totalPassenger;

                            $baseFareAdult -= $discount;
                            $baseFareChild -= $discount;
                            $baseFareInfant -= $discount;
                        } else if ($engine->rule == 'Route Mark Up') {
                            if ($piceData->origin == $origin && $piceData->destination == $destination) {
                                $routeMarkup = ($piceData->amount / 100) * $grandTotal;
                                $grandTotal += $routeMarkup * $totalPassenger;

                                $baseFareAdult += $routeMarkup;
                                $baseFareChild += $routeMarkup;
                                $baseFareInfant += $routeMarkup;
                            }
                        } else if ($engine->rule == 'Route Discount') {
                            if ($piceData->origin == $origin && $piceData->destination == $destination) {
                                $routeDiscount = ($piceData->amount / 100) * $grandTotal;
                                $grandTotal -= $routeDiscount * $totalPassenger;

                                $baseFareAdult -= $routeDiscount;
                                $baseFareChild -= $routeDiscount;
                                $baseFareInfant -= $routeDiscount;
                            }
                        }
                    }
                }
            }

            $apiResponse->Fares->TotalPrice = $grandTotal;
            if (property_exists($apiResponse->Fares->fareBreakDown, 'ADT')) {
                $apiResponse->Fares->fareBreakDown->ADT->BaseFare =  $baseFareAdult;
                $apiResponse->Fares->fareBreakDown->ADT->TotalFare =  ($baseFareAdult + $apiResponse->Fares->fareBreakDown->ADT->TotalTax);
            }
            if (property_exists($apiResponse->Fares->fareBreakDown, 'CNN')) {
                $apiResponse->Fares->fareBreakDown->CNN->BaseFare =  $baseFareChild;
                $apiResponse->Fares->fareBreakDown->CNN->TotalFare =  $baseFareChild + $apiResponse->Fares->fareBreakDown->CNN->TotalTax;
            }
            if (property_exists($apiResponse->Fares->fareBreakDown, 'INF')) {
                $apiResponse->Fares->fareBreakDown->INF->BaseFare = $baseFareInfant;
                $apiResponse->Fares->fareBreakDown->INF->TotalFare =  $baseFareInfant + $apiResponse->Fares->fareBreakDown->INF->TotalTax;
            }
            return $apiResponse;
        } else {
            $respWithMark = array();
            foreach ($apiResponse as $apiRes) {
                $apiRes['Fares']['OriginalPrice'] = $apiRes['Fares']['TotalPrice'];
                $grandTotal = $apiRes['Fares']['TotalPrice'];
                $apiRes['ApiIdentifier'] = @$api->identifierName;

                //start
                $baseFareAdult = 0;
                $baseFareChild = 0;
                $baseFareInfant = 0;
                if (key_exists('ADT', $apiRes['Fares']['fareBreakDown'])) {
                    $baseFareAdult = $apiRes['Fares']['fareBreakDown']['ADT']['BaseFare'];
                }
                if (key_exists('CNN', $apiRes['Fares']['fareBreakDown'])) {
                    $baseFareChild =  $apiRes['Fares']['fareBreakDown']['CNN']['BaseFare'];
                }
                if (key_exists('INF', $apiRes['Fares']['fareBreakDown'])) {
                    $baseFareInfant = $apiRes['Fares']['fareBreakDown']['INF']['BaseFare'];
                }
                //end

                foreach ($pricingEngine as $engine) {
                    $piceData = json_decode($engine->data);

                    if ($api->identifierName == $apiResponse->ApiIdentifier) {
                        if ($piceData->type == 'Fixed') {
                            if ($engine->rule == 'Mark Up') {
                                $markup = $piceData->amount;
                                $grandTotal += $markup * $totalPassenger;

                                $baseFareAdult += $markup;
                                $baseFareChild += $markup;
                                $baseFareInfant += $markup;
                            } else if ($engine->rule == 'Discount') {
                                $discount = $piceData->amount;
                                $grandTotal -= $discount * $totalPassenger;

                                $baseFareAdult -= $discount;
                                $baseFareChild -= $discount;
                                $baseFareInfant -= $discount;
                            } else if ($engine->rule == 'Route Mark Up') {
                                if ($piceData->origin == $origin && $piceData->destination == $destination) {
                                    $routeMarkup = $piceData->amount;
                                    $grandTotal += $routeMarkup * $totalPassenger;
                                    $baseFareAdult += $routeMarkup;
                                    $baseFareChild += $routeMarkup;
                                    $baseFareInfant += $routeMarkup;
                                }
                            } else if ($engine->rule == 'Route Discount') {
                                if ($piceData->origin == $origin && $piceData->destination == $destination) {
                                    $routeDiscount = $piceData->amount;
                                    $grandTotal -= $routeDiscount * $totalPassenger;

                                    $baseFareAdult -= $routeDiscount;
                                    $baseFareChild -= $routeDiscount;
                                    $baseFareInfant -= $routeDiscount;
                                }
                            }
                            // else if($engine->rule == 'API Ranking'){

                            // }
                        } else {
                            if ($engine->rule == 'Mark Up') {
                                $markup = ($piceData->amount / 100) * $grandTotal;
                                $grandTotal += $markup * $totalPassenger;
                                $baseFareAdult += $markup;
                                $baseFareChild += $markup;
                                $baseFareInfant += $markup;
                            } else if ($engine->rule == 'Discount') {
                                $discount = ($piceData->amount / 100) * $grandTotal;
                                $grandTotal -= $discount * $totalPassenger;

                                $baseFareAdult -= $discount;
                                $baseFareChild -= $discount;
                                $baseFareInfant -= $discount;
                            } else if ($engine->rule == 'Route Mark Up') {
                                if ($piceData->origin == $origin && $piceData->destination == $destination) {
                                    $routeMarkup = ($piceData->amount / 100) * $grandTotal;
                                    $grandTotal += $routeMarkup * $totalPassenger;
                                    $baseFareAdult += $routeMarkup;
                                    $baseFareChild += $routeMarkup;
                                    $baseFareInfant += $routeMarkup;
                                }
                            } else if ($engine->rule == 'Route Discount') {
                                if ($piceData->origin == $origin && $piceData->destination == $destination) {
                                    $routeDiscount = ($piceData->amount / 100) * $grandTotal;
                                    $grandTotal -= $routeDiscount * $totalPassenger;
                                    $baseFareAdult -= $routeDiscount;
                                    $baseFareChild -= $routeDiscount;
                                    $baseFareInfant -= $routeDiscount;
                                }
                            }
                            // if($engine->rule == 'API Ranking'){

                            // }

                        }
                    }
                }
                if (key_exists('ADT', $apiRes['Fares']['fareBreakDown'])) {
                    $apiRes['Fares']['fareBreakDown']['ADT']['BaseFare'] = $baseFareAdult;
                    $apiRes['Fares']['fareBreakDown']['ADT']['TotalFare'] = ($baseFareAdult + $apiRes['Fares']['fareBreakDown']['ADT']['TotalTax']);
                }
                if (key_exists('CNN', $apiRes['Fares']['fareBreakDown'])) {
                    $apiRes['Fares']['fareBreakDown']['CNN']['BaseFare'] = $baseFareChild;
                    $apiRes['Fares']['fareBreakDown']['CNN']['TotalFare'] = ($baseFareChild + $apiRes['Fares']['fareBreakDown']['CNN']['TotalTax']);
                }
                if (key_exists('INF', $apiRes['Fares']['fareBreakDown'])) {
                    $apiRes['Fares']['fareBreakDown']['INF']['BaseFare'] =  $baseFareInfant;
                    $apiRes['Fares']['fareBreakDown']['INF']['TotalFare'] = ($baseFareInfant + $apiRes['Fares']['fareBreakDown']['INF']['TotalTax']);
                }
                $apiRes['Fares']['TotalPrice'] = $grandTotal;
                $respWithMark[] = $apiRes;
            }

            return $respWithMark;
        }
    }
}
