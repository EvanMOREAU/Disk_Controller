<?php
use GestionFichiers\Fichier;
use GestionDossiers\Dossier;

include 'autoload.php';

print("Utilisez les namespaces\n\n");

$dossier = new Dossier();
$dossier->nom = 'monDossier';
$dossier->newDir();

$fichier = new Fichier();
$fichier->nom = "monDossier/monFichier.txt";
$fichier->newFile();
$fichier->writeFile("Bonjour les SIO !");

print($fichier->readFile());
print("\n");
print_r($dossier->lister());

// $fichier->delFile();
// $dossier->delDir();

?>