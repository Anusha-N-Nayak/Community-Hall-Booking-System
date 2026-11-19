<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $hall_name = $_POST["hall_name"];
    $location = $_POST["location"];
    $capacity = $_POST["capacity"];
    $price = $_POST["price"];
    $facilities = $_POST["facilities"];
    $image = $_POST["image"];
    $description = $_POST["description"];

    $sql = "INSERT INTO halls