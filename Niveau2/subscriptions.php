<?php
session_start();
if (!isset($_SESSION['connected_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $connected_id = $_SESSION['connected_id'];

    $mysqli = new mysqli("localhost", "root", "", "socialnetwork");

    $laQuestionEnSql = "INSERT INTO followers (followed_user_id, following_user_id) VALUES (?,?)";
    $stmt = $mysqli->prepare($laQuestionEnSql);
    $stmt->bind_param("ii", $user_id, $connected_id);

    if ($stmt->execute()) {
        echo "Vous êtes maintenant abonné à cet utilisateur.";
    } else {
        echo "Erreur lors de l'abonnement.";
    }

    $stmt->close();
    header('Location: wall.php?user_id='. $user_id);
    exit();
}

?>
<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Mes abonnements</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
       
    <?php include 'headerdeco.php'; ?>

        <div id="wrapper">
            <aside>
                <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez la liste des personnes dont
                        l'utilisatrice
                        n° <?php echo intval($_GET['user_id']) ?>
                        suit les messages
                    </p>

                </section>
            </aside>
            <main class='contacts'>
                <?php
                // Etape 1: récupérer l'id de l'utilisateur
                $userId = intval($_GET['user_id']);
                // Etape 2: se connecter à la base de donnée
                $mysqli2 = new mysqli("localhost", "root", "", "socialnetwork");
                // Etape 3: récupérer le nom de l'utilisateur
                $laQuestionEnSql = "
                    SELECT users.* 
                    FROM followers 
                    LEFT JOIN users ON users.id=followers.followed_user_id 
                    WHERE followers.following_user_id='$userId'
                    GROUP BY users.id
                    ";
                $lesInformations = $mysqli2->query($laQuestionEnSql);
                // Etape 4: à vous de jouer
                //@todo: faire la boucle while de parcours des abonnés et mettre les bonnes valeurs ci dessous 

              

                while ($post = $lesInformations->fetch_assoc())
                {
                    /* echo "<pre>" . print_r($post, 1) . "</pre>"; */
                ?>
                <article>
                    <img src="user.jpg" alt="blason"/>
                    <h3><?php echo $post['alias'] ?></h3>
                    <p><?php echo $post['id'] ?></p>                    
                </article> 
                <?php } ?>
            </main>
        </div>
    </body>
</html>