<?php
session_start();

//-----récupérer les paramètres----
$_PARAMS = $_POST;
//---------------------------------


$CMD = $_PARAMS['CMD'];
$pwd = null;
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
    global $_PARAMS, $pwd; 

    $param1 = isset($_PARAMS['PARAM1']) ? $_PARAMS['PARAM1'] : '';
    $param2 = isset($_PARAMS['PARAM2']) ? $_PARAMS['PARAM2'] : '';
    error_log(' Username 1.1: ' . $param1);

    if (!empty($param1) && !empty($param2)) {
        $_SESSION['username'] = $param1;
        $_SESSION['mdp'] = $param2;
        error_log(' Username 1: ' . $param1);

        if (!is_dir("users/" . $_SESSION['username'])) {
            mkdir("users/" . $_SESSION['username'], 0777, true);
            $response = array('status' => 'success', 'message' => 'Login successful', 'home_status' => 'created');
        } else {
            $response = array('status' => 'success', 'message' => 'Login successful');
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Missing parameters for login');
    }
    $pwd = '/home/'.$_SESSION['username'];
    send_response('LOGIN', $response['status'], $response['message']);
    exit; 
}
function LOGOUT(){
    global $pwd;

    session_unset();
    session_destroy();

    $pwd = null;
    send_response('LOGOUT', 'success', 'Logout successful');
    exit;
}
function WHOAMI(){
    if(isset($_SESSION['username'])) {
        error_log(' Username 2: ' . $_SESSION['username']);
        $response = array('status' => 'success', 'username' => $_SESSION['username']);
    } else {
        $response = array('status' => 'error', 'message' => 'User not logged in');
    }

    send_response('WHOAMI', $response['status'], $response['message']);
    exit;
}
function DIRECTORY() {
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
        send_response('DIRECTORY', $response['status'], '');
        exit;
    } else {
        $response = array('status' => 'error', 'message' => 'User not logged in');
        send_response('DIRECTORY', $response['status'], $response['message']);
        exit;
    }
}
function send_response($cmd, $status, $content = null){
    global $pwd;

    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'N/A';

    $response = array(
        'cmd' => $cmd,
        'status' => $status,
        'content' => $content,
        'debug' => array(
            'user' => $username,
            'home' => isset($_SESSION['username']) ? '/home/' . $_SESSION['username'] : 'N/A',
            'pwd' => $pwd
        )
    );

    echo json_encode($response);
    exit;
}
function LIST_DIRECTORIES() {
    if (isset($_SESSION['username'])) {
        $currentDirectory = 'users/' . $_SESSION['username'];
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
        $response = array('status' => 'error', 'message' => 'User not logged in');
        send_response('LIST_DIRECTORIES', $response['status'], $response['message']);
        exit;
    }
}


switch($CMD) {
    case "LOGIN" : LOGIN() ; break;
    case "LOGOUT": LOGOUT(); break;
    case "WHOAMI": WHOAMI(); break;
    case "DIR": DIRECTORY(); break;
    case "CD1": LIST_DIRECTORIES(); break;
    default:
    // Envoyer une réponse d'erreur au format JSON pour les commandes non reconnues
    $response = array('status' => 'error', 'message' => 'Unknown command');
    echo json_encode($response);
    break;
}
?>

