<?php
require_once "config/database.php";
try {
    $db = getDB();
    echo "Connected successfully to: " . $db->getAttribute(PDO::ATTR_CONNECTION_STATUS);
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
