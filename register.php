<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Konfiguracia PDO
require_once('config.php');
// Kniznica pre 2FA
require_once ("GoogleAuthenticator/PHPGangsta/GoogleAuthenticator.php");

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo $e->getMessage();
}

// ------- Pomocne funkcie -------
function checkEmpty($field) {
    // Funkcia pre kontrolu, ci je premenna po orezani bielych znakov prazdna.
    // Metoda trim() oreze a odstrani medzery, tabulatory a ine "whitespaces".
    if (empty(trim($field))) {
        return true;
    }
    return false;
}

function checkLength($field, $min, $max) {
    // Funkcia, ktora skontroluje, ci je dlzka retazca v ramci "min" a "max".
    // Pouzitie napr. pre "login" alebo "password" aby mali pozadovany pocet znakov.
    $string = trim($field);     // Odstranenie whitespaces.
    $length = strlen($string);      // Zistenie dlzky retazca.
    if ($length < $min || $length > $max) {
        return false;
    }
    return true;
}

function checkUsername($username) {
    // Funkcia pre kontrolu, ci username obsahuje iba velke, male pismena, cisla a podtrznik.
    if (!preg_match('/^[a-zA-Z0-9_]+$/', trim($username))) {
        return false;
    }
    return true;
}

function checkGmail($email) {
    // Funkcia pre kontrolu, ci zadany email je gmail.
    if (!preg_match('/^[\w.+\-]+@gmail\.com$/', trim($email))) {
        return false;
    }
    return true;
}

function userExist($db, $login, $email) {
    // Funkcia pre kontrolu, ci pouzivatel s "login" alebo "email" existuje.
    $exist = false;

    $param_login = trim($login);
    $param_email = trim($email);

    $query = "SELECT id FROM users where (login = '" .  $login . "') or (email = '" .  $email . "')";
    $stmt = $db->query($query); 
    $persons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    

   if(count($persons)==0){
    $exist=false;
   }else{
    $exist = true;
   }
    return $exist;
}

// ------- ------- ------- -------



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errmsg = "";

    // Validacia username
    if (checkEmpty($_POST['login']) === true) {
        $errmsg .= "<p>Zadajte login.</p>";
    } elseif (checkLength($_POST['login'], 6,32) === false) {
        $errmsg .= "<p>Login musi mat min. 6 a max. 32 znakov.</p>";
    } elseif (checkUsername($_POST['login']) === false) {
        $errmsg .= "<p>Login moze obsahovat iba velke, male pismena, cislice a podtrznik.</p>";
    }

    // Kontrola pouzivatela
    if (userExist($db, $_POST['login'], $_POST['email']) === true) {
        $errmsg .= "Pouzivatel s tymto e-mailom / loginom uz existuje.</p>";
    }

    // Validacia mailu
    if (checkGmail($_POST['email'])) {
        $errmsg .= "Prihlaste sa pomocou Google prihlasenia";
        // Ak pouziva google mail, presmerujem ho na prihlasenie cez Google.
        // header("Location: google_login.php");
    }

    // TODO: Validacia hesla
    // TODO: Validacia mena, priezviska

    if (empty($errmsg)) {
        $sql = "INSERT INTO users (fullname, login, email, password, 2fa_code) VALUES (:fullname, :login, :email, :password, :2fa_code)";

        $fullname = $_POST['firstname'] . ' ' . $_POST['lastname'];
        $email = $_POST['email'];
        $login = $_POST['login'];
        $hashed_password = password_hash($_POST['password'], PASSWORD_ARGON2ID);

        // 2FA pomocou PHPGangsta kniznice: https://github.com/PHPGangsta/GoogleAuthenticator
        $g2fa = new PHPGangsta_GoogleAuthenticator();
        $user_secret = $g2fa->createSecret();
        $codeURL = $g2fa->getQRCodeGoogleUrl('Olympic Games', $user_secret);
        $_SESSION['auth_key'] = $user_secret;

        // Bind parametrov do SQL
        $stmt = $db->prepare($sql);

        $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":login", $login, PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(":2fa_code", $user_secret, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // qrcode je premenna, ktora sa vykresli vo formulari v HTML.
            $qrcode = $codeURL;
        } else {
            echo "Ups. Nieco sa pokazilo";
        }

        unset($stmt);
    }
    unset($db);
}

?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.3/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link  rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <title>Login/register s 2FA - Register</title>
    <style>
        .container{
            margin: auto;
            width: 70%;
            border: 3px solid green;
            padding: 10px;
            text-align: center;
            font-size: 20px;
        }
        h1{
            text-align: center;
        }
        input {
        padding: 10px;
        margin: 5px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark" aria-label="navbar">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample10" aria-controls="navbarsExample10" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample10" <?php if(isset($_SESSION['access_token']) && $_SESSION['access_token']){echo "style='margin-left: 10px'";} elseif (isset($_SESSION['loggedin']) && $_SESSION['loggedin']){echo "style='margin-left: 1px'";}else{echo "style='margin-left: 1px'";}?>>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Domov</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Prihlásenie</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="secured.php">Úprava</a>
                </li>
            </ul>
            
        </div>
        <span class="navbar-text" <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']){echo "style='margin-right: 100px'";}?>>
                        <a class="nav-link" style="color: rgba(124,252,0,100)" href="odhlasenie.php"><strong><?php if(isset($_SESSION['login'])){echo $_SESSION['login'];};?></strong></a>
            </span>
    </div>
</nav>
<header>
    <hgroup>
        <h1>Registrácia - Vytvorenie nového konta používateľa</h1>
    </hgroup>
</header>
<main>
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <label for="firstname">
                Meno:
                <input type="text" name="firstname" value="" id="firstname" placeholder="napr. Jonatan" >
            </label>

            <label for="lastname">
                Priezvisko:
                <input type="text" name="lastname" value="" id="lastname" placeholder="napr. Petrzlen" >
            </label>

            <br>

            <label for="email">
                E-mail:
                <input type="email" name="email" value="" id="email" placeholder="napr. jpetrzlen@example.com" >
            </label>

            <label for="login">
                Login:
                <input type="text" name="login" value="" id="login" placeholder="napr. jperasin" >
            </label>

            <br>

            <label for="password">
                Heslo:
                <input type="password" name="password" value="" id="password" >
            </label>

            <button class="button" type="submit">Vytvoriť konto</button>

            <?php
            if (!empty($errmsg)) {
                // Tu vypis chybne vyplnene polia formulara.
                echo $errmsg;
            }
            if (isset($qrcode)) {
                // Pokial bol vygenerovany QR kod po uspesnej registracii, zobraz ho.
                $message = '<p>Naskenujte QR kod do aplikacie Authenticator pre 2FA: <br><img src="'.$qrcode.'" alt="qr kod pre aplikaciu authenticator"></p>';

                echo $message;
                echo '<p>Teraz sa mozte prihlasit: <a href="login.php" class="button" role="button">Login</a></p>';
            }
            ?>

        </form>
        <p>Máte vytvorené konto? <a href="login.php" role="button" class="button">Prihláste sa tu.</a></p>
    </div>
</main>
</body>
</html>