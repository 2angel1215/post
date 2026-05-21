<?php

$conn = mysqli_connect("localhost","root","","K_KNOCK");

if(!$conn) {
    die("db 연결 실패".mysqli_connect_error());
}
?>