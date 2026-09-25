<?php
require_once "includes/mailer.php";
$result = sendMail("bestlinkcollegeoftheph@gmail.com", "Test User", "Test Subject", "<p>Test HTML body</p>");
var_dump($result);

