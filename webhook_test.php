<?php
// File: webhook_test.php
// Letakkan file ini di folder /v/ sejajar dengan index.php
$raw_input = file_get_contents('php://input');
$time = date('Y-m-d H:i:s');
$method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';

$log_content = "=== NATIVE WEBHOOK TEST AT " . $time . " ===\n";
$log_content .= "Method: " . $method . "\n";
$log_content .= "Payload: " . $raw_input . "\n\n";

file_put_contents('wa_native.txt', $log_content, FILE_APPEND);

header("Content-Type: application/json");
echo json_encode(["status" => "success"]);
