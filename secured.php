<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();



require_once('config.php');
require_once 'vendor/autoload.php';


//var_dump($_POST["person_id"]);

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT * FROM person";
    $stmt = $db->query($query); 
    $persons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT * FROM game";
    $stmt = $db->query($query); 
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT * FROM placement";
    $stmt = $db->query($query); 
    $placements = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    echo $e->getMessage();
}


if(!empty($_POST) && !empty($_POST['name'])){

    $query = "SELECT * FROM person p WHERE (p.name = '" . $_POST['name'] . "') AND (p.surname = '" . $_POST['surname'] . "') AND (p.birth_day = '" . $_POST['birth_day'] . "');";
    $stmt = $db->query($query);
    $persons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($_POST['death_day']==""){$_POST['death_day']=null;}
    if ($_POST['death_place']==""){$_POST['death_place']=null;}
    if($_POST['death_country']==""){$_POST['death_country']=null;}
    if(count($persons)==0) {
        $sql = "INSERT INTO person (name, surname, birth_day, birth_place, birth_country, death_day, death_place, death_country) VALUES (?,?,?,?,?,?,?,?)";
        $stmt = $db->prepare($sql);
        $success = $stmt->execute([$_POST['name'], $_POST['surname'], $_POST['birth_day'], $_POST['birth_place'], $_POST['birth_country'], $_POST['death_day'], $_POST['death_place'], $_POST['death_country']]);

        $query = "SELECT * FROM person p WHERE (p.name = '" . $_POST['name'] . "') AND (p.surname = '" . $_POST['surname'] . "') AND (p.birth_day = '" . $_POST['birth_day'] . "');";
        $stmt = $db->query($query);
        $persons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<script>alert('Úspešne pridaný športovec');</script>";
        echo "<script>window.location.href='index.php';</script>";
    }else{
        echo "<script>alert('Zadaná osoba sa v databáze už nachádza')</script>";

    }
}

