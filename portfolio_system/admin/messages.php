
<?php
session_start();
require_once "../db.php";

/* Check if admin is logged in */
if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit;
}

/* Create security token */
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

/* Safe HTML output */
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$notice = "";
$error = "";

/* Handle Edit and Delete */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        exit("Invalid request. Please refresh the page.");
    }

    $id = filter_var(
        $_POST["id"] ?? null,
        FILTER_VALIDATE_INT
    );

    $action = $_POST["action"] ?? "";

    if (!$id || $id < 1) {
        header("Location: messages.php");
        exit;
    }

    /* UPDATE MESSAGE */
    if ($action === "update") {

        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $message = trim($_POST["message"] ?? "");

        if (
            $name === "" ||
            strlen($name) > 255 ||
            strlen($email) > 255 ||
            $message === "" ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            $_SESSION["message_error"] =
                "Please enter a valid name, email, and message.";

            header("Location: messages.php?edit=" . $id);
            exit;
        }

        $stmt = $conn->prepare(
            "UPDATE messages
             SET name = ?, email = ?, message = ?
             WHERE id = ?"
        );

        if (!$stmt) {
            exit("Unable to prepare the update request.");
        }

        $stmt->bind_param(
            "sssi",
            $name,
            $email,
            $message,
            $id
        );

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: messages.php?updated=1");
            exit;
        }

        $stmt->close();

        $_SESSION["message_error"] =
            "Unable to update the message.";

        header("Location: messages.php?edit=" . $id);
        exit;
    }

    /* DELETE MESSAGE */
    if ($action === "delete") {

        $stmt = $conn->prepare(
            "DELETE FROM messages WHERE id = ?"
        );

        if (!$stmt) {
            exit("Unable to prepare the delete request.");
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        if ($deleted) {
            header("Location: messages.php?deleted=1");
        } else {
            header("Location: messages.php?notfound=1");
        }

        exit;
    }

    header("Location: messages.php");
    exit;
}

/* Show notifications */
$error = $_SESSION["message_error"] ?? "";
unset($_SESSION["message_error"]);

if (isset($_GET["updated"])) {
    $notice = "Message updated successfully!";
} elseif (isset($_GET["deleted"])) {
    $notice = "Message deleted successfully!";
} elseif (isset($_GET["notfound"])) {
    $notice = "Message was not found or was already deleted.";
}

/* Get selected edit ID */
$edit_id = filter_input(
    INPUT_GET,
    "edit",
    FILTER_VALIDATE_INT
);

if (!$edit_id || $edit_id < 1) {
    $edit_id = 0;
}

/* Get messages */
$messages = [];

