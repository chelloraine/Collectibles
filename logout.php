<?php
session_start();
session_unset();  
session_destroy(); 
header("Location: http://localhost/website/LOGIN/loginpage.php"); // Redirect to login page
exit;
?>