if (isset($_SESSION['access_token']) && $_SESSION['access_token'] || isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] ) {


} else {
    // Ak pouzivatel prihlaseny nie je, presmerujem ho na hl. stranku.
    header('Location: login.php');
}

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
            $('.tableS').DataTable();
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
    
    <h1>Prihlásený používateľ</h1>
      
    <div class="content">
    
        <div class="pageElement">
        <h2>Pridaj športovca</h2>  
            <div class="pridaj">
                    <form action="#" method="post" onsubmit="return personAddCheck();">
                        <div class="mb-3">
                            <label for="InputName" class="form-label">Name:</label>
                            <input type="text" name="name" class="form-control" id="InputName" >
                            <div id="InputNameWarn" class="red"></div>
                        </div>
                        <div class="mb-3">
                            <label for="InputSurname" class="form-label">Surname:</label>
                            <input type="text" name="surname" class="form-control" id="InputSurname" >
                            <div id="InputSurnameWarn" class="red"></div>
                        </div>
                        <div class="mb-3">
                            <label for="InputDate" class="form-label">birth day:</label>
                            <input type="date" name="birth_day" class="form-control" id="InputDate" >
                            <div id="InputDateWarn" class="red"></div>
                        </div>
                        <div class="mb-3">
                            <label for="InputbrPlace" class="form-label">birth place:</label>
                            <input type="text" name="birth_place" class="form-control" id="InputBrPlace" >
                            <div id="InputBrPlaceWarn" class="red"></div>
                        </div>
                        <div class="mb-3">
                            <label for="InputBrCountry" class="form-label">birth country:</label>
                            <input type="text" name="birth_country" class="form-control" id="InputBrCountry" >
                            <div id="InputBrCountryWarn" class="red"></div>
                        </div>
                        <div class="mb-3">
                            <label for="InputDeathDate" class="form-label">death day:</label>
                            <input type="date" name="death_day" class="form-control" id="InputDeathDate" >
                        </div>
                        <div class="mb-3">
                            <label for="InputdtPlace" class="form-label">death place:</label>
                            <input type="text" name="death_place" class="form-control" id="InputdtPlace" >
                        </div>
                        <div class="mb-3">
                            <label for="InputDtCountry" class="form-label">death country:</label>
                            <input type="text" name="death_country" class="form-control" id="InputDtCountry" >
                        </div>
                        <button type="submit" class="btn btn-secondary">Submit</button>
                    </form>
                </div>
            </div>
            <?php
                    

                    if(!empty($_POST) && !empty($_POST['person_id'])){
                        $sql = "INSERT INTO placement (person_id, game_id, placing, discipline) VALUES (?,?,?,?)";
                        $stmt = $db->prepare($sql);
                        $pridajum = $stmt->execute([$_POST['person_id'], $_POST['game_id'],$_POST['placing'],$_POST['discipline']]);
                        echo "<script>alert('Úspešne pridané umiestnenie');</script>";
                    }

            ?>
            <div class="pageElement1">
            <h2>Pridaj umiestnenie</h2> 
                <div class="pridaj">
                    <form action="#" method="post" onsubmit="return placementAddCheck();">
                        <select name="person_id">
                            <?php
                            foreach($persons as $person){
                                echo '<option value="' . $person['id'] . '">' . $person['name'] . ' ' . $person['surname'] . '</option>';
                            }       
                            ?>
                        </select>
                        <br>
                        <br>
                        <select name="game_id">
                            <?php
                            foreach($games as $game){
                                echo '<option value="' . $game['id'] . '">' . $game['city'] . ' ' . $game['type'] . ' ' . $game['year'] .'</option>';
                            }       
                            ?>
                        </select>
                        <label for="InputDiscipline">Disciplína:</label>
                        <input type="text" id="InputDiscipline" name="discipline" min="1" max="100" >
                        <div id="InputDisciplineWarn" class="red"></div>
                        <label for="InputPlace">Umiestenie:</label><br>
                        <input type="number" id="InputPlace" name="placing" min="1" max="100" ><br><br><br>
                        <div id="InputPlaceWarn" class="red"></div>
                        <button type="submit" class="btn btn-secondary">Submit</button>
                    </form>
                </div>
            </div>    
        </div>
    
        <table class="tableS">
            <thead>
                <tr><td>Meno</td><td>Priezvisko</td><td>Dátum narodenia</td><td>Miesto narodenia</td><td>Krajina narodenia</td><td>Dátum úmrtia</td><td>Miesto úmrtia</td><td>Krajina úmrtia</td><td>Akcia</td></tr>
            </thead>
            <tbody>
                <?php
                    $query = "SELECT person.id as u_id, person.name, person.surname, person.birth_day, person.birth_place, person.birth_country, person.death_day,person.death_place, person.death_country FROM person";
                    $stmt = $db->query($query); 
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                   
                    foreach ($rows as $row)
                    {
                        $datebirth = new DateTimeImmutable($row["birth_day"]);
                        echo("<tr><td>{$row['name']}</td><td>{$row['surname']}</td><td>{$datebirth->format("d.m.Y")}</td><td>{$row['birth_place']}</td><td>{$row['birth_country']}</td><td>{$row['death_day']}</td><td>{$row['death_place']}</td><td>{$row['death_country']}</td><td><a href='edit.php?id={$row['u_id']}' class='button'>Edit</a></td></tr>");
                    }
                ?>
            </tbody>
        </table>
    <style>
        .button{
            background-color: #6c757d;
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
            margin: 4px 2px;
            cursor: pointer;
        }
    </style>
    <script>

                function personAddCheck(){
                    if($('#InputName').val()===""){$('#InputNameWarn').html('Nezadané meno'); return false}else{$('#InputNameWarn').html('');}
                    if($('#InputSurname').val()===""){$('#InputSurnameWarn').html('Nezadané priezvisko'); return false}else{$('#InputSurnameWarn').html('');}
                    if($('#InputDate').val()===""){$('#InputDateWarn').html('Nezadaný dátum narodenia'); return false}else{$('#InputDateWarn').html('');}
                    if($('#InputBrPlace').val()===""){$('#InputBrPlaceWarn').html('Nezadané miesto narodenia'); return false}else{$('#InputBrPlaceWarn').html('');}
                    if($('#InputBrCountry').val()===""){$('#InputBrCountryWarn').html('Nezadaná krajina narodenia'); return false}else{$('#InputBrCountryWarn').html('');}
                    return true
                }

                function placementAddCheck(){
                if($('#InputDiscipline').val()===""){$('#InputDisciplineWarn').html('Nezadaná disciplína'); return false}else{$('#InputDisciplineWarn').html('');}
                if($('#InputPlace').val()===""){$('#InputPlaceWarn').html('Nezadané umiestnenie'); return false}else{$('#InputPlaceWarn').html('');}
                return true
                }
    </script>
    
</body>
</html>