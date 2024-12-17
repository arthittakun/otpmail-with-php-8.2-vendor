<?php
date_default_timezone_set("Asia/Bangkok");
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "otp_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$otp = $data['otp'] ?? '';

if (empty($otp)) {
    echo json_encode(["success" => false, "message" => "OTP is required"]);
    exit;
}

// ตรวจสอบ OTP และเวลาหมดอายุ
$sql = "SELECT * FROM otp_codes WHERE otp_code = ? AND expires_at > NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // ลบ OTP หลังใช้งาน
    $delete_sql = "DELETE FROM otp_codes WHERE otp_code = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("s", $otp);
    $delete_stmt->execute();
    echo json_encode(["success" => true, "message" => "OTP is valid"]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid or expired OTP"]);
}

$stmt->close();
$conn->close();
?>