$stmt = $conn->prepare(
    "SELECT id, name, email, message, created_at
     FROM messages
     ORDER BY created_at DESC"
);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    $stmt->close();
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

    <title>Messages | Admin</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #080b14;
            color: white;
            min-height: 100vh;
        }

        .navbar {
            background: #111827;
            border-bottom: 1px solid #334155;
            padding: 20px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
        }

        .logo span {
            color: #8b5cf6;
        }

        .back {
            color: #cbd5e1;
            text-decoration: none;
            padding: 10px 16px;
            border: 1px solid #334155;
            border-radius: 8px;
        }

        .back:hover {
            border-color: #8b5cf6;
            color: #a78bfa;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 50px auto;
        }

        h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #94a3b8;
            margin-bottom: 35px;
        }

        .messages {
            display: grid;
            gap: 20px;
        }

        .message-card {
            background: #111827;
            border: 1px solid #334155;
            border-radius: 15px;
            padding: 25px;
            transition: border-color 0.3s;
        }

        .message-card:hover {
            border-color: #8b5cf6;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 20px;
        }

        .sender {
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 0;
        }

        .avatar {
            width: 50px;
            height: 50px;
            flex-shrink: 0;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
        }

        .sender h3 {
            color: white;
            margin-bottom: 3px;
            overflow-wrap: anywhere;
        }

        .sender a {
            color: #a78bfa;
            font-size: 14px;
            text-decoration: none;
            overflow-wrap: anywhere;
        }

        .date {
            color: #64748b;
            font-size: 13px;
            white-space: nowrap;
        }

        .message-text {
            background: #080b14;
            border-left: 3px solid #8b5cf6;
            padding: 18px;
            border-radius: 8px;
            color: #cbd5e1;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            line-height: 1.7;
        }

        .message-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
            flex-wrap: wrap;
        }

        .action-btn {
            display: inline-block;
            padding: 10px 16px;
            border: none;
            border-radius: 7px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            font-family: Arial, sans-serif;
        }

        .edit-btn {
            background: #7c3aed;
            color: white;
        }

        .delete-btn {
            background: #dc2626;
            color: white;
        }

        .cancel-btn {
            background: #334155;
            color: white;
        }

        .action-btn:hover {
            opacity: 0.85;
        }

        .edit-form {
            margin-top: 20px;
            padding: 20px;
            background: #080b14;
            border: 1px solid #334155;
            border-radius: 10px;
        }

        .edit-form h3 {
            margin-bottom: 15px;
        }

        .edit-form label {
            display: block;
            margin: 15px 0 7px;
            color: #cbd5e1;
        }

        .edit-form input,
        .edit-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #475569;
            border-radius: 7px;
            background: #111827;
            color: white;
            font: inherit;
        }

        .edit-form textarea {
            min-height: 140px;
            resize: vertical;
        }

        .notice,
        .error {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .notice {
            background: #14532d;
            border: 1px solid #166534;
            color: #dcfce7;
        }

        .error {
            background: #7f1d1d;
            border: 1px solid #991b1b;
            color: #fee2e2;
        }

        .empty {
            text-align: center;
            padding: 80px 20px;
            border: 1px dashed #334155;
            border-radius: 15px;
        }

        .empty-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .empty h2 {
            margin-bottom: 10px;
        }

        .empty p {
            color: #64748b;
        }

        @media (max-width: 600px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .message-header {
                flex-direction: column;
            }

            .date {
                white-space: normal;
            }

            .container {
                width: 94%;
                margin: 30px auto;
            }

            h1 {
                font-size: 29px;
            }

            .message-card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">

    <div class="logo">
        <span>&lt;</span>
        Portfolio Admin
        <span>/&gt;</span>
    </div>

    <a href="dashboard.php" class="back">
        ← Dashboard
    </a>

</header>

<main class="container">

    <h1>Contact Messages</h1>

    <p class="subtitle">
        Messages submitted by visitors through your portfolio.
    </p>

    <?php if ($notice !== ""): ?>
        <div class="notice">
            <?= e($notice) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="messages">

        <?php if (count($messages) > 0): ?>

            <?php foreach ($messages as $msg): ?>

                <article class="message-card">

                    <div class="message-header">

                        <div class="sender">

                            <div class="avatar">
                                <?= e(strtoupper(substr($msg["name"], 0, 1))) ?>
                            </div>

                            <div>

                                <h3>
                                    <?= e($msg["name"]) ?>
                                </h3>

                                <a href="mailto:<?= e($msg["email"]) ?>">
                                    <?= e($msg["email"]) ?>
                                </a>

                            </div>

                        </div>

                        <div class="date">
                            <?= e($msg["created_at"]) ?>
                        </div>

                    </div>

                    <div class="message-text"><?= e($msg["message"]) ?></div>

                    <!-- EDIT AND DELETE BUTTONS -->
                    <div class="message-actions">

                        <a
                            class="action-btn edit-btn"
                            href="messages.php?edit=<?= (int)$msg["id"] ?>"
                        >
                            Edit
                        </a>

                        <form
                            method="POST"
                            action="messages.php"
                            onsubmit="return confirm('Are you sure you want to permanently delete this message?');"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= e($_SESSION["csrf_token"]) ?>"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int)$msg["id"] ?>"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="delete"
                            >

                            <button
                                type="submit"
                                class="action-btn delete-btn"
                            >
                                Delete
                            </button>

                        </form>

                    </div>

                    <!-- EDIT FORM ON THE SAME PAGE -->
                    <?php if ($edit_id === (int)$msg["id"]): ?>

                        <form
                            method="POST"
                            action="messages.php"
                            class="edit-form"
                        >

                            <h3>Edit Message</h3>

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= e($_SESSION["csrf_token"]) ?>"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int)$msg["id"] ?>"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="update"
                            >

                            <label for="name-<?= (int)$msg["id"] ?>">
                                Name
                            </label>

                            <input
                                id="name-<?= (int)$msg["id"] ?>"
                                type="text"
                                name="name"
                                maxlength="255"
                                required
                                value="<?= e($msg["name"]) ?>"
                            >

                            <label for="email-<?= (int)$msg["id"] ?>">
                                Email
                            </label>

                            <input
                                id="email-<?= (int)$msg["id"] ?>"
                                type="email"
                                name="email"
                                maxlength="255"
                                required
                                value="<?= e($msg["email"]) ?>"
                            >

                            <label for="message-<?= (int)$msg["id"] ?>">
                                Message
                            </label>

                            <textarea
                                id="message-<?= (int)$msg["id"] ?>"
                                name="message"
                                required
                            ><?= e($msg["message"]) ?></textarea>

                            <div class="message-actions">

                                <button
                                    type="submit"
                                    class="action-btn edit-btn"
                                >
                                    Save Changes
                                </button>

                                <a
                                    href="messages.php"
                                    class="action-btn cancel-btn"
                                >
                                    Cancel
                                </a>

                            </div>

                        </form>

                    <?php endif; ?>

                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty">

                <div class="empty-icon">💬</div>

                <h2>No Messages Yet</h2>

                <p>
                    Messages from your contact form
                    will appear here.
                </p>

            </div>

        <?php endif; ?>

    </div>

</main>

</body>
</html>
