<?php
// backend/mpesa_stk_push.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_config.php';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->complaint_id) && isset($data->amount) && isset($data->phone)) {
    
    $phone = $data->phone; 
    // For testing in the sandbox, it's highly recommended to hardcode the amount to 1 to avoid hitting sandbox limits.
    // Once everything works perfectly, change this back to: $amount = $data->amount;
    $amount = 1; 
    $complaint_id = $data->complaint_id;

    // ==========================================
    // 1. DARAJA SANDBOX CREDENTIALS
    // ==========================================
    // PASTE YOUR KEYS HERE FROM STEP 1
    $consumerKey = 'xfBHUelVHAby2ol0g37CFC141cSbeeI3kxS1pG65sLMGyQAn'; 
    $consumerSecret = 'uFzZsVYa48rMIans6NPHbbGbRDrT4RMiUgFENL9OgWlY5lA3awAlRljM6KLWtgzQ';

    // Standard Safaricom Sandbox Test Credentials
    $BusinessShortCode = '174379';
    $Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';  
    $PartyA = $phone; 
    $AccountReference = 'County Complaints';
    $TransactionDesc = 'Fee for Complaint ID ' . $complaint_id;
    
    // NOTE: Safaricom will send the final success/fail receipt here. 
    // It MUST be a publicly accessible HTTPS URL. 'localhost' will NOT work for Safaricom to reach you back.
    // For now, put a dummy URL just to trigger the push.
    $CallBackURL = 'https://mydomain.com/backend/mpesa_callback.php';  

    // ==========================================
    // 2. GENERATE ACCESS TOKEN
    // ==========================================
    $authUrl = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $credentials = base64_encode($consumerKey . ':' . $consumerSecret);

    $curl_auth = curl_init();
    curl_setopt($curl_auth, CURLOPT_URL, $authUrl);
    curl_setopt($curl_auth, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
    curl_setopt($curl_auth, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_auth, CURLOPT_HEADER, false);
    curl_setopt($curl_auth, CURLOPT_SSL_VERIFYPEER, false);

    $auth_response = curl_exec($curl_auth);
    $auth_error = curl_error($curl_auth);
    curl_close($curl_auth);

    if ($auth_error) {
        echo json_encode(["success" => false, "message" => "Failed to connect to Safaricom Auth"]);
        exit();
    }

    $auth_data = json_decode($auth_response);
    
    if (!isset($auth_data->access_token)) {
        echo json_encode(["success" => false, "message" => "Invalid credentials or token generation failed", "safaricom_error" => $auth_response]);
        exit();
    }

    $access_token = $auth_data->access_token;

    // ==========================================
    // 3. INITIATE STK PUSH
    // ==========================================
    $stkUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $Timestamp = date('YmdHis');
    $Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);

    $curl_post_data = [
        'BusinessShortCode' => $BusinessShortCode,
        'Password' => $Password,
        'Timestamp' => $Timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $PartyA,
        'PartyB' => $BusinessShortCode,
        'PhoneNumber' => $PartyA,
        'CallBackURL' => $CallBackURL,
        'AccountReference' => $AccountReference,
        'TransactionDesc' => $TransactionDesc
    ];

    $curl_stk = curl_init();
    curl_setopt($curl_stk, CURLOPT_URL, $stkUrl);
    curl_setopt($curl_stk, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ]);
    curl_setopt($curl_stk, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_stk, CURLOPT_POST, true);
    curl_setopt($curl_stk, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
    curl_setopt($curl_stk, CURLOPT_SSL_VERIFYPEER, false);

    $stk_response = curl_exec($curl_stk);
    $stk_error = curl_error($curl_stk);
    curl_close($curl_stk);

    if ($stk_error) {
        echo json_encode(["success" => false, "message" => "Failed to initiate STK Push"]);
        exit();
    }

    $stk_data = json_decode($stk_response);

    // ==========================================
    // ==========================================
    // 4. HANDLE RESPONSE
    // ==========================================
    if (isset($stk_data->ResponseCode) && $stk_data->ResponseCode == "0") {
        
        // 🚨 DEMO MODE HACK: AUTO-UPDATE DATABASE 🚨
        // Because localhost cannot receive the Safaricom Callback, 
        // we will instantly update the database to 'Paid' right here.
        try {
            $updateSql = "UPDATE complaints 
                          SET payment_status = 'Paid', 
                              status = 'In Progress' 
                          WHERE id = :id";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindValue(':id', $complaint_id, PDO::PARAM_INT);
            $updateStmt->execute();
        } catch (PDOException $e) {
            // Handle error quietly
        }
        // 🚨 END DEMO HACK 🚨

        echo json_encode([
            "success" => true, 
            "message" => "STK Push initiated successfully. Please enter your M-Pesa PIN.",
            "CheckoutRequestID" => $stk_data->CheckoutRequestID
        ]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "STK Push failed",
            "safaricom_response" => $stk_data
        ]);
    }

} else {
    http_response_code(400);
    echo json_encode(["error" => "Missing phone, amount, or complaint_id"]);
}
?>