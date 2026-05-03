<?php
// User name: Secret 
$password = "Sherwin123";

$hash = password_hash($password, PASSWORD_DEFAULT);

echo $hash;

?>