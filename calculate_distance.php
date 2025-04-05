<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_key = '5b3ce3597851110001cf6248bfe7817b566145d4b717d4ce1aa0ce4b'; // Replace with your OpenRouteService API Key
    $data = json_decode(file_get_contents('php://input'), true);

    $departure = $data['departure_location'] ?? null;
    $arrival = $data['arrival_location'] ?? null;

    if (!$departure || !$arrival) {
        echo json_encode(['success' => false, 'message' => 'Both departure and arrival locations are required.']);
        exit();
    }

    $geocode_url = "https://api.openrouteservice.org/geocode/search?api_key={$api_key}&text=";

    // Get coordinates for departure location
    $departure_response = @file_get_contents($geocode_url . urlencode($departure));
    $departure_data = json_decode($departure_response, true);
    $departure_coords = $departure_data['features'][0]['geometry']['coordinates'] ?? null;

    // Debugging: Log the geocoding response for departure
    file_put_contents('logs.txt', "Departure Response: " . print_r($departure_data, true), FILE_APPEND);

    // Get coordinates for arrival location
    $arrival_response = @file_get_contents($geocode_url . urlencode($arrival));
    $arrival_data = json_decode($arrival_response, true);
    $arrival_coords = $arrival_data['features'][0]['geometry']['coordinates'] ?? null;

    // Debugging: Log the geocoding response for arrival
    file_put_contents('logs.txt', "Arrival Response: " . print_r($arrival_data, true), FILE_APPEND);

    if (!$departure_coords || !$arrival_coords) {
        echo json_encode(['success' => false, 'message' => 'Could not geocode one or both locations.']);
        exit();
    }

    // Calculate distance using OpenRouteService API
    $distance_url = "https://api.openrouteservice.org/v2/directions/driving-car";
    $body = [
        'coordinates' => [$departure_coords, $arrival_coords]
    ];

    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\nAuthorization: $api_key\r\n",
            'method' => 'POST',
            'content' => json_encode($body)
        ]
    ];

    $context = stream_context_create($options);
    $distance_response = @file_get_contents($distance_url, false, $context);
    $distance_data = json_decode($distance_response, true);

    // Debugging: Log the distance calculation response
    file_put_contents('logs.txt', "Distance Response: " . print_r($distance_data, true), FILE_APPEND);

    if (isset($distance_data['routes'][0]['summary']['distance'])) {
        $distance = $distance_data['routes'][0]['summary']['distance'] / 1000; // Convert meters to kilometers
        $price_per_km = 10; // Example price per kilometer
        $price = $distance * $price_per_km;

        echo json_encode([
            'success' => true,
            'distance' => round($distance, 2),
            'price' => round($price, 2)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not calculate distance.']);
    }
}
?>