<?php
session_start();
date_default_timezone_set('Europe/Paris');

if (count($_POST)) $_PARAMS = $_POST;
else $_PARAMS = $_GET;

$CMD = isset($_PARAMS['CMD']) ? $_PARAMS['CMD'] : "Aucune commande";

function cmd_login($username, $password) {
    $response = array();

    if (!isset($_SESSION['username'])) {
        if (($username === 'Dupont' || $username === 'Durand') && $password === 'abc123') {
            $_SESSION['username'] = $username;
            $response['success'] = true;
            $response['message'] = 'Connection reussie';
        } else {
            $response['success'] = false;
            $response['message'] = 'Mauvais mot de passe ou utilisateur';
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Vous êtes déjà connecté';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function cmd_logout() {
    $_SESSION = [];
    $response['success'] = true;
    $response['message'] = "Déconnexion réussie";

    header('Content-Type: application/json');
    echo json_encode($response);
}

function cmd_whoami() {
    $response = array();

    if (isset($_SESSION['username'])) {
        $response['success'] = true;
        $response['username'] = $_SESSION['username'];
    } else {
        $response['success'] = false;
        $response['message'] = "Vous n'êtes pas connecté.";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function cmd_dir() {
    $response = array();

    if (isset($_SESSION['username'])) {
        $directory = './users/' . $_SESSION['username'];
        $scandir = scandir($directory);
        $files = array();

        foreach ($scandir as $fichier) {
            if ($fichier != "." && $fichier != "..") {
                if (is_file($directory . '/' . $fichier)) {
                    $fileInfo = array(
                        'type' => 'FILE',
                        'name' => $fichier,
                        'size' => filesize($directory . '/' . $fichier),
                        'lastModified' => date("d-m-Y H:i:s", filemtime($directory . '/' . $fichier))
                    );
                    $files[] = $fileInfo;
                } elseif (is_dir($directory . '/' . $fichier)) {
                    $dirInfo = array(
                        'type' => 'DIR',
                        'name' => $fichier,
                        'lastModified' => date("d-m-Y H:i:s", filemtime($directory . '/' . $fichier))
                    );
                    $files[] = $dirInfo;
                } else {
                    $files[] = $fichier;
                }
            }
        }

        $response['success'] = true;
        $response['files'] = $files;
    } else {
        $response['success'] = false;
        $response['message'] = "Vous n'êtes pas connecté.";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

if ($CMD === 'Login') cmd_login($_PARAMS['PARAM1'], $_PARAMS['PARAM2']);
if ($CMD === 'Logout') cmd_logout();
if ($CMD === 'Whoami') cmd_whoami();
if ($CMD === 'Dir') cmd_dir();
?>
