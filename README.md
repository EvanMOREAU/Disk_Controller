# Disk_Controller


====================
SESSION PAR UTILISATEUR
user:'DUPONT'                           le username de l'utilisateur
home:'C:/wamp64/www/users/dupont'       le home de l'utilisateur
pwd :   '/'             dupont à sa racine (home)
        '/plans/'       dupont dans son sous-répertoire /plans
        '/plans/appart' dupont dans son sous-répertoire /plans/appart


CMD home ramène à home:'C:/wamp64/www/users/dupont'   

====================

DIR disk.php? CMD=DIR
Rôle    : Renvoyer le contenu du répertoire courant de l'utilisateur courant
Serveur : Ne modifie rien sur le serveur
Réponse {
        cmd:"disk.php ? CMD=DIR",
        status:"OK",
        content:[
                {type:"FILE", name:"fichier1.txt",date:"18/10/2023-14:34:26",size:15},
                {type:"FILE",name:"fichier2.txt",date:"18/10/2023-14:44:23",size:22},
                {type:"DIR",name:"dir1",date:"17/10/2023-14:44:29"},
                {type:"DIR",name:"dir2",date:"18/10/2022-14:44:43"},
        ]
}
-------------------------------------------------------------------
date:"18/10/2023-14:44:43"

date:"2022/10/18-14:44:43"
date:"2023/10/18-14:44:43" permet des comparaisons de strings

date:"20221018144443"           ce serait dégueulasse de faire ça #RIQUETTE


---------------------------------------------------------------------
SESSION
=======
user    DUPONT
home    c:/wamp64/www/USERS
pwd     /                       répertoire courant de 