<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Form</title>
</head>
<body>

<form action="send_email.php" method="post">
  <label for="firstName">First Name:</label>
  <input type="text" id="firstName" name="firstName" required>
  <br>
 
  <label for="email">Email Address:</label>
  <input type="email" id="email" name="email" required>
  <br>
 
  <label for="message">Message:</label>
  <textarea id="message" name="message" required></textarea>
  <br>
 
  <input type="submit" value="Send">
</form>

</body>
</html>