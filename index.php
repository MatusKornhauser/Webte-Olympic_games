<?php
    session_start();
    

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
            table = $('.table').DataTable({
                order: [[2, 'asc']]
            });
        }); 
    </script>
    <style>
        .table{
            width: 100%;
            overflow: auto;
        }
        
    
    </style>
</head>
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

<body>
    <div class="container-md">
    <h1>Slovenský olympijsky víťazi</h1>
    <table class="table">
        <thead>
            <tr><td>Meno</td><td>Priezvisko</td><td>Rok</td><td>Mesto</td><td>Krajina</td><td>Typ</td><td>Disciplina</td><td>Umiestnenie</td></tr>
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

                    $query = "SELECT person.id as u_id, game.year, game.type , game.city, game.country,  person.name, person.surname,  placement.id as p_id, placement.discipline, placement.placing  FROM person JOIN placement ON person.id = placement.person_id JOIN game ON placement.game_id = game.id ORDER BY `person`.`surname` ASC";
                    #$query = "SELECT * FROM game";
                    $stmt = $db->query($query); 
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row)
                    {
                        echo("<tr><td><a href='detail.php?id={$row['u_id']}'> {$row['name']} </a> </td><td>{$row['surname']}</td> <td>{$row['year']}</td> 
                            <td>{$row['city']}</td> <td>{$row['country']}</td> <td>{$row['type']}</td> <td>{$row['discipline']}</td>
                            <td>{$row['placing']}</td></tr>");
                    }
        ?>
        </tbody>
    </table>
    <table class="table" >
        <thead>
            <tr><td>Meno</td><td>Priezvisko</td><td>Počet získaných zlatých medailí</td></tr>
        </thead>
        <tbody>
            <?php
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                error_reporting(E_ALL);
                require_once("config.php");
                        try {
                            $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
                            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            echo "<h2>TOP 10 Olympijskych víťazov</h2><br>";
                        } catch(PDOException $e) {
                            echo "Connection failed: " . $e->getMessage();
                        }

                        $query="SELECT person.name, person.surname, COUNT(placement.person_id) as Pocet_zlatých_medaili FROM placement JOIN person ON placement.person_id = person.id WHERE placement.placing = 1 GROUP BY placement.person_id ORDER BY Pocet_zlatých_medaili  DESC LIMIT 10";
                        $stm = $db->query($query);
                        $rows = $stm->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($rows as $row)
                        {
                        
                            echo("<tr><td>{$row['name']}</td> <td>{$row['surname']}</td> <td>{$row['Pocet_zlatých_medaili']}</td> </tr> ");

                        }
                        echo "</br>";

                        
            ?>
     </tbody>
    </table>
    </div>
</body>
</html>
