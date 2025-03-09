<?php
// generate_insights.php

header('Content-Type: application/json');

// Get the incoming POST request data
$data = json_decode(file_get_contents("php://input"), true);

// Extract the prompt and data from the request
$prompt = $data['prompt'] ?? '';
$reportData = $data['data'] ?? '';

// Validate if both prompt and report data are provided
if (empty($prompt) || empty($reportData)) {
    echo json_encode(["status" => "error", "message" => "Report data and prompt are required"]);
    exit();
}

// Log the received data for debugging
error_log("Received prompt: " . $prompt);
error_log("Received report data: " . print_r($reportData, true));

// API Key for Gemini (replace with your actual API key)
$apiKey = 'ke'; // Replace with your Gemini API key
$geminiModel = 'gemini-2.0-flash'; // Model you want to use

// Prepare the request payload for the Gemini API
$requestPayload = json_encode([
    "contents" => [
        [
            "parts" => [
                [
                    "text" => "Here are a few generic approaches for generating insights from a given dataset:\n" .
                    "1. Descriptive Insights: Provide key statistics, such as averages, sums, and counts.\n" .
                    "2. Trend Insights: Identify patterns or trends over time.\n" .
                    "3. Comparative Insights: Compare different datasets or entities.\n" .
                    "4. Correlation Insights: Suggest correlations between different variables.\n\n" .
                    "Please apply the most suitable approach from the above to the following dataset and provide brief insights:\n" .
                    json_encode($reportData) . "\n\n" .
                    "For the output, follow this format:\n" .
                    "1. Provide insights in a simple, clear text format, without extra markdown, bullet points, or unnecessary noise.\n" .
                    "2. Make sure the insights are concise, focusing only on the key findings without elaborating too much.\n" .
                    "2. End with a **brief summary** of the entire data report, summarizing the major points. Provide the summary in clear, plain text with no extra formatting.\n" .
                    "Do not add unnecessary descriptions or markdown formatting. Just plain text, brief, and insightful."
                ]
            ]
        ]
    ]
]);

// Call the Gemini API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://generativelanguage.googleapis.com/v1/models/{$geminiModel}:generateContent?key={$apiKey}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);

// Check if the request was successful
if ($response === false) {
    echo json_encode(["status" => "error", "message" => "Failed to call Gemini API"]);
    exit();
}

curl_close($ch);

// Decode the Gemini API response
$responseData = json_decode($response, true);

// Check if the response contains the generated insights
if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $insights = $responseData['candidates'][0]['content']['parts'][0]['text'];
    echo json_encode(["status" => "success", "insights" => $insights]);
} else {
    echo json_encode(["status" => "error", "message" => "No insights generated"]);
}
?>