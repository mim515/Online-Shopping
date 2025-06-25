<?php
include 'components/connect.php';
session_start();

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

if(isset($_POST['order'])){

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
   $address = 'flat no. '. $_POST['flat'] .', '. $_POST['street'] .', '. $_POST['city'] .', '. $_POST['state'] .', '. $_POST['country'] .' - '. $_POST['pin_code'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];

   // Check if cart is not empty
   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if($check_cart->rowCount() > 0){

      // Insert order into database
      $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);

      // Get the last inserted order ID
      $order_id = $conn->lastInsertId();

      // Store order details in the session
      $_SESSION['order_id'] = $order_id;
      $_SESSION['total_price'] = $total_price;
      $_SESSION['payment_method'] = $method;

      // Clear the cart
      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);

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
          $mail->Username   = 'Your email'; // Your email
          $mail->Password   = 'Your App password'; // App password
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
          $mail->Port       = 465;

          // Recipients
          $mail->setFrom('Your email', 'EasyShop');
          $mail->addAddress($email, $name);

          // Content
          $mail->isHTML(true);
          $mail->Subject = "Order Confirmation #$order_id";
          
          // Build the email body
          $mail->Body = "
              <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                  <h2 style='color: #333;'>Thank you for your order, $name!</h2>
                  <p style='color: #555;'>Your order has been received and is being processed.</p>
                  
                     <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                  <h3 style='color: #333; margin-top: 0;'>Order Details</h3>
                  <p><strong>Order ID:</strong> $order_id</p>
                  <p><strong>Order Date:</strong> " . date('F j, Y') . "</p>
                  <p><strong>Payment Method:</strong> $method</p>
                  <p><strong>Total Amount:</strong> Nrs.$total_price/-</p>
                  <p><strong style='color: red;'>Transection Id:</strong> <strong style='color: blue;'>112211</strong></p>
                     </div>
                  
                  <div style='margin: 20px 0;'>
                      <h3 style='color: #333;'>Order Items</h3>
                      <p>$total_products</p>
                  </div>
                  
                  <div style='margin: 20px 0;'>
                      <h3 style='color: #333;'>Shipping Address</h3>
                      <p>$address</p>
                  </div>
                  
                  <p style='color: #555;'>We'll notify you when your order ships. If you have any questions, please contact us.</p>
                  
                  <p style='color: #333; font-weight: bold;'>Thank you for shopping with EasyShop!</p>
              </div>
          ";

          // Plain text version for non-HTML email clients
          $mail->AltBody = "Thank you for your order, $name!\n\n"
                          . "Order ID: $order_id\n"
                          . "Order Date: " . date('F j, Y') . "\n"
                          . "Payment Method: $method\n"
                          . "Total Amount: Nrs.$total_price/-\n\n"
                          . "Shipping Address:\n$address\n\n"
                          . "Order Items:\n$total_products\n\n"
                          . "We'll notify you when your order ships.\n\n"
                          . "Thank you for shopping with EasyShop!";

          $mail->send();
          $_SESSION['message'] = 'Order placed successfully You can Pay now ! Transection Id sent to your email.';
      } catch (Exception $e) {
          $_SESSION['message'] = "Order was placed but we couldn't send confirmation email. Please note your order ID: $order_id";
          error_log("Email sending error: " . $e->getMessage());
      }

      // Redirect to the payment page
      header('Location: payment.php');
      exit();
   }
   else{
      $message[] = 'Your cart is empty';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>checkout</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">

   <form action="" method="POST">

   <h3>Your Orders</h3>

      <div class="display-orders">
      <?php
         $grand_total = 0;
         $cart_items[] = '';
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') - ';
               $total_products = implode($cart_items);
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
      ?>
         <p> <?= $fetch_cart['name']; ?> <span>(<?= '$'.$fetch_cart['price'].'/- x '. $fetch_cart['quantity']; ?>)</span> </p>
      <?php
            }
         }else{
            echo '<p class="empty">your cart is empty!</p>';
         }
      ?>
         <input type="hidden" name="total_products" value="<?= $total_products; ?>">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>" value="">
         <div class="grand-total">Grand Total : <span>Nrs.<?= $grand_total; ?>/-</span></div>
      </div>

      <h3>place your orders</h3>

      <div class="flex">
         <div class="inputBox">
            <span>Your Name:</span>
            <input type="text" name="name" placeholder="enter your name" class="box" maxlength="40" required>
         </div>
         <div class="inputBox">
            <span>Your Email :</span>
            <input type="email" name="email" placeholder="enter your email" class="box" maxlength="30" required>
         </div>
         <div class="inputBox">
            <span>How To Pay? :</span>
            <select name="method" class="box" required>
               <option value="Rocket">Rocket</option>
               <option value="Bkash">Bkash</option>
               <option value="Nagad">Nagad</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Your Payment id Number :</span>
            <input type="number" name="number" placeholder="enter your number" class="box" min="0" max="999999999999" onkeypress="if(this.value.length == 11) return false;" required>
         </div>
         <div class="inputBox">
            <span>Area:</span>
            <input type="text" name="state" placeholder="Uttara" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Address,Street no :</span>
            <input type="text" name="street" placeholder="e.g. Street Name/No." class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Address,Flat no :</span>
            <input type="text" name="flat" placeholder="e.g. Flat number" class="box" maxlength="50" required>
         </div>
         
         <div class="inputBox">
            <span>City :</span>
            <input type="text" name="city" placeholder="Dhaka" class="box" maxlength="50" required>
         </div>
         
         <div class="inputBox">
            <span>Country :</span>
            <input type="text" name="country" placeholder="Bangladesh" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>ZIP CODE :</span>
            <input type="number" min="0" name="pin_code" placeholder="e.g. 1240" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($grand_total > 1)?'':'disabled'; ?>" value="place order">

   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
