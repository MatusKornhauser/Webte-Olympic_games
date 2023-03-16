<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();


require_once('config.php');
require_once 'vendor/autoload.php';
require_once ("GoogleAuthenticator/PHPGangsta/GoogleAuthenticator.php");
$ga = new PHPGangsta_GoogleAuthenticator();

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo $e->getMessage();
}
//Inicializacia Google API klienta
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

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo $e->getMessage();
}


// Ak bolo prihlasenie uspesne, Google server nam posle autorizacny kod v URI,
// ktory ziskame pomocou premennej $_GET['code']. Pri neuspesnom prihlaseni tento kod nie je odoslany.
if (isset($_GET['code'])) {
    // Na zaklade autentifikacneho kodu ziskame "access token".
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);
    

    // Inicializacia triedy OAuth2, pomocou ktorej ziskame informacie pouzivatela na zaklade Scopes.
    $oauth = new Google\Service\Oauth2($client);
    $account_info = $oauth->userinfo->get();

    // Ziskanie dat pouzivatela z Google uctu. Tieto data sa nachadzaju aj v tokene po jeho desifrovani.
    $g_fullname = $account_info->name;
    $g_id = $account_info->id;
    $g_email = $account_info->email;
    $g_name = $account_info->givenName;
    $g_surname = $account_info->familyName;

    // Na tomto mieste je vhodne vytvorit poziadavku na vlastnu DB, ktora urobi:
    // 1. Ak existuje prihlasenie Google uctom -> ziskaj mi minule prihlasenia tohoto pouzivatela.
    // 2. Ak neexistuje prihlasenie pod tymto Google uctom -> vytvor novy zaznam v tabulke prihlaseni.

    // Ulozime potrebne data do session.
    $_SESSION['access_token'] = $token['access_token'];
    $_SESSION['login'] = $g_email;
    $_SESSION['id'] = $g_id;
    $_SESSION['fullname'] = $g_fullname;
    $_SESSION['name'] = $g_name;
    $_SESSION['surname'] = $g_surname;

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.3/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link  rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <title>Document</title>
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
    <main>
        <div class="odhlas">
            <?php
                if(isset($_SESSION['access_token']) && $_SESSION['access_token']){
                    echo 'Vitaj '.$_SESSION['fullname'];echo "</br>";
                    echo 'Si prihlaseny pod emailom: '.$_SESSION['login'];echo "</br>";
                
                }else{
                    echo 'Vitaj '.$_SESSION['fullname'];echo "</br>";
                    echo 'Tvoj identifikator (login) je: '.$_SESSION['login'];echo "</br>";
                    echo 'Datum registracie/vytvonia konta: '.$_SESSION['created_at'];echo "</br>";
                    echo "Autentifikačný kód: " . $_SESSION['code2FA'];
                    $qrCodeUrl = $ga->getQRCodeGoogleUrl('Olympic Games', $_SESSION['code2FA']);
                    echo '<br>QR Kód:<br /><img src="'.$qrCodeUrl.'" alt="QR kód pre 2 faktorovú autentifikáciu"/>';
                }
            ?>
            <br>
            <br>
            <a role="button" class="button" href="logout.php">Odhlásenie</a></p>
            <a role="button" class="button" href="index.php">Späť na hlavnú stránku</a></p>
        </div>

    </main>
    
</body>
</html>