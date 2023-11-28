<?php
session_start();
//-----récupérer les paramètres----
$_PARAMS = $_POST;
//---------------------------------

$CMD = $_PARAMS['CMD'];

function SIZE($location) {
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
    global $_PARAMS; 
    $param1 = isset($_PARAMS['PARAM1']) ? $_PARAMS['PARAM1'] : '';
    $param2 = isset($_PARAMS['PARAM2']) ? $_PARAMS['PARAM2'] : '';

    if (!empty($param1) && !empty($param2)) {
        $_SESSION['username'] = $param1;
        $_SESSION['mdp'] = $param2;

        if (!is_dir("users/" . $_SESSION['username'])) {
            mkdir("users/" . $_SESSION['username'], 0777, true);
            $response = array('status' => 'success', 'message' => 'Login successful', 'home_status' => 'created');
            $_SESSION['pwd'] = "/";
            $_SESSION['home'] = "C:/Users/moreeva/Documents/Disk_Controller/users/" . $_SESSION['username'];

        } else {
            $response = array('status' => 'success', 'message' => 'Login successful');
            $_SESSION['pwd'] = "/";
            $_SESSION['home'] = "C:/Users/moreeva/Documents/Disk_Controller/users/" . $_SESSION['username'];
        }

    } else {
        $response = array('status' => 'error', 'message' => 'Missing parameters for login');
    }
    send_response('LOGIN', $response['status'], $response['message']);
    exit; 
}
function LOGOUT(){
    global $_PARAMS;
    session_unset();
    session_destroy();

    send_response('LOGOUT', 'success', 'Logout successful');
    exit;
}
function WHOAMI(){
    global $_PARAMS;
    if(isset($_SESSION['username'])) {
        $response = array('status' => 'success', 'username' => $_SESSION['username']);
        send_response('WHOAMI', $response['status']);

    } else {
        $response = array('status' => 'error', 'message' => 'User not logged in');
        send_response('WHOAMI', $response['status'], $response['message']);
    }
    exit;
}
function DIRECTORY() {
    global $_PARAMS;
    if(isset($_SESSION['username'])) {
        $rep = 'users/' . $_SESSION['username'];
        $dirs = scandir($rep);
        $response = array('status' => 'success', 'directories' => array());

        foreach ($dirs as $dir) {
            // if ($dir != '.' && $dir != '..') {
                $location = $rep . '/' . $dir;
                if (is_dir($location)) {
                    $response['directories'][] = array('type' => 'dir', 'name' => $dir);
                } elseif(is_file($location)) {
                    $response['directories'][] = array('type' => 'file', 'name' => $dir, 'size' => SIZE($location), 'date' => filemtime($location));
                }
            // }
        }

        // Envoyer une réponse au format JSON
        send_response('DIRECTORY', $response['status'], $response['directories']);
        exit;
    } else {
        $response = array('status' => 'error', 'message' => 'User not logged in');
        send_response('DIRECTORY', $response['status'], $response['message']);
        exit;
    }
}
function send_response($cmd, $status, $content = null){
    global $_PARAMS;
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'N/A';

    $response = array(
        'cmd' => $cmd,
        'status' => $status,
        'content' => $content,
        'debug' => array(
            'user' => $username,
            'home' => isset($_SESSION['username']) ? '/home/' . $_SESSION['username'] : 'N/A',
            'pwd'  => isset($_SESSION['pwd']) ? '' . $_SESSION['pwd'] : 'N/A',
            'SESSION' => $_SESSION, // Ajoutez directement l'ensemble de la session
            )
    );

    echo json_encode($response);
    // foreach($_SESSION as $key => $value){
    //     error_log('Après Session : ' . $key . ' -> '. $value );
    // }    
    exit;
}
function LIST_DIRECTORIES() {
    global $_PARAMS;
    if (isset($_SESSION['username'])) {
        $currentDirectory = $_SESSION['home'] . $_SESSION['pwd'];
        
        // Vérifier si le chemin du répertoire existe avant d'appeler scandir
        if (is_dir($currentDirectory)) {
            $dirs = scandir($currentDirectory);
            $directories = array();
            foreach ($dirs as $dir) {
                if ($dir != '.' && $dir != '..' && is_dir($currentDirectory . '/' . $dir)) {
                    $directories[] = $dir;
                }
            }
            $response = array('status' => 'success', 'directories' => $directories);
            send_response('LIST_DIRECTORIES', $response['status'], $response['directories']);
            exit;
        } else {
            $response = array('status' => 'error', 'message' => 'Directory not found');
            send_response('LIST_DIRECTORIES', $response['status'], $response['message']);
            exit;
        }
    } else {
        $response = array('status' => 'error', 'message' => 'User not logged in');
        send_response('LIST_DIRECTORIES', $response['status'], $response['message']);
        exit;
    }
}

function CHANGE_DIRECTORY() {
    global $_PARAMS;
    $newDirectory = $_PARAMS['PARAM1'];
    if (!empty($newDirectory)) { // S'assurer que le nouveau répertoire est défini
        if ($newDirectory == '..') {
            // Si le nouveau répertoire est "..", remonter d'un niveau
            $parts = explode('/', rtrim($_SESSION['pwd'], '/'));
            $extractedValue = array_pop($parts);

            // Vérifier si le chemin est déjà la racine
            if (empty($parts)) {
                $_SESSION['pwd'] = '/';
            } else {
                // Vérifier si le tableau a plus d'un élément après l'extraction de ".."
                if (count($parts) > 1) {
                    $_SESSION['pwd'] = implode('/', $parts) . '/';
                } else {
                    $_SESSION['pwd'] = implode('/', $parts);
                }
            }
            if($_SESSION['pwd'] == ""){
                $_SESSION['pwd'] = "/";
            }
            // Envoyer une réponse pour indiquer que le répertoire a été changé avec succès
            send_response('CD', 'success', 'Directory changed successfully');
        } else {
            $newDirectoryPath = $_SESSION['home'] . $_SESSION['pwd'] . $newDirectory;
            if (is_dir($newDirectoryPath)) {
                // Ajouter un "/" à la fin du chemin si ce n'est pas déjà le cas
                $_SESSION['pwd'] = rtrim($_SESSION['pwd'], '/') . '/';
                $_SESSION['pwd'] .= $newDirectory . '/';
                send_response('CD', 'success', 'Directory changed successfully');
            } else {
                send_response('CD', 'error', 'Directory not found');
            }
        }
    } else {
        send_response('CD', 'error', 'Missing parameters');
        error_log('There is no PARAM1 in the $_PARAMS["PARAM1"]');
    }
    exit;
}



function HOME(){
    global $_PARAMS;
    $_SESSION['pwd'] = '/';
    if($_SESSION['pwd'] == '/'){
        send_response('HOME', 'success', $_SESSION['pwd']);
    }else{
        send_response('HOME', 'error', $_SESSION['pwd']);
    }
        
    
}
switch($CMD) {
    case "LOGIN" : LOGIN() ; break;
    case "LOGOUT": LOGOUT(); break;
    case "WHOAMI": WHOAMI(); break;
    case "DIR": DIRECTORY(); break;
    case "CD1": LIST_DIRECTORIES(); break;
    case "CHANGE_DIRECTORY": CHANGE_DIRECTORY(); break;
    case "HOME": HOME(); break;


    default:
    // Envoyer une réponse d'erreur au format JSON pour les commandes non reconnues
    $response = array('status' => 'error', 'message' => 'Unknown command');
    echo json_encode($response);
    break;
}


?>