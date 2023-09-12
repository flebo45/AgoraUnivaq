<?php
$to = "alessio.torrieri@gmail.com"; // Replace with the recipient's email address
$subject = "Hello, User!";
$message = "This is a test email sent from PHP.";
$headers = "From: noreply@example.com"; // Use a legitimate email address or noreply address

// Send the email
if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully.";
} else {
    echo "Email delivery failed.";
}