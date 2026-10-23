<?php

session_start();

require_once "db.php";

$error = "";

/* If already logged in */
if (isset($_SESSION["admin_id"])) {
    header("Location: admin/dashboard.php");
    exit;
}

/* Login */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        $error = "Please enter username and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, username, password
             FROM admins
             WHERE username = ?
             LIMIT 1"
        );

        if ($stmt) {

            $stmt->bind_param("s", $username);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $admin = $result->fetch_assoc();

                /*
                 * Database password:
                 * SHA2('12345', 256)
                 */
                $hashed_password = hash(
                    "sha256",
                    $password
                );

                if (
                    hash_equals(
                        $admin["password"],
                        $hashed_password
                    )
                ) {

                    session_regenerate_id(true);

                    $_SESSION["admin_id"] =
                        $admin["id"];

                    $_SESSION["admin_username"] =
                        $admin["username"];

                    header(
                        "Location: admin/dashboard.php"
                    );

                    exit;

                } else {

                    $error =
                        "Invalid username or password.";

                }

            } else {

                $error =
                    "Invalid username or password.";
            }

            $stmt->close();

        } else {

            $error =
                "Database query error.";
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Login</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            font-family: Arial, sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #080b14,
                    #17112b
                );

            color: white;
        }

        .login-box {

            width: 90%;

            max-width: 420px;

            padding: 40px;

            background: #111827;

            border: 1px solid #334155;

            border-radius: 20px;

            box-shadow:
                0 20px 60px rgba(0,0,0,0.5);
        }

        .logo {

            text-align: center;

            font-size: 30px;

            font-weight: bold;

            margin-bottom: 10px;
        }

        .logo span {

            color: #8b5cf6;
        }

        .subtitle {

            text-align: center;

            color: #94a3b8;

            margin-bottom: 30px;
        }

        label {

            display: block;

            margin-bottom: 8px;

            color: #e2e8f0;

            font-weight: bold;
        }

        input {

            width: 100%;

            padding: 14px;

            margin-bottom: 20px;

            background: #080b14;

            border: 1px solid #334155;

            border-radius: 8px;

            color: white;

            font-size: 15px;

            outline: none;
        }

        input:focus {

            border-color: #8b5cf6;
        }

        button {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 8px;

            background: #8b5cf6;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;
        }

        button:hover {

            background: #7c3aed;
        }

        .error {

            background: #3f1515;

            border: 1px solid #ef4444;

            color: #fca5a5;

            padding: 12px;

            border-radius: 8px;

            text-align: center;

            margin-bottom: 20px;
        }

        .back {

            display: block;

            text-align: center;

            margin-top: 25px;

            color: #94a3b8;

            text-decoration: none;
        }

        .back:hover {

            color: #a78bfa;
        }

    </style>

</head>

<body>


<div class="login-box">

    <div class="logo">

        <span>&lt;</span>
        ADMIN
        <span>/&gt;</span>

    </div>

    <p class="subtitle">
        Portfolio Management System
    </p>


    <?php if ($error !== ""): ?>

        <div class="error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <form method="POST" action="login.php">

        <label for="username">
            Username
        </label>

        <input
            type="text"
            id="username"
            name="username"
            placeholder="Enter username"
            required
        >


        <label for="password">
            Password
        </label>

        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter password"
            required
        >


        <button type="submit">
            Login
        </button>

    </form>


    <a href="index.php" class="back">
        ← Back to Portfolio
    </a>

</div>


</body>

</html>
