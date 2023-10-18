DISK : <br>

<?php

session_start();

//-----récupérer les paramètres----
if(count($_POST)) $_PARAMS = $_POST;
else              $_PARAMS = $_GET;
//---------------------------------

$CMD = $_PARAMS['CMD'];
function Size($location) {
    $size = filesize($location);
    $fileSize = 0;
    
    switch (true) {
        case ($size >= 1073741824)  : $fileSize = number_format($size / 1073741824, 2) . 'Go'  ; break;
        case ($size >= 1048576)     : $fileSize = number_format($size / 1048576, 2) . 'Mo'     ; break;
        case ($size >= 1024)        : $fileSize = number_format($size / 1024, 2) . 'Ko'        ; break;
        default                     : $fileSize = $size . 'o'                             ; break;
    }
    return $fileSize;
}

function LOGIN(){
    if(isset($_PARAMS['PARAM1'])&&isset($_PARAMS['PARAM2'])){
        $_SESSION['username'] = $_PARAMS['PARAM1'];
        $_SESSION['mdp'] = $_PARAMS['PARAM2'];
        if (session_status() == PHP_SESSION_ACTIVE) {
            echo 'La session est démarrée.';
            if (!is_dir("users/" . $_SESSION['username'])) {
                mkdir("users/" . $_SESSION['username'], 0777, true);
                echo "Répertoire créé avec succès.";
            }
            echo '<br>';
            $_SESSION['location'] = "C:/wamp64/www/users/".$_SESSION['username'];
            echo $_SESSION['location'];
        } else {
            echo 'La session n\'est pas démarrée.';
        }
    }
}
function LOGOUT(){
    session_destroy();
}
function WHOAMI(){
    echo '<br>';
    echo '<u>Username :</u> '.$_SESSION['username'] ;
    echo '<br>';
    echo $_SESSION['mdp'] ;
    echo '<br>';
}
function DIRECTORY() {
    $rep = 'users/' . $_SESSION['username'];
    $dirs = scandir($rep);
    date_default_timezone_set('Europe/Paris');
    echo '<ul>';
    foreach ($dirs as $dir) {
        if ($dir != '.' && $dir != '..') {
            $location = $rep . '/' . $dir;
            echo '<li>';
            if (is_dir($location)) {
                echo "[DIR] " . $dir . "<br>";
            } elseif(is_file($location)) {
             
                echo "[FILE] " . $dir . " <u>" .date('Y/m/d-H:i:s', filemtime($location)). "</u>". " " . Size($location) . "<br>";
            }
            echo '</li>';
        }
    }
    echo '</ul>';
}


echo '<u>Commande :</u> '.$CMD;
echo '<br>';
switch($CMD) {
    case "LOGIN" : LOGIN() ; break;
    case "LOGOUT": LOGOUT(); break;
    case "WHOAMI": WHOAMI(); break;
    case "DIR": DIRECTORY(); break;
    default:
        echo "Commande non reconnue";
        break;
}
?>

