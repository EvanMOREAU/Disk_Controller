<?php

namespace GestionDossiers;

class Dossier{
    
    public $nom;

    public function newDir(){
        mkdir($this->nom);
    }

    public function delDir(){
        rmdir($this->nom);
    }

    public function lister(){
       return scandir($this->nom);
    }

}

// $dossier = new Dossier();
// $dossier->nom = "yoo";
// $dossier->newDir();
// $dossier->delDir();

?>