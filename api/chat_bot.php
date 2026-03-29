<?php
// api/chat_bot.php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$userMessage = $data['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['error' => 'Message is empty']);
    exit;
}

// ⚠️ IMPORTANT: Replace this with your actual NVIDIA API KEY or set it in your environment
$api_key = getenv('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');
if (!$api_key || $api_key == '') {
    $api_key = "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"; // User will need to paste their key here
}

$url = "https://integrate.api.nvidia.com/v1/chat/completions";

$postData = [
    "model" => "google/gemma-2-27b-it",
    "messages" => [
        [
            "role" => "user",
            "content" => "You are a highly professional, concise, and helpful AI concierge for the 'Hotel Finder' premium booking platform. Your goal is to help users find luxury hotels, resorts, and villas. Respond to the following user message effectively and with emojis:\n\nUser Message: " . $userMessage
        ]
    ],
    "temperature" => 0.2,
    "top_p" => 0.7,
    "max_tokens" => 1024,
    "stream" => false
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local XAMPP testing

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if(curl_errno($ch)){
    echo json_encode(['error' => 'cURL Error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}
curl_close($ch);

if ($httpcode !== 200) {
    echo json_encode(['error' => 'NVIDIA API Error (' . $httpcode . ')', 'details' => json_decode($response, true)]);
    exit;
}

$json = json_decode($response, true);
$botReply = $json['choices'][0]['message']['content'] ?? 'Sorry, I am having trouble thinking right now.';

echo json_encode(['reply' => $botReply]);
?>
