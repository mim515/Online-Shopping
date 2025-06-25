<?php
session_start();
include 'components/connect.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_SESSION['order_id'] ?? '';

if(!$order_id){
    header('Location: orders.php');
    exit();
}

$select_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ? AND user_id = ? AND payment_status = 'pending'");
$select_order->execute([$order_id, $user_id]);
$order = $select_order->fetch(PDO::FETCH_ASSOC);

if(!$order){
    $_SESSION['message'] = 'Invalid order or already paid!';
    header('Location: orders.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $transaction_id = filter_var($_POST['transaction_id'], FILTER_SANITIZE_STRING);
    $phone_number = filter_var($_POST['phone_number'], FILTER_SANITIZE_STRING);

    // Update order in database
    // $update_order = $conn->prepare("UPDATE `orders` SET payment_status = 'completed', transaction_id = ?, phone_number = ? WHERE id = ?");
    // $update_order->execute([$transaction_id, $phone_number, $order_id]);

    // Load PHPMailer
    require 'PHPMailer/Exception.php';
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';

    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ashrafulakash467@gmail.com'; // Your email
        $mail->Password   = 'your_app_specific_password'; // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('ashrafulakash467@gmail.com', 'EasyShop');
        $mail->addAddress($order['email'], $order['name']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Payment Confirmation - Order #$order_id";
        
        // Build the email body with order details
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #333;'>Thank you for your payment, {$order['name']}!</h2>
                <p style='color: #555;'>Your payment for Order #$order_id has been successfully processed.</p>
                
                <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='color: #333; margin-top: 0;'>Order Summary</h3>
                    <p><strong>Order ID:</strong> $order_id</p>
                    <p><strong>Transaction ID:</strong> $transaction_id</p>
                    <p><strong>Payment Method:</strong> {$order['method']}</p>
                    <p><strong>Phone Number:</strong> $phone_number</p>
                    <p><strong>Total Amount:</strong> Nrs.{$order['total_price']}/-</p>
                    <p><strong>Order Date:</strong> ".date('F j, Y', strtotime($order['placed_on']))."</p>
                </div>
                
                <div style='margin: 20px 0;'>
                    <h3 style='color: #333;'>Order Items</h3>
                    <p>{$order['total_products']}</p>
                </div>
                
                <div style='margin: 20px 0;'>
                    <h3 style='color: #333;'>Shipping Address</h3>
                    <p>{$order['address']}</p>
                </div>
                
                <p style='color: #555;'>Your order is now being processed. We'll notify you when it ships.</p>
                
                <p style='color: #555;'>If you have any questions, please contact our customer support.</p>
                
                <p style='color: #333; font-weight: bold;'>Thank you for shopping with EasyShop!</p>
            </div>
        ";

        // Plain text version for non-HTML email clients
        $mail->AltBody = "Thank you for your payment, {$order['name']}!\n\n"
                        . "Order ID: $order_id\n"
                        . "Transaction ID: $transaction_id\n"
                        . "Amount: Nrs.{$order['total_price']}/-\n\n"
                        . "Your order is being processed. We'll notify you when it ships.\n\n"
                        . "Thank you for shopping with EasyShop!";

        $mail->send();
        $_SESSION['message'] = 'Payment submitted successfully! Confirmation sent to your email.';
    } catch (Exception $e) {
        $_SESSION['message'] = "Payment was successful but we couldn't send confirmation email. Please note your transaction ID: $transaction_id";
        error_log("Email sending error: " . $e->getMessage());
    }

    // Clear session data
    unset($_SESSION['order_id'], $_SESSION['total_price'], $_SESSION['payment_method']);
    header('Location: orders.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Complete Payment</title>
   <link rel="stylesheet" href="css/style.css">
   <style>
       .payment-form {
           max-width: 800px;
           margin: 20px auto;
           padding: 20px;
           background: #fff;
           border-radius: 8px;
           box-shadow: 0 0 10px rgba(0,0,0,0.1);
       }
       .order-summary {
           background: #f9f9f9;
           padding: 15px;
           border-radius: 5px;
           margin-bottom: 20px;
       }
       .inputBox {
           margin-bottom: 15px;
       }
       .inputBox span {
           display: block;
           margin-bottom: 5px;
           font-weight: bold;
       }
       .inputBox input {
           width: 100%;
           padding: 10px;
           border: 1px solid #ddd;
           border-radius: 4px;
           box-sizing: border-box;
       }
       .btn {
           background: #28a745;
           color: white;
           padding: 10px 15px;
           border: none;
           border-radius: 4px;
           cursor: pointer;
           font-size: 16px;
           width: 100%;
       }
       .btn:hover {
           background: #218838;
       }
       .message {
           padding: 10px;
           margin: 10px 0;
           border-radius: 4px;
       }
       .success {
           background: #d4edda;
           color: #155724;
       }
       .error {
           background: #f8d7da;
           color: #721c24;
       }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="payment-form">
   <h1 class="heading">Complete Payment</h1>
   
   <?php if(isset($_SESSION['message'])): ?>
      <div class="message <?= strpos($_SESSION['message'], 'successfully') !== false ? 'success' : 'error' ?>">
          <?= $_SESSION['message']; ?>
      </div>
      <?php unset($_SESSION['message']); ?>
   <?php endif; ?>

   <div class="order-summary">
       <h3>Order Summary</h3>
       <p><strong>Order ID:</strong> <?= $order_id; ?></p>
       <p><strong>Payment Method:</strong> <?= $order['method']; ?></p>
       <p><strong>Total Amount:</strong> Nrs.<?= $order['total_price']; ?>/-</p>
   </div>

   <form method="POST">
      <div class="inputBox">
          <span>Transaction ID:</span>
          <input type="text" name="transaction_id" required placeholder="Enter transaction ID">
      </div>
      <div class="inputBox">
    <p><strong>Enter Your <?= htmlspecialchars($order['method']); ?></strong> <strong> Pin</strong> </p>
    <input type="text" name="phone_number" required placeholder="Enter phone number used for payment">
</div>

      <input type="submit" value="Submit Payment" class="btn">
   </form>
</section>

<?php include 'components/footer.php'; ?>
</body>
</html>