<?php
session_start();
session_unset();      // clear all session variables
session_destroy();    // destroy the session itself
header("Location: login.php");
exit();
