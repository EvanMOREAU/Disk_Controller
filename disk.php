DISK : <br>

<?php

//--- récupérer les paramètres
if(count($_POST)) $_PARAMS = $_POST;
else              $_PARAMS = $_GET;

$CMD = $_PARAMS['CMD'];
echo $CMD;  

?>