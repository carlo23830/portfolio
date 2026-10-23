
<?php
require_once "db.php";

$message_sent = false;
$error_message = "";

// HANDLE CONTACT FORM
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name === "" || $email === "" || $message === "") {
        $error_message = "Please fill in all fields.";
    } elseif (strlen($name) > 100 || strlen($email) > 150) {
        $error_message = "Name or email is too long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {

        $stmt = $conn->prepare(
            "INSERT INTO messages (name, email, message)
             VALUES (?, ?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param("sss", $name, $email, $message);

            if ($stmt->execute()) {
                $message_sent = true;
            } else {
                $error_message = "Unable to send your message. Please try again.";
            }

            $stmt->close();
        } else {
            $error_message = "A system error occurred. Please try again.";
        }
    }
}

// GET PROJECTS FROM DATABASE
$projects = [];

$result = $conn->query(
    "SELECT id, title, description, technologies, project_link
     FROM projects
     ORDER BY created_at DESC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}

// SAFE HTML OUTPUT
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="theme-color" content="#09090f">
    <meta name="description"
          content="Personal portfolio of ESTANDARTE, JAN CARLO R., BSIT student and aspiring web developer.">

    <title>Jan Carlo Estandarte | Portfolio</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<!-- ================= NAVBAR ================= -->

<header class="navbar">
    <div class="container nav-content">

        <h1 class="logo">JC<span class="logo-accent">.</span></h1>

        <nav aria-label="Main navigation">
            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#projects">Projects</a>
            <a href="#contact">Contact</a>
            <a href="login.php">Admin</a>
        </nav>

    </div>
</header>


<main>

    <!-- ================= HOME ================= -->

    <section id="home" class="hero">
        <div class="container">

            <p class="hero-tag">WELCOME TO MY PORTFOLIO</p>

            <h2>Hello, I'm<br>ESTANDARTE, JAN CARLO R.</h2>

            <h2 class="hero-subtitle">
                BSIT 2-9 | KUNWARI PROGRAMMER
            </h2>

            <p>
                Hi! I'm Jan Carlo, a BSIT student who loves coding.
    Sometimes the code works perfectly, and other times, it doesn't. I don't know why but 
    I'm still learning, so please don't judge my bugs!🪲🪲🪲
            </p>
           

            <div class="hero-actions">

                <a href="#projects" class="btn">
                    View My Projects ↗
                </a>

                <a href="#contact" class="btn btn-outline">
                    Contact Me
                </a>

            </div>

            <div class="hero-stats">

                <div class="stat">
                    <strong><?= count($projects) ?></strong>
                    <span>Projects</span>
                </div>

                <div class="stat">
                    <strong>04</strong>
                    <span>Core Technologies</span>
                </div>

                <div class="stat">
                    <strong>BSIT 2-9</strong>
                    <span>Student of PHINMA UNIVERSITY of ILOILO</span>
                </div>

            </div>

        </div>
    </section>


    <!-- ================= ABOUT ================= -->

    <section id="about">
        <div class="container">

            <p class="section-label">GET TO KNOW ME</p>

            <h2>About Me</h2>

            <p class="about-description">
                Hello! I am Jan Carlo Estandarte, a Bachelor of Science
                in Information Technology student learning programming
                and web development. I am interested in designing
                responsive websites, building dynamic applications,
                and understanding database management.
                This portfolio showcases my projects and developing skills.
            </p>

            <div class="skills">

                <article class="skill">
                    <span class="skill-icon">&lt;/&gt;</span>
                    <h3>HTML</h3>
                    <p>Creating structured and accessible web pages.</p>
                </article>

                <article class="skill">
                    <span class="skill-icon">✦</span>
                    <h3>CSS</h3>
                    <p>Designing modern and responsive interfaces.</p>
                </article>

                <article class="skill">
                    <span class="skill-icon">PHP</span>
                    <h3>PHP</h3>
                    <p>Building dynamic web applications.</p>
                </article>

                <article class="skill">
                    <span class="skill-icon">DB</span>
                    <h3>MySQL</h3>
                    <p>Managing and organizing application data.</p>
                </article>

            </div>

        </div>
    </section>


    <!-- ================= PROJECTS ================= -->

    <section id="projects" class="gray-section">
        <div class="container">

            <p class="section-label">MY RECENT WORK</p>

            <h2>My Projects</h2>

            <p class="section-description">
                Explore the projects stored in my portfolio database.
            </p>

            <div class="project-grid">

                <?php if (count($projects) > 0): ?>

                    <?php foreach ($projects as $project): ?>

                        <article class="project-card">

                            <p class="project-number">
                                PROJECT <?= e($project["id"]) ?>
                            </p>

                            <h3>
                                <?= e($project["title"]) ?>
                            </h3>

                            <p class="project-description">
                                <?= nl2br(e($project["description"])) ?>
                            </p>

                            <?php if (!empty($project["technologies"])): ?>

                                <div class="project-tech">
                                    <span>TECHNOLOGIES</span>
                                    <p>
                                        <?= e($project["technologies"]) ?>
                                    </p>
                                </div>

                            <?php endif; ?>

                            <?php if (!empty($project["project_link"])): ?>

                                <a
                                    href="<?= e($project["project_link"]) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn project-btn"
                                >
                                    View Project ↗
                                </a>

                            <?php endif; ?>

                        </article>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-projects">
                        <span class="empty-icon">◇</span>

                        <h3>No Projects Yet</h3>

                        <p>
                            Projects will appear here when they are
                            added to your portfolio database.
                        </p>
                    </div>

                <?php endif; ?>

            </div>

        </div>
    </section>


    <!-- ================= CONTACT ================= -->

    <section id="contact">
        <div class="container">

            <p class="section-label">LET'S CONNECT</p>

            <h2>Contact Me</h2>

            <p class="section-description">
                Have a question or want to get in touch?
                Send me a message using the form below.
            </p>

            <?php if ($message_sent): ?>

                <div class="success" role="status">
                    Your message has been sent successfully!
                </div>

            <?php endif; ?>

            <?php if ($error_message !== ""): ?>

                <div class="error" role="alert">
                    <?= e($error_message) ?>
                </div>

            <?php endif; ?>

            <form
                method="POST"
                action="index.php#contact"
                class="contact-form"
            >

                <label for="name">Your Name</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    maxlength="100"
                    placeholder="Enter your full name"
                    autocomplete="name"
                    value="<?= !$message_sent ? e($_POST["name"] ?? "") : "" ?>"
                    required
                >

                <label for="email">Email Address</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    maxlength="150"
                    placeholder="you@example.com"
                    autocomplete="email"
                    value="<?= !$message_sent ? e($_POST["email"] ?? "") : "" ?>"
                    required
                >

                <label for="message">Your Message</label>

                <textarea
                    id="message"
                    name="message"
                    rows="6"
                    placeholder="Write your message here..."
                    required
                ><?= !$message_sent ? e($_POST["message"] ?? "") : "" ?></textarea>

                <button type="submit" class="btn">
                    Send Message ↗
                </button>

            </form>

        </div>
    </section>

</main>


<!-- ================= FOOTER ================= -->

<footer>
    <div class="container footer-content">

        <p>
            &copy; <?= date("Y") ?>
            <strong>ESTANDARTE, JAN CARLO R.</strong>
        </p>

        <p class="footer-subtitle">
            Designed with passion and creativity.
        </p>

    </div>
</footer>

</body>
</html>