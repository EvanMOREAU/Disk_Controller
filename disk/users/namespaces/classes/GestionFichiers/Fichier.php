<?php

namespace GestionFichiers;

class Fichier{

    public $nom;

    public function newFile(){
        touch($this->nom);
    }

    public function writeFile($content){
        file_put_contents($this->nom, $content);
    }

    public function readFile(){
        return file_get_contents($this->nom);
    }

    public function delFile(){
        unlink ( $this->nom);
    }

}

$fichier = new Fichier();
$fichier->nom = "coucou.txt";
$fichier->newFile();
$fichier->writeFile('coucou ca va ?');
print ($fichier->readFile());

// $fichier->delFile();

?>