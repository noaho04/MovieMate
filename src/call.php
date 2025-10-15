<?php
function call_api($chatlog) {
    include "APIKEY.php";

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";

    $system_instruction = ["parts" => [["text" => "Du er en hyggelig AI-chatbot som skal gi anbefalinger og informasjon om filmer. Hold svarene korte og ikke bruk formattering som fet-skrift og lister."]]];

    $data = [
        "system_instruction" => $system_instruction,
        "contents" => $chatlog
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-goog-api-key: $api_key",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "cURL error: " . curl_error($ch);
    } else {
        $result = json_decode($response, true);
    }

    curl_close($ch);

    return $result['candidates'][0]['content']['parts'][0]['text'];
}
?>