<?php
// Projet TraceGPS - services web
// fichier :  api/services/EnvoyerPosition.php
// Dernière mise à jour : 18/10/2019 par Corentin

// Rôle : ce service web permet à un utilisateur authentifié d'envoyer sa position.
// Paramètres à fournir :
// •	pseudo : le pseudo de l'utilisateur
// •	mdp : le mot de passe de l'utilisateur hashé en sha1
// •	idTrace : l'id de la trace dont le point fera partie
// •	dateHeure : la date et l'heure au point de passage (format 'Y-m-d H:i:s')
// •	latitude : latitude du point de passage
// •	longitude : longitude du point de passage
// •	altitude : altitude du point de passage
// •	rythmeCardio : rythme cardiaque au point de passage (ou 0 si le rythme n'est pas mesurable)
// •	lang : le langage utilisé pour le flux de données ("xml" ou "json")

// Description du traitement :
// •	Vérifier que les données transmises sont complètes
// •	Vérifier l'authentification de l'utilisateur
// •	Vérifier l'existence du numéro de trace
// •	Vérifier que la trace appartient bien à l'utilisateur
// •	Vérifier que la trace n'est pas encore terminée
// •	Enregistrer le point dans la base de données
// •	Retourner l'id du point


// Les paramètres doivent être passés par la méthode GET :
//     http://<hébergeur>/tracegps/api/EnvoyerPosition?pseudo=europa&mdp=
//     13e3668bbee30b004380052b086457b014504b3e&idTrace=3&dateHeure=2018-01-19 13:10:28
//     &latitude=48.2159&longitude=-1.5485&altitude=110&rythmeCardio=86&lang=xml


// connexion du serveur web à la base MySQL
$dao = new DAO();

// Récupération des données transmises
$pseudo = ( empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdp = ( empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$idTrace = ( empty($this->request['idTrace'])) ? "" : $this->request['idTrace'];
$dateHeure = ( empty($this->request['dateHeure'])) ? "" : $this->request['dateHeure'];
$latitude = ( empty($this->request['latitude'])) ? "0" : $this->request['latitude'];
$longitude = ( empty($this->request['longitude'])) ? "0" : $this->request['longitude'];
$altitude = ( empty($this->request['altitude'])) ? "0" : $this->request['altitude'];
$rythmeCardio = ( empty($this->request['rythmeCardio'])) ? "0" : $this->request['rythmeCardio'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];
$idPoint = null;
$laTrace = $dao->getUneTrace($idTrace);

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET")
{	$msg = "Erreur : méthode HTTP incorrecte.";
    $code_reponse = 406;
}
else {
    // Les paramètres doivent être présents
    if ($pseudo == '' || $mdp == '' || $idTrace == '' || $dateHeure == '' || $latitude == '' || $longitude == '' || $altitude == '' || $rythmeCardio == '') {
        $msg = "Erreur : données incomplètes ou incorrectes.";
        $msg .= "pseudo=" . $pseudo;
        $msg .= "mdp=" . $mdp;
        $msg .= "idTrace=" . $idTrace;
        $msg .= "dateHeure=" . $dateHeure;
        $msg .= "latitude=" . $latitude;
        $msg .= "longitude=" . $longitude;
        $msg .= "altitude=" . $altitude;
        $msg .= "rythmeCardio=" . $rythmeCardio;
        
        
        $code_reponse = 400;
    }
    else {
        if ( $dao->getNiveauConnexion($pseudo, $mdp) == 0 ) {
            $msg = "Erreur : authentification incorrecte.";
            $code_reponse = 401;
        }
        else {
            if ( $laTrace == null ) {
                $msg = "Erreur : le numéro de trace n'existe pas.";
                $code_reponse = 401;
            }
            else {
                if ( $laTrace->getIdUtilisateur() != $dao->getUnUtilisateur($pseudo)->getId()) {
                    $msg = "Erreur : le numéro de trace ne correspond pas à cet utilisateur.";
                    $code_reponse = 401;
                }
                else {
                    if ( $laTrace->getTerminee()) {
                        $msg = "Erreur : la trace est déjà terminée.";
                        $code_reponse = 401;
                    }
                    else {
                        $idPoint = $laTrace->getNombrePoints() + 1;
                        $unPoint = new PointDeTrace($idTrace, $idPoint, $latitude, $longitude, $altitude, $dateHeure, $rythmeCardio, 0, 0, 0);
                        $laTrace->ajouterPoint($unPoint);
                        $dernierPoint = $laTrace->getLesPointsDeTrace()[$laTrace->getNombrePoints() - 1];
                        $ok = $dao->creerUnPointDeTrace($dernierPoint);
                        
                        if ( ! $ok) {
                            $msg = "Erreur : problème lors de l'enregistrement du point.";
                            $code_reponse = 500;
                            $idPoint = null;
                        }
                        else {
                            $msg = "Point créé.";
                            $code_reponse = 201;
                        }
                    }
                }
            }
        }
    }
}
// ferme la connexion à MySQL :
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    $content_type = "application/xml; charset=utf-8";      // indique le format XML pour la réponse
    $donnees = creerFluxXML ($msg, $idPoint);
}
else {
    $content_type = "application/json; charset=utf-8";      // indique le format Json pour la réponse
    $donnees = creerFluxJSON ($msg, $idPoint);
}

// envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);

// fin du programme (pour ne pas enchainer sur les 2 fonctions qui suivent)
exit;

// ================================================================================================

// création du flux XML en sortie
function creerFluxXML($msg, $idPoint)
{
    /* Exemple de code XML
    <?xml version="1.0" encoding="UTF-8"?>
    <data>
      <reponse>Point créé.</reponse>
      <donnees>
          <id>6</id>
      </donnees>
    </data>
    */
    
    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();
    
    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';
    
    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web EnvoyerPosition - BTS SIO - Lycée De La Salle - Rennes');
    // place ce commentaire à la racine du document XML
    $doc->appendChild($elt_commentaire);
    
    // crée l'élément 'data' à la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    
    // place l'élément 'reponse' juste après l'élément 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);
    
    // place l'élément 'donnees' après l'élément 'reponse'
    $elt_donnees = $doc->createElement('donnees');
    $elt_data->appendChild($elt_donnees);
    
    if ($idPoint != null) {
        // place l'élément 'id' juste après l'élément 'donnees'
        $elt_id = $doc->createElement('id', $idPoint);
        $elt_donnees->appendChild($elt_id);
    }

    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    return $doc->saveXML();
}

// ================================================================================================

// création du flux JSON en sortie
function creerFluxJSON($msg, $idPoint)
{
    /* Exemple de code JSON
    {
        "data": {
            "reponse": "Point créé."
            "donnees": {
                "id": 7
            }
        }
    }
    */
    
    // construction de l'élément "data"
    $elt_data = ["reponse" => $msg];
    
    if ($idPoint != null) {
        $elt_data += ["donnees" => $idPoint];
    }
    else {
        $elt_data += ["donnees" => []];
    }
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    return json_encode($elt_racine, JSON_PRETTY_PRINT);
}

// ================================================================================================
?>