<?php
// Projet TraceGPS
// fichier : modele/DAO.test1.php
// Rôle : test de la classe DAO.class.php
// Dernière mise à jour : xxxxxxxxxxxxxxxxx par xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

// Le code des tests restant à développer va être réparti entre les membres de l'équipe de développement.
// Afin de limiter les conflits avec GitHub, il est décidé d'attribuer un fichier de test à chaque développeur.
// Développeur 1 : fichier DAO.test1.php
// Développeur 2 : fichier DAO.test2.php
// Développeur 3 : fichier DAO.test3.php
// Développeur 4 : fichier DAO.test4.php

// Quelques conseils pour le travail collaboratif :
// avant d'attaquer un cycle de développement (début de séance, nouvelle méthode, ...), faites un Pull pour récupérer
// la dernière version du fichier.
// Après avoir testé et validé une méthode, faites un commit et un push pour transmettre cette version aux autres développeurs.
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Test de la classe DAO</title>
	<style type="text/css">body {font-family: Arial, Helvetica, sans-serif; font-size: small;}</style>
</head>
<body>

<?php
// connexion du serveur web à la base MySQL
include_once ('DAO.class.php');
$dao = new DAO();


// test de la méthode getLesUtilisateursAutorisant($idUtilisateur
// modifié par Guillaume ONFRAY le 08/10/2019
echo "<h3>Test de getLesUtilisateursAutorisant(idUtilisateur) : </h3>";
$lesUtilisateurs = $dao->getLesUtilisateursAutorisant(4);
$nbReponses = sizeof($lesUtilisateurs);
echo "<p>Nombre d'utilisateurs autorisant l'utilisateur 4 à voir leurs parcours : " . $nbReponses . "</p>";
// affichage des utilisateurs
foreach ($lesUtilisateurs as $unUtilisateur)
{   echo ($unUtilisateur->toString());
echo ('<br>');
}

// test de la méthode creerUneAutorisation
// Modifié par Guillaume ONFRAY le 09/10/2019
echo "<h3>Test de creerUneAutorisation : </h3>";
if ($dao->creerUneAutorisation(2, 1)) $ok = "oui"; else $ok="non";
echo "<p>la création de l'autorisation de l'utilisateur 2 vers l'utilisateur 1 a réussi : <b>" .$ok. "</b><br>";
// la même autorisation ne peut pas être enregistrée 2 fois
if ($dao->creerUneAutorisation(2, 1)) $ok = "oui"; $ok = "non";
echo "<p>La création de l'autorisation de l'utilisateur 2 vers l'utilisateur 1 a réussi : <b>" .$ok."</b><br>";



// Test de la méthode creerUnPointDeTrace
// modifié par Guillaume ONFRAY le 15/10/2019
echo "<h3>Test de creerUnPointDeTrace : </h3>";
// on affiche d'abord le nombre de points (5) de la trace 1
$lesPoints = $dao->getLesPointsDeTrace(1);
$nbPoints = sizeof($lesPoints);
echo "<p>Nombre de points de la trace 1 : " .$nbPoints. "</p>";
// on crée ensuite un sixième point et on l'ajoute à la trace 1
$unIdTrace = 1;
$unID = 6;
$uneLatitude = 48.20;
$uneLongitude = -1.55;
$uneAltitude = 50;
$uneDateHeure = date('Y-m-d H:i:s', time());
$unRythmeCardio = 80;
$unTempsCumule = 0;
$uneDistanceCumulee = 0;
$uneVitesse = 15;
$unPoint = new PointDeTrace($unIdTrace, $unID, $uneLatitude, $uneLongitude, $uneAltitude, $uneDateHeure, $unRythmeCardio, $unTempsCumule, $uneDistanceCumulee, $uneVitesse);
$ok = $dao->creerUnPointDeTrace($unPoint);
// on affiche à nouveau le nombre de points (6) de la trace 1
$lesPoints = $dao->getLesPointsDeTrace(1);
$nbPoints = sizeof($lesPoints);
echo "<p>Nombre de points de la trace 1 : " . $nbPoints . "</p>";
echo ('<br>');


// test de la méthode getLesTraces($idUtilisateur)
// Modifié par Guillaume ONFRAY le 15/10/2019
// test de la méthode getLesTraces($idUtilisateur) ------------------------------------------------
// modifié par Jim le 14/8/2018
echo "<h3>Test de getLesTraces(idUtilisateur) : </h3>";
$lesTraces = $dao->getLesTraces(2);
$nbReponses = sizeof($lesTraces);
echo "<p>Nombre de traces de l'utilisateur 2 : " . $nbReponses . "</p>";
// affichage des traces
foreach ($lesTraces as $uneTrace)
{   echo ($uneTrace->toString());
echo ('<br>');
}

// test de la méthode supprimerUneTrace -----------------------------------------------------------
// modifié par Guillaume ONFRAY le 15/10/2019
echo "<h3>Test de supprimerUneTrace : </h3>";
$ok = $dao->supprimerUneTrace(22);
if ($ok) {
    echo "<p>Trace bien supprimée !</p>";
}
else {
    echo "<p>Echec lors de la suppression de la trace !</p>";
}


// ferme la connexion à MySQL :
unset($dao);
?>

</body>
</html>