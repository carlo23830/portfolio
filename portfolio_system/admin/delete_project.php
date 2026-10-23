<?php

session_start();

require_once "../db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit;
}


$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {
    header("Location: dashboard.php");
    exit;
}


$stmt = $conn->prepare(
    "DELETE FROM projects WHERE id = ?"
);

if ($stmt) {

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}


header("Location: dashboard.php");
exit;

?>
