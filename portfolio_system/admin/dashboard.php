
<?php
session_start();

require_once "../db.php";

// Check if admin is logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit;
}

// COUNT PROJECTS
$project_count = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM projects");

if ($result) {
    $row = $result->fetch_assoc();
    $project_count = (int) $row["total"];
}

// COUNT MESSAGES
$message_count = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM messages");

if ($result) {
    $row = $result->fetch_assoc();
    $message_count = (int) $row["total"];
}

// GET PROJECTS
$projects = [];

$result = $conn->query(
    "SELECT id, title, description, technologies, project_link, created_at
     FROM projects
     ORDER BY created_at DESC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}

// Safe HTML output
function e($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Jan Carlo Estandarte</title>

    <link rel="stylesheet" href="../style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #09090f;
            color: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        .admin-header {
            background: #111118;
            color: white;
            padding: 24px 0;
            border-bottom: 1px solid #292936;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: auto;
        }

        .admin-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .admin-nav h2 {
            margin: 0 0 10px;
            font-size: 25px;
        }

        .admin-nav p {
            margin: 0;
            color: #b8b8c7;
        }

        .admin-nav a {
            display: inline-block;
            color: #e5e7eb;
            text-decoration: none;
            margin-left: 18px;
            padding: 8px 0;
        }

        .admin-nav a:hover {
            color: #a78bfa;
        }

        .dashboard {
            padding: 45px 0;
            min-height: 80vh;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: #ffffff;
            color: #222222;
            padding: 30px;
            text-align: center;
            border-radius: 14px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-card h3 {
            font-size: 42px;
            color: #2563eb;
            margin: 0 0 12px;
        }

        /* FIX: Make both names visible */
        .stat-card p {
            color: #222222;
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }

        .btn {
            display: inline-block;
            background: #7c3aed;
            color: #ffffff;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn:hover {
            background: #6d28d9;
        }

        .dashboard-title {
            margin-top: 35px;
            margin-bottom: 20px;
            color: #ffffff;
        }

        .project-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            color: #222222;
        }

        .project-table th,
        .project-table td {
            border: 1px solid #dddddd;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }

        .project-table th {
            background: #222222;
            color: #ffffff;
        }

        .project-table tr:hover {
            background: #f3f4f6;
        }

        .project-description {
            color: #555555;
            max-width: 350px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .project-link,
        .action-link {
            text-decoration: none;
            margin-right: 8px;
            font-weight: bold;
            color: #2563eb;
        }

        .project-link:hover,
        .action-link:hover {
            text-decoration: underline;
        }

        .delete-link {
            color: #dc2626;
        }

        .empty-message {
            background: #ffffff;
            color: #444444;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }

        .success-message {
            background: #dcfce7;
            border: 1px solid #86efac;
            color: #166534;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        @media (max-width: 800px) {
            .stats {
                grid-template-columns: 1fr;
            }

            .admin-nav {
                flex-direction: column;
                text-align: center;
            }

            .admin-nav a {
                margin: 5px 8px;
            }

            .project-table {
                font-size: 13px;
            }

            .container {
                width: 94%;
            }
        }
    </style>
</head>

<body>

    <!-- ADMIN HEADER -->
    <header class="admin-header">
        <div class="container admin-nav">

            <div>
                <h2>ESTANDARTE, JAN CARLO R.</h2>
                <p>WELCOME EVERYONE! 👋</p>
            </div>

            <nav>
                <a href="../index.php">View Portfolio</a>
                <a href="messages.php">Messages</a>
                <a href="../logout.php">Logout</a>
            </nav>

        </div>
    </header>

    <!-- DASHBOARD -->
    <main class="dashboard">
        <div class="container">

            <!-- SUCCESS MESSAGES -->
            <?php if (isset($_GET["added"])): ?>
                <div class="success-message">
                    ✓ Project added successfully!
                </div>
            <?php endif; ?>

            <?php if (isset($_GET["updated"])): ?>
                <div class="success-message">
                    ✓ Project updated successfully!
                </div>
            <?php endif; ?>

            <?php if (isset($_GET["deleted"])): ?>
                <div class="success-message">
                    ✓ Project deleted successfully!
                </div>
            <?php endif; ?>

            <!-- STATISTICS -->
            <div class="stats">

                <div class="stat-card">
                    <h3><?= $project_count ?></h3>
                    <p>Total Projects</p>
                </div>

                <div class="stat-card">
                    <h3><?= $message_count ?></h3>
                    <p>Total Messages</p>
                </div>

            </div>

            <!-- ADD PROJECT -->
            <div style="margin-bottom: 25px;">
                <a href="add_project.php" class="btn">
                    + Add New Project
                </a>
            </div>

            <h2 class="dashboard-title">Manage Projects</h2>

            <!-- PROJECT LIST -->
            <?php if (count($projects) > 0): ?>

                <div style="overflow-x: auto;">
                    <table class="project-table">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Technologies</th>
                                <th>Project Link</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <tr>

                                    <td>
                                        <?= (int) $project["id"] ?>
                                    </td>

                                    <td>
                                        <?= e($project["title"]) ?>
                                    </td>

                                    <td>
                                        <div class="project-description">
                                            <?= nl2br(e($project["description"])) ?>
                                        </div>
                                    </td>

                                    <td>
                                        <?= e($project["technologies"] ?? "") ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($project["project_link"])): ?>
                                            <a
                                                href="<?= e($project["project_link"]) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="project-link">
                                                View
                                            </a>
                                        <?php else: ?>
                                            <span>No link</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= e($project["created_at"]) ?>
                                    </td>

                                    <td>
                                        <a
                                            href="edit_project.php?id=<?= (int) $project["id"] ?>"
                                            class="action-link">
                                            Edit
                                        </a>

                                        <a
                                            href="delete_project.php?id=<?= (int) $project["id"] ?>"
                                            class="action-link delete-link"
                                            onclick="return confirm('Are you sure you want to delete this project?');">
                                            Delete
                                        </a>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

            <?php else: ?>

                <div class="empty-message">
                    <h3>No Projects Found</h3>

                    <p>You haven't added any projects yet.</p>

                    <a href="add_project.php" class="btn">
                        + Add Your First Project
                    </a>
                </div>

            <?php endif; ?>

        </div>
    </main>

</body>
</html>
