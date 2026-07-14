<?php

namespace App\Enum;

enum DeliveryEndpointType: string
{
    case CreateShipment = 'create_shipment';
    case CancelShipment = 'cancel_shipment';
    case TrackShipment = 'track_shipment';
    case GetLabel = 'get_label';
    case CalculateCost = 'calculate_cost';
    case GetCities = 'get_cities';
    case Auth = 'auth';
}
