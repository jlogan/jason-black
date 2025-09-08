<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load SendGrid
require 'vendor/autoload.php';

// Load configuration
require_once 'config.php';


// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Validate required fields
if (empty($name) || empty($phone) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate phone number (basic validation)
$cleanPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
if (!preg_match('/^[\+]?[1-9][\d]{9,15}$/', $cleanPhone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
    exit;
}

// Sanitize data
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

// Prepare CSV data
$timestamp = date('Y-m-d H:i:s');
$csvData = [
    $timestamp,
    $name,
    $phone,
    $email
];

// CSV file path
$csvFile = 'form_submissions.csv';

// Check if CSV file exists, if not create header
if (!file_exists($csvFile)) {
    $header = ['Timestamp', 'Name', 'Phone', 'Email'];
    $fp = fopen($csvFile, 'w');
    fputcsv($fp, $header, ',', '"', '\\');
    fclose($fp);
}

// Append data to CSV file
$fp = fopen($csvFile, 'a');
if ($fp === false) {
    echo json_encode(['success' => false, 'message' => 'Unable to save data']);
    exit;
}

// Lock file for writing
if (flock($fp, LOCK_EX)) {
    fputcsv($fp, $csvData, ',', '"', '\\');
    flock($fp, LOCK_UN);
    fclose($fp);
    
    // Send email notification using SendGrid (with fallback to PHP mail)
    $emailSent = sendEmailNotification($name, $phone, $email, $timestamp);
    
    // Send success response
    $message = 'Data saved successfully';
    if ($emailSent) {
        $message .= ' and email notification sent';
    } else {
        $message .= ' (email notification failed)';
    }
    
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    fclose($fp);
    echo json_encode(['success' => false, 'message' => 'Unable to save data']);
}

/**
 * Send email notification using SendGrid
 */
function sendEmailNotification($name, $phone, $email, $timestamp) {
    return sendEmailWithSendGrid($name, $phone, $email, $timestamp);
}


/**
 * Send email using SendGrid
 */
function sendEmailWithSendGrid($name, $phone, $email, $timestamp) {
    try {
        $sendgrid = new \SendGrid(SENDGRID_API_KEY);
        
        // Admin notification email
        $adminEmail = new \SendGrid\Mail\Mail();
        $adminEmail->setFrom(FROM_EMAIL, FROM_NAME);
        $adminEmail->setSubject("New Campaign Contact: " . htmlspecialchars($name) . " - Jason Black for State Senate");
        $adminEmail->addTo(TO_EMAIL, TO_NAME);
        $adminEmail->setReplyTo($email, $name);
        
        // Create HTML email content
        $htmlContent = "
        <html>
        <head>
            <title>New Campaign Contact - Jason Black for State Senate</title>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f5f5f5;'>
            <div style='max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #B11F29 0%, #8B1A1A 100%); padding: 30px 20px; text-align: center;'>
                    <h1 style='color: #ffffff; margin: 0; font-size: 24px; font-weight: bold;'>Jason Black for State Senate</h1>
                    <p style='color: #ffffff; margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>New Campaign Contact</p>
                </div>
                
                <!-- Content -->
                <div style='padding: 30px 20px;'>
                    <h2 style='color: #B11F29; margin: 0 0 20px 0; font-size: 20px;'>New Contact Form Submission</h2>
                    
                    <div style='background: #f8f9fa; border-left: 4px solid #B11F29; padding: 20px; margin: 20px 0; border-radius: 0 4px 4px 0;'>
                        <h3 style='margin: 0 0 15px 0; color: #333; font-size: 16px;'>Contact Information</h3>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #555; width: 120px;'>Name:</td>
                                <td style='padding: 8px 0; color: #333;'>" . htmlspecialchars($name) . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #555;'>Phone:</td>
                                <td style='padding: 8px 0; color: #333;'><a href='tel:" . htmlspecialchars($phone) . "' style='color: #B11F29; text-decoration: none;'>" . htmlspecialchars($phone) . "</a></td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #555;'>Email:</td>
                                <td style='padding: 8px 0; color: #333;'><a href='mailto:" . htmlspecialchars($email) . "' style='color: #B11F29; text-decoration: none;'>" . htmlspecialchars($email) . "</a></td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #555;'>Submitted:</td>
                                <td style='padding: 8px 0; color: #333;'>" . htmlspecialchars($timestamp) . "</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div style='background: #e8f4fd; border: 1px solid #bee5eb; padding: 15px; border-radius: 4px; margin: 20px 0;'>
                        <h4 style='margin: 0 0 10px 0; color: #0c5460; font-size: 14px;'>Quick Actions</h4>
                        <p style='margin: 0; font-size: 14px; color: #0c5460;'>
                            • <a href='mailto:" . htmlspecialchars($email) . "?subject=Re: Your Interest in Jason Black for State Senate' style='color: #B11F29; text-decoration: none;'>Reply to this contact</a><br>
                            • Add to campaign database<br>
                            • Follow up within 24 hours
                        </p>
                    </div>
                    
                    <div style='border-top: 2px solid #e9ecef; padding-top: 20px; margin-top: 30px;'>
                        <p style='color: #6c757d; font-size: 14px; margin: 0; text-align: center;'>
                            <strong>Jason Black for State Senate</strong><br>
                            <em>Present. Proven. Ready.</em><br>
                            <span style='font-size: 12px;'>This is an automated message from the campaign website.</span>
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='background: #f8f9fa; padding: 15px 20px; text-align: center; border-top: 1px solid #e9ecef;'>
                    <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                        PAID FOR BY FRIENDS OF JASON BLACK, INC.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $adminEmail->addContent("text/html", $htmlContent);
        
        // Send admin notification
        $response = $sendgrid->send($adminEmail);
        
        if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
        // Send confirmation email to user
        sendConfirmationEmail($name, $email);
            return true;
        } else {
            error_log("SendGrid failed with status: " . $response->statusCode() . " - " . $response->body());
            return false;
        }
        
    } catch (Exception $e) {
        error_log("SendGrid email sending failed: " . $e->getMessage());
        return false;
    }
}


/**
 * Send confirmation email using SendGrid
 */
function sendConfirmationEmail($name, $email) {
    try {
        $sendgrid = new \SendGrid(SENDGRID_API_KEY);
        
        $userEmail = new \SendGrid\Mail\Mail();
        $userEmail->setFrom(FROM_EMAIL, FROM_NAME);
        $userEmail->setSubject("Thank you for your interest - Jason Black for State Senate");
        $userEmail->addTo($email, $name);
        
        // Create HTML confirmation email
        $htmlContent = "
        <html>
        <head>
            <title>Thank You</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #B11F29;'>Thank you, " . htmlspecialchars($name) . "!</h2>
                <p>Thank you for your interest in Jason Black's campaign for State Senate. We have received your information and will be in touch soon.</p>
                
                <p>Together, we can elect a leader who listens, cares, and delivers for our community.</p>
                
                <div style='background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center;'>
                    <p style='margin: 0; font-size: 18px; font-weight: bold; color: #B11F29;'>Jason Black for State Senate</p>
                    <p style='margin: 5px 0 0 0; font-style: italic; color: #666;'>Present. Proven. Ready.</p>
                </div>
                
                <hr style='border: 1px solid #eee; margin: 20px 0;'>
                <p style='color: #666; font-size: 12px;'>
                    <em>This is an automated message. Please do not reply to this email.</em>
                </p>
            </div>
        </body>
        </html>
        ";
        
        $userEmail->addContent("text/html", $htmlContent);
        
        $response = $sendgrid->send($userEmail);
        return $response->statusCode() >= 200 && $response->statusCode() < 300;
        
    } catch (Exception $e) {
        error_log("SendGrid confirmation email sending failed: " . $e->getMessage());
        return false;
    }
}

?>
