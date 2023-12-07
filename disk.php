<?php
session_start();
//-----récupérer les paramètres----
$_PARAMS = $_POST;
//---------------------------------
$CMD = $_PARAMS['CMD'];
function SIZE($location) {// Calcul la taille d'un fichier
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
function LOGIN(){//Login de l'utilisateur. Enregistrement du username
    global $_PARAMS; 
    $param1 = isset($_PARAMS['PARAM1']) ? $_PARAMS['PARAM1'] : '';
    $param2 = isset($_PARAMS['PARAM2']) ? $_PARAMS['PARAM2'] : '';

    if (!empty($param1) && !empty($param2)) {
        $_SESSION['username'] = $param1;

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
function LOGOUT(){// Destruction de la session actuelle
    global $_PARAMS;
    session_unset();
    session_destroy();

    send_response('LOGOUT', 'success', 'Logout successful');
    exit;
}
function WHOAMI(){// Renvoie le nom d'utilisateur
    global $_PARAMS;
    if(isset($_SESSION['username'])) {
        $response = array('status' => 'success', 'username' => $_SESSION['username']);
        send_response('WHOAMI', $response['status'], $response['username']);

    } else {
        $response = array('status' => 'error', 'message' => 'User not logged in');
        send_response('WHOAMI', $response['status'], $response['message']);
    }
    exit;
}
function DIRECTORY(){// Liste tous les répertoires dans le répertoire courant
    global $_PARAMS;
    if(isset($_SESSION['username'])) {
        $rep = $_SESSION['home'] . $_SESSION['pwd'];
        $dirs = scandir($rep);
        $response = array('status' => 'success', 'directories' => array());
        foreach ($dirs as $dir) {
            if ($dir != '.') {
                $location = $rep . '/' . $dir;
                if (is_dir($location)) {
                    $response['directories'][] = array('type' => 'dir', 'name' => $dir);
                } elseif(is_file($location)) {
                    $response['directories'][] = array('type' => 'file', 'name' => $dir, 'size' => SIZE($location), 'date' => filemtime($location));
                }
            }
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
function send_response($cmd, $status, $content = null){// Renvoie de la réponse (appelé dans toutes les fonctions utiles)
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
            'SESSION' => $_SESSION, 
            )
    );

    echo json_encode($response);
    // foreach($_SESSION as $key => $value){
    //     error_log('Après Session : ' . $key . ' -> '. $value );
    // }    
    exit;
}
function CD(){// Change le répertoire courant
    global $_PARAMS;

    $newDirectory = isset($_PARAMS['PARAM1']) ? $_PARAMS['PARAM1'] : '';

    if (!empty($newDirectory)) {
        // Si le nouveau répertoire est "..", remonter d'un niveau
        if ($newDirectory == '..') {
            $parts = explode('/', rtrim($_SESSION['pwd'], '/'));
            array_pop($parts);

            if (empty($parts)) {
                $_SESSION['pwd'] = '/';
            } else {
                $_SESSION['pwd'] = implode('/', $parts) . '/';
            }

            if ($_SESSION['pwd'] == "") {
                $_SESSION['pwd'] = "/";
            }

            send_response('CD', 'success', 'Directory changed successfully');
        } else {
            $newDirectoryPath = $_SESSION['home'] . $_SESSION['pwd'] . $newDirectory;

            if (is_dir($newDirectoryPath)) {
                $_SESSION['pwd'] = rtrim($_SESSION['pwd'], '/') . '/';
                $_SESSION['pwd'] .= $newDirectory . '/';
                send_response('CD', 'success', 'Directory changed successfully');
            } else {
                send_response('CD', 'error', 'Directory not found');
            }
        }
    } else {
        send_response('CD', 'error', 'Missing parameters');
        error_log('Missing PARAM1 in the $_PARAMS["PARAM1"]');
    }
    exit;
}
function HOME(){// Défini le pwd sur "/"
    global $_PARAMS;
    $_SESSION['pwd'] = '/';
    if($_SESSION['pwd'] == '/'){
        send_response('HOME', 'success', $_SESSION['pwd']);
    }else{
        send_response('HOME', 'error', $_SESSION['pwd']);
    }
        
    
}
function MakeDir() {
    global $_PARAMS;

    $newDirName = isset($_PARAMS['PARAM1']) ? $_PARAMS['PARAM1'] : '';

    if (!empty($newDirName)) {
        $newDirPath = $_SESSION['home'] . $_SESSION['pwd'] . $newDirName;

        if (!file_exists($newDirPath)) {
            mkdir($newDirPath, 0777, true);
            $response = array('status' => 'success', 'message' => 'Directory created successfully');
        } else {
            $response = array('status' => 'error', 'message' => 'Directory already exists');
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Missing parameters');
    }

    send_response('MKDIR', $response['status'], $response['message']);
}
function RemoveDir() { // Analyse de la requête et lancement des actions
    global $_PARAMS;

    $dirToRemove = isset($_PARAMS['PARAM1']) ? $_PARAMS['PARAM1'] : '';

    if (!empty($dirToRemove)) {
        $dirPath = $_SESSION['home'] . $_SESSION['pwd'] . $dirToRemove;

        if (is_dir($dirPath)) {
            $result = removeDirectory($dirPath);
            send_response('RMDIR', $result['status'], $result['message']);
        } else {
            $response = array('status' => 'error', 'message' => 'Directory not found');
            send_response('RMDIR', $response['status'], $response['message']);
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Missing parameters');
        send_response('RMDIR', $response['status'], $response['message']);
    }
}
function removeDirectory($directoryPath) {//logique de suppression de répertoire de la fonction RemoveDir
    if (!is_dir($directoryPath)) {
        return ['status' => 'error', 'message' => 'Le répertoire spécifié n\'existe pas.'];
    }

    $files = array_diff(scandir($directoryPath), ['.', '..']);

    foreach ($files as $file) {
        $filePath = $directoryPath . '/' . $file;
        if (is_dir($filePath)) {
            removeDirectory($filePath);
        } else {
            unlink($filePath);
        }
    }

    if (rmdir($directoryPath)) {
        return ['status' => 'success', 'message' => 'Le répertoire a été supprimé avec succès.'];
    } else {
        return ['status' => 'error', 'message' => 'Erreur lors de la suppression du répertoire.'];
    }
}
switch($CMD) {
    case "LOGIN"    :  LOGIN()    ;        break;
    case "LOGOUT"   :  LOGOUT()   ;        break;
    case "WHOAMI"   :  WHOAMI()   ;        break;
    case "DIR"      :  DIRECTORY();        break;
    case "CD"       :  CD()       ;        break;
    case "HOME"     :  HOME()     ;        break;
    case "MKDIR"    :  MakeDir()  ;        break;
    case "RMDIR"    :  RemoveDir();        break;


    default:
    // Envoyer une réponse d'erreur au format JSON pour les commandes non reconnues
    $response = array('status' => 'error', 'message' => 'Unknown command');
    echo json_encode($response);
    break;
}
?>