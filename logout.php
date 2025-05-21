<?php
session_start();
session_destroy();
header("Location: action.html?msg=You%20have%20logged%20out%20successfully!&redirect=login_sign.html");
exit();
?>
