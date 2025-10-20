<?php
function call_api($chatlog) {
    include "API_KEY.php";

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";

    // Define baseline system instruction for the chatbot
    $system_instruction = ["parts" => [["text" => "Du er MovieMate, en hyggelig AI-chatbot som skal gi anbefalinger og informasjon om filmer. Hold svarene korte og ikke bruk formattering som fet-skrift og lister."]]];

    // Pass the system instruction and chatlog to $data
    $data = [
        "system_instruction" => $system_instruction,
        "contents" => $chatlog
    ];

    $ch = curl_init($url);

    // Set up headers for request
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-goog-api-key: $API_KEY",
        "Content-Type: application/json"
    ]);
    // Define it as a POST request, and pass $data as json
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    // Return the response instead of dumping it out
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Execute the request
    $response = curl_exec($ch);
    // Simple error handling for curl errors (expand upon, + api errors)
    if (curl_errno($ch)) {
        $result = "cURL error: " . curl_error($ch);
    } else {
        $result = json_decode($response, true);
        // Get the resulting text from API-response (add checks for eventual error responses, and default error response from AI as message)
        $result = $result['candidates'][0]['content']['parts'][0]['text'];
    }
    curl_close($ch);
    return $result;
}
?>