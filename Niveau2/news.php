<?php
session_start();
if (!isset($_SESSION['connected_id'])) {
    header('Location: login.php');
    exit();
}

// Assurez-vous que $_SESSION['likes'] est un tableau
if (!isset($_SESSION['likes']) || !is_array($_SESSION['likes'])) {
    $_SESSION['likes'] = array();
}

// Traitement du formulaire de like
if (isset($_POST['like']) && isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];
    if (!isset($_SESSION['likes'][$post_id])) {
        $_SESSION['likes'][$post_id] = 0;
    }
    $_SESSION['likes'][$post_id]++;
    // Redirection après le traitement du formulaire pour éviter la resoumission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "socialnetwork");
if ($mysqli->connect_errno) {
    echo "<article>";
    echo ("Échec de la connexion : " . $mysqli->connect_error);
    echo ("<p>Indice: Vérifiez les paramètres de <code>new mysqli(...</code></p>");
    echo "</article>";
    exit();
}

// Récupérer les derniers messages
$laQuestionEnSql = "
    SELECT posts.content,
           posts.created,
           users.alias as author_name, 
           users.id as user_id, 
           posts.id as post_id, 
           count(likes.id) as like_number,  
           GROUP_CONCAT(DISTINCT tags.label) AS taglist 
    FROM posts
    JOIN users ON users.id = posts.user_id
    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
    LEFT JOIN tags ON posts_tags.tag_id = tags.id 
    LEFT JOIN likes ON likes.post_id = posts.id 
    GROUP BY posts.id
    ORDER BY posts.created DESC  
    LIMIT 5
";

$lesInformations = $mysqli->query($laQuestionEnSql);
if (!$lesInformations) {
    echo "<article>";
    echo ("Échec de la requête : " . $mysqli->error);
    echo ("<p>Indice: Vérifiez la requête SQL suivante dans phpmyadmin<code>$laQuestionEnSql</code></p>");
    echo "</article>";
    exit();
}
?>


<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>ReSoC - Actualités</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css" />
</head>

<body>


<head>
    <meta charset="utf-8">
    <title>ReSoC - Actualités</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css" />
</head>

<body>

    <?php include 'header.php'; ?>

    <div id="wrapper">
        <aside>
            <img src="user.jpg" alt="Portrait de l'utilisatrice" />
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez les derniers messages de
                    tous les utilisatrices du site.</p>
            </section>
        </aside>
        <main>
            <?php
            while ($post = $lesInformations->fetch_assoc()) {
                $post_id = $post['post_id'];
                $user_id = $post['user_id'];
                $like_number = isset($_SESSION['likes'][$post_id]) ? $_SESSION['likes'][$post_id] : 0;
                ?>

                <article>
                    <h3>
                        <time><?php echo $post['created'] ?></time>
                    </h3>
                    <address><a target="_blank" href="wall.php?user_id=<?php echo intval($user_id) ?>"><?php echo $post['author_name'] ?></a></address>
                    <div>
                        <?php echo $post['content'] ?>
                    </div>
                    <footer>
                        <small>♥ <?php echo $like_number; ?></small>

                        <form method="POST" action="">
                            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                            <input type="submit" name="like" value="Ajouter un like">
                        </form>

                        <a href=""><?php echo $post['taglist'] ?></a>
                    </footer>
                </article>
                <?php
            }
            ?>
        </main>
    </div>
</body>

</html>