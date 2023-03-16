<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();
    
    require_once('config.php');
    $detail_id = $_GET["id"];
    try {
        $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e) {
        echo $e->getMessage();
    }

    if(!empty($_POST) && !empty($_POST['name'])){

        if ($_POST['death_day']==""){$_POST['death_day']='NULL';}else{$_POST['death_day'] = "'" . $_POST['death_day'] . "'";}
        if ($_POST['death_place']==""){$_POST['death_place']='NULL';}else{$_POST['death_place'] = "'" . $_POST['death_place'] . "'";}
        if($_POST['death_country']==""){$_POST['death_country']='NULL';}else{$_POST['death_country'] = "'" . $_POST['death_country'] . "'";}
    
        $sql = "UPDATE person SET name ='" .  $_POST['name'] . "', surname ='" .  $_POST['surname'] . "', birth_day ='" .  $_POST['birth_day'] . "', birth_place ='" . $_POST['birth_place'] . "', birth_country ='" . $_POST['birth_country'] . "', death_day =" . $_POST['death_day'] . ", death_place =" . $_POST['death_place'] . ", death_country =" . $_POST['death_country'] . " WHERE id=" . $detail_id;
        $stmt = $db->prepare($sql);
        $success = $stmt->execute();
        echo "<script>alert('Úspešne aktualizovaný športovec'); </script>";
    }
    if(!empty($_POST) && !empty($_POST['discipline'])){
        $sql = "UPDATE placement SET game_id='" . $_POST['game_id'] . "', placing='" . $_POST['placing'] . "', discipline='" . $_POST['discipline'] . "' WHERE id=" . $_POST['placement_id'];
        $stmt = $db->prepare($sql);
        $successa = $stmt->execute();
        echo "<script>alert('Úspešne aktualizované umiestenie'); </script>";
    
    }

    if(!empty($_GET) && !empty($_GET['placement_id'])) {
        $sql = "DELETE FROM placement WHERE id =" . $_GET['placement_id'];
        $stmt = $db->prepare($sql);
        $success = $stmt->execute();
        echo "<script>alert('Úspešne vymazaný záznam'); </script>";
    }
    
    if(!empty($_GET) && !empty($_GET['delete'])) {
        $sql = "DELETE FROM person WHERE id =" . $detail_id;
        $stmt = $db->prepare($sql);
        $success = $stmt->execute();
        echo "<script>alert('Úspešne vymazaná osoba');</script>";
        echo "<script>window.location.href='index.php';</script>";
      
    }
   

    if (isset($_SESSION['access_token']) && $_SESSION['access_token'] || isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] ) {
        

    } else {
        // Ak pouzivatel prihlaseny nie je, presmerujem ho na hl. stranku.
        header('Location: login.php');
    }

    $query = "SELECT p.name, p.surname, p.id as 'person_id', p.birth_day, p.birth_place, p.birth_country, p.death_day, p.death_place, p.death_country, g.id as 'game_id', g.type, g.year, g.city, g.country, pl.discipline, pl.placing, pl.id as 'placement_id' FROM person p
                JOIN placement pl on pl.person_id = p.id
                JOIN game g on g.id = pl.game_id
                WHERE p.id =" . $detail_id . ";";
    $stmt = $db->query($query);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT * FROM game";
    $stmt = $db->query($query);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <style>
        #editForm{
            display: none;
            margin-bottom: 50px;
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
    <h1>Edit športovca</h1>
    <div class="contentEdit">
        <div class="pageElement2">
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
                        echo "<b>Krajina úmrtia</b>: ".$detaily["death_country"];echo "</br>";
                        echo "</br>";
                    }
                    
                ?>
                <div id="delete-button">
                        <button id="person_delete" class="btn btn-secondary">Odstrániť</button>
                </div>
            </div>
        </div>
        <div class="pageElement">
            <div class="tabulka">
                <?php
                    if(isset($_GET["id"])){
                        $query = "SELECT * FROM person WHERE id=".$_GET['id'];
                        $stmt = $db->query($query);
                        $detaily = $stmt->fetch(PDO::FETCH_ASSOC);
                        $datebirth = new DateTimeImmutable($detaily["birth_day"]);
                    }
                ?>
                <form action="#" method="post" onsubmit="return personEditCheck();">
                    <div class="mb-3">
                        <label for="InputName" class="form-label">Name:</label>
                        <input type="text" name="name" class="form-control" id="InputName" value="<?php echo $detaily["name"] ?>">
                        <div id="InputNameWarn" class="red"></div>
                    </div>
                    <div class="mb-3">
                        <label for="InputSurname" class="form-label">Surname:</label>
                        <input type="text" name="surname" class="form-control" id="InputSurname"value="<?php echo $detaily["surname"] ?>" >
                        <div id="InputSurnameWarn" class="red"></div>
                    </div>
                    <div class="mb-3">
                        <label for="InputDate" class="form-label">birth day:</label>
                        <input type="date" name="birth_day" class="form-control" id="InputDate" value="<?php echo $detaily["birth_day"] ?>" >
                        <div id="InputDateWarn" class="red"></div>
                    </div>
                    <div class="mb-3">
                        <label for="InputbrPlace" class="form-label">birth place:</label>
                        <input type="text" name="birth_place" class="form-control" id="InputBrPlace" value="<?php echo $detaily["birth_place"] ?>">
                        <div id="InputBrPlaceWarn" class="red"></div>
                    </div>
                    <div class="mb-3">
                        <label for="InputBrCountry" class="form-label">birth country:</label>
                        <input type="text" name="birth_country" class="form-control" id="InputBrCountry" value="<?php echo $detaily["birth_country"] ?>">
                        <div id="InputBrCountryWarn" class="red"></div>
                    </div>
                    <div class="mb-3">
                        <label for="InputDeathDate" class="form-label">death day:</label>
                        <input type="date" name="death_day" class="form-control" id="InputDeathDate" value="<?php echo $detaily["death_day"] ?>">
                    </div>
                    <div class="mb-3">
                        <label for="InputdtPlace" class="form-label">death place:</label>
                        <input type="text" name="death_place" class="form-control" id="InputdtPlace" value="<?php echo $detaily["death_place"] ?>">
                    </div>
                    <div class="mb-3">
                        <label for="InputDtCountry" class="form-label">death country:</label>
                        <input type="text" name="death_country" class="form-control" id="InputDtCountry" value="<?php echo $detaily["death_country"] ?>">
                    </div>
                    <button type="submit" class="btn btn-secondary">Upraviť</button>
                </form>
            </div>
        </div>
    </div>

        <div class="table-responsive">
        <table id="example" class="dataTable display" style="width:100%">
            <thead>
            <tr>
                <th>Umiestnenie</th>
                <th>Typ</th>
                <th>Rok</th>
                <th>Mesto</th>
                <th>Krajina</th>
                <th>Disciplína</th>
                <th></th>
                <th></th>

            </tr>
            </thead>

        </table>
        <br>
    </div>
    <div id="editForm" class="pageElement">

            <form class="formLogin" action="#" method="post" onsubmit="placementEditCheck();">
                <label>
                    <select id="game_id" name="game_id">
                        <?php
                        foreach($games as $game){
                            echo '<option value="' . $game['id'] . '">' . $game['type'] . ' ' . $game['year'] . ' ' . $game['city'] . '</option>';
                        }
                        ?>
                    </select><br>
                </label>
                <div class="mb-3">
                    <label for="InputDiscipline" class="form-label">Disciplína</label>
                    <input type="text" name="discipline" class="form-control" id="InputDiscipline">
                    <div id="InputDisciplineWarn" class="red"></div>
                </div>
                <div class="mb-3">
                    <label for="InputPlace" class="form-label">Umiestnenie</label>
                    <input type="number" name="placing" class="form-control" id="InputPlace">
                    <div id="InputPlaceWarn" class="red"></div>
                </div>
                <input type="hidden" id="placement_id" name="placement_id">
               <br>
                <button type="submit" class="btn btn-secondary">Upraviť</button>
            </form>

        </div>
        
        <script>
            <?php
                $js_array = json_encode($details);
                echo "var data = ". $js_array . ";\n";
                if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']||isset($_SESSION['access_token']) && $_SESSION['access_token']){
                    echo "var loggedIn = true;";
                }else{
                    echo "var loggedIn = false;";
                }
            ?>

            var deleteB = document.getElementById('delete-button');
            $(document).ready(function () {
                if(loggedIn) {
                    table = $('#example').DataTable({
                        data: data,
                        columns: [
                            {data: 'placing'},
                            {data: 'type'},
                            {data: 'year'},
                            {data: 'city'},
                            {data: 'country'},
                            {data: 'discipline'},
                            {
                                data: null,
                                className: "dt-center editor-edit",
                                defaultContent: '<button class="btn btn-secondary">Upraviť</button>',
                                orderable: false
                            },
                            {
                                data: null,
                                className: "dt-center editor-delete",
                                defaultContent: '<button class="btn btn-secondary">Zmazať</button>',
                                orderable: false
                            }
                        ],
                        "columnDefs": [{
                            "targets": -1,
                            "orderable": false
                        }],
                        "lengthChange": false,
                        "pageLength": 10,
                        "pagingType": "full_numbers",
                        "searching": false,
                        "bInfo": false
                    
                
                });
                // Edit record
                $('#example').on('click', 'td.editor-edit', function (e) {
                    e.preventDefault();
                    var editForm = document.getElementById('editForm');
                    var game_id = document.getElementById('game_id');
                    var inputDiscipline = document.getElementById('InputDiscipline');
                    var inputPlace = document.getElementById('InputPlace');
                    var placement_id = document.getElementById('placement_id')
                    var data1 = table.row(this).data();
                    editForm.style.display = "flex";
                    game_id.value = data1['game_id'];
                    inputDiscipline.value = data1['discipline'];
                    inputPlace.value = data1['place'];
                    placement_id.value = data1['placement_id'];
                    editForm.scrollIntoView();
                });

                // Delete a record
                $('#example').on('click', 'td.editor-delete', function (e) {
                    e.preventDefault();

                    var data2 = table.row(this).data();
                    window.location.href = "edit.php?id=" + data2['person_id'] + "&placement_id=" + data2['placement_id'];


                });
                deleteB.style.display = "block";
               
            }
            
        });

        var deleteButton = document.getElementById('person_delete');
        deleteButton.addEventListener('click',function (){
        window.location.href="edit.php?id=" + <?php echo $detail_id?> + "&delete=true";
        });



        function personEditCheck(){
        if($('#InputName').val()===""){$('#InputNameWarn').html('Nezadané meno'); return false}else{$('#InputNameWarn').html('');}
        if($('#InputSurname').val()===""){$('#InputSurnameWarn').html('Nezadané priezvisko'); return false}else{$('#InputSurnameWarn').html('');}
        if($('#InputDate').val()===""){$('#InputDateWarn').html('Nezadaný dátum narodenia'); return false}else{$('#InputDateWarn').html('');}
        if($('#InputBrPlace').val()===""){$('#InputBrPlaceWarn').html('Nezadané miesto narodenia'); return false}else{$('#InputBrPlaceWarn').html('');}
        if($('#InputBrCountry').val()===""){$('#InputBrCountryWarn').html('Nezadaná krajina narodenia'); return false}else{$('#InputBrCountryWarn').html('');}
        return true
    }

        function placementEditCheck(){
            if($('#InputDiscipline').val()===""){$('#InputDisciplineWarn').html('Nezadaná disciplína'); return false}else{$('#InputDisciplineWarn').html('');}
            if($('#InputPlace').val()===""){$('#InputPlaceWarn').html('Nezadané umiestnenie'); return false}else{$('#InputPlaceWarn').html('');}
            return true
        }
        </script>
    
    
</body>
</html>