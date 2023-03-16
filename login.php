<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$warn1 = '';
$warn2 = '';
$warn3 = '';
$warn4 = '';
session_start();


if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true ){
    header("location: odhlasenie.php");
    exit;
}


require_once "config.php";
require_once 'vendor/autoload.php';
require_once "GoogleAuthenticator/PHPGangsta/GoogleAuthenticator.php";

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo $e->getMessage();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // TODO: Skontrolovat ci login a password su zadane (podobne ako v register.php).

    $sql = "SELECT fullname, email, login, password, created_at, 2fa_code FROM users WHERE login = :login";

    $stmt = $db->prepare($sql);

    // TODO: Upravit SQL tak, aby mohol pouzivatel pri logine zadat login aj email.
    $stmt->bindParam(":login", $_POST["login"], PDO::PARAM_STR);

    if ($stmt->execute()) {
        if ($stmt->rowCount() == 1) {
            // Uzivatel existuje, skontroluj heslo.
            $row = $stmt->fetch();
            $hashed_password = $row["password"];

            if (password_verify($_POST['password'], $hashed_password)) {
                // Heslo je spravne.
                $g2fa = new PHPGangsta_GoogleAuthenticator();
                if ($g2fa->verifyCode($row["2fa_code"], $_POST['2fa'], 2)) {
                    // Heslo aj kod su spravne, pouzivatel autentifikovany.
                    
                    // Uloz data pouzivatela do session.
                    $_SESSION['code2FA'] = $row['2fa_code'];
                    $_SESSION["loggedin"] = true;
                    $_SESSION["login"] = $row['login'];
                    $_SESSION["fullname"] = $row['fullname'];
                    $_SESSION["email"] = $row['email'];
                    $_SESSION["created_at"] = $row['created_at'];
                    

                    // Presmeruj pouzivatela na zabezpecenu stranku.
                    header("location: odhlasenie.php");
                }
                else {
                    echo "<script>alert('Neplatny kod 2FA.'); </script>";
                }
            } else {
                echo "<script>alert('Nespravne meno alebo heslo.'); </script>";
            }
        } else {
            echo "<script>alert('Nespravne meno alebo heslo.'); </script>";
        }
    } else {
        echo "<script>alert('Ups. Nieco sa pokazilo!'); </script>";
    }

    unset($stmt);
    unset($db);
}

?>
<?php

  

// Inicializacia Google API klienta
$client = new Google\Client();

// Definica konfiguracneho JSON suboru pre autentifikaciu klienta.
// Subor sa stiahne z Google Cloud Console v zalozke Credentials.
$client->setAuthConfig('client_secret.json');

// Nastavenie URI, na ktoru Google server presmeruje poziadavku po uspesnej autentifikacii.
$redirect_uri = "https://site130.webte.fei.stuba.sk/oh/odhlasenie.php";
$client->setRedirectUri($redirect_uri);

// Definovanie Scopes - rozsah dat, ktore pozadujeme od pouzivatela z jeho Google uctu.
$client->addScope("email");
$client->addScope("profile");

// Vytvorenie URL pre autentifikaciu na Google server - odkaz na Google prihlasenie.
$auth_url = $client->createAuthUrl();


?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login/Register</title>
    <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.3/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link  rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <style>
      .log{
        width: 70%;
        margin: 0 auto;
      }

    @media screen and (max-width: 950px) {
            #log{
                width: 50%;
            }

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
        <h1>Prihlásenie - Prihlásenie používateľa po registrácii</h1>
    </hgroup>
</header>
<main>
<div  id="log">
    <form action="#" method="post" onsubmit="return loginCheck();">
    <div class="log">
        <label for="InputNick">
            Prihlasovacie meno:
            <input type="text" name="login" value="" id="InputNick" >
            <div id="InputNickWarn" class="red"> <?php echo $warn2?></div>
        </label>
        <br>
        <label for="InputPassword">
            Heslo:
            <input type="password" name="password" value="" id="InputPassword" >
            <div id="InputPasswordWarn" class="red"> <?php echo $warn3?></div>
        </label>
        <br>
        <label for="InputKey">
            2FA kod:
            <input type="number" name="2fa" value="" id="InputKey" >
            <div id="InputKeyWarn" class="red"> <?php echo $warn4?></div>
        </label>
    </div>

        <button class="button" type="submit">Prihlasit sa</button>
    </form>
    <form action="register.php" method="get" target="_self">
            <p>Ešte nemáte vytvorené konto?<button class="button" type="submit">Registrujte sa tu</button></p>
         
      </form>

      <?php
        
        echo '<a role="button" class="button_g" href="' . filter_var($auth_url, FILTER_SANITIZE_URL) . '">Google prihlasenie</a>';
        
        ?>
</div>
</main>
<script>
    console.log('fun');
    function loginCheck(){
        if($('#InputNick').val()===""){$('#InputNickWarn').html('Nezadané používateľské meno'); console.log('name'); return false;}else{$('#InputNickWarn').html('');}
        if($('#InputPassword').val()===""){$('#InputPasswordWarn').html('Nezadané heslo'); console.log('pass'); return false;}else{$('#InputPasswordWarn').html('');}
        if($('#InputKey').val()===""){$('#InputKeyWarn').html('Nezadaný autentifikačný kód'); console.log('code'); return false;}else{$('#InputKeyWarn').html('');}
        return true
    }


</script>
</body>
</html>