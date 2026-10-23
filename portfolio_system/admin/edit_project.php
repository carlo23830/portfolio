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


$error = "";


// Get existing project

$stmt = $conn->prepare(
    "SELECT id, title, description, technologies, project_link
     FROM projects
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: dashboard.php");
    exit;
}

$project = $result->fetch_assoc();

$stmt->close();


// Update project

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $technologies = trim($_POST["technologies"] ?? "");
    $project_link = trim($_POST["project_link"] ?? "");


    if ($title === "" || $description === "") {

        $error = "Title and description are required.";

    } elseif (strlen($title) > 150) {

        $error = "Project title is too long.";

    } elseif (
        $project_link !== "" &&
        !filter_var($project_link, FILTER_VALIDATE_URL)
    ) {

        $error = "Please enter a valid project URL.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE projects
             SET title = ?,
                 description = ?,
                 technologies = ?,
                 project_link = ?
             WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param(
                "ssssi",
                $title,
                $description,
                $technologies,
                $project_link,
                $id
            );

            if ($stmt->execute()) {

                header("Location: dashboard.php");
                exit;

            } else {

                $error = "Unable to update the project.";
            }

            $stmt->close();

        } else {

            $error = "A database error occurred.";
        }
    }

    // Keep submitted values in form
    $project["title"] = $title;
    $project["description"] = $description;
    $project["technologies"] = $technologies;
    $project["project_link"] = $project_link;
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

    <title>Edit Project</title>

    <link rel="stylesheet" href="../style.css">

    <style>

        .form-container {
            max-width: 750px;
            margin: 50px auto;
        }

        .form-container label {
            display: block;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        .form-container input,
        .form-container textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-container textarea {
            min-height: 150px;
        }

        .form-container button {
            margin-top: 20px;
        }

    </style>

</head>

<body>

<div class="form-container">

    <h1>Edit Project</h1>

    <br>


    <?php if ($error): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <label for="title">
            Project Title
        </label>

        <input
            type="text"
            id="title"
            name="title"
            maxlength="150"
            value="<?= htmlspecialchars($project["title"]) ?>"
            required
        >


        <label for="description">
            Description
        </label>

        <textarea
            id="description"
            name="description"
            required
        ><?= htmlspecialchars($project["description"]) ?></textarea>


        <label for="technologies">
            Technologies
        </label>

        <input
            type="text"
            id="technologies"
            name="technologies"
            maxlength="255"
            value="<?= htmlspecialchars($project["technologies"]) ?>"
        >


        <label for="project_link">
            Project Link
        </label>

        <input
            type="url"
            id="project_link"
            name="project_link"
            maxlength="255"
            value="<?= htmlspecialchars($project["project_link"]) ?>"
        >


        <button type="submit" class="btn">
            Save Changes
        </button>

        <a href="dashboard.php" class="btn">
            Cancel
        </a>

    </form>

</div>

</body>
</html>
