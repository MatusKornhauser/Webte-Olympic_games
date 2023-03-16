<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail</title>
    <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.3/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link  rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script>
        $(document).ready(function () {
            table = $('.detail_osoby').DataTable({
                order: [[1, 'asc']],
                paging: false,
                info: false
                
            });
        }); 

    </script>   
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
    <h1>Detail športovca</h1>
    <div class="uvod">
        <?php  
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);

            require_once('config.php');

            try {
                $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {
                echo $e->getMessage();
            }

            if(isset($_GET["id"])){
                $query = "SELECT * FROM person WHERE id=".$_GET['id'];
                $stmt = $db->query($query);
                $detaily = $stmt->fetch(PDO::FETCH_ASSOC);
                $datebirth = new DateTimeImmutable($detaily["birth_day"]);
                echo "<b>Meno: </b>".$detaily["name"];echo "</br>";
                echo "<b>Priezvisko</b>: ".$detaily["surname"];echo "</br>";
                echo "<b>Dátum narodenia</b>: ".$datebirth->format("d.m.Y");echo "</br>";
                echo "<b>Miesto narodenia</b>: ".$detaily["birth_place"];echo "</br>";
                echo "<b>Krajina narodenia</b>: ".$detaily["birth_country"];echo "</br>";
                echo "<b>Dátum úmrtia</b>: ".$detaily["death_day"];echo "</br>";
                echo "<b>Miesto úmrtia</b>: ".$detaily["death_place"];echo "</br>";
                echo "<b>Krajina úmrtia</b>: ".$detaily["death_country"];
            }
        ?>
    </div>
    <div class="detaily">
        <table class = "detail_osoby">
        <thead>
            <tr><td>Umiestnenie</td><td>Rok</td><td>Mesto</td><td>Krajina</td><td>Typ</td><td>Disciplina</td></tr>
        </thead>
        <tbody>
            <?php
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);

            require_once('config.php');

            try {
                $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {
                echo $e->getMessage();
            }
            if(isset($_GET["id"])){
                $query = "SELECT * FROM placement WHERE person_id=" . $_GET['id'];
                $stmt = $db->query($query);
                $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach($details as $detail)
                {
                    $query = "SELECT * FROM game WHERE id=" .$detail['game_id'];
                    $stmt = $db->query($query);
                    $game = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo("<tr><td>{$detail['placing']}</td><td>{$game['year']}</td><td>{$game['city']}</td><td>{$game['country']}</td><td>{$game['type']}</td><td>{$detail['discipline']}</td></tr> ");

                }
            }
            ?>
        </tbody>
        </table>
    </div>

    
</body>
</html>