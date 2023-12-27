<?php

require_once('./vendor/autoload.php');
require_once("./config/config.inc.php");
require_once('./class/RendezVous.php');
require_once('./class/Campaign.php');
require_once('./class/ApplicationManager.php');
require_once('./class/SMSMode.php');

// Chargement de la configuration
$manager = new ApplicationManager();
$manager->display("");
$manager->display("Chargement de la configuration");
if ($config['verboseMode'] == true) {
    $manager->setVerbose(ApplicationManager::VERBOSE_ON);
    $manager->display(' > Mode VERBOSE : Le programme affiche le détail des opérations effectuées');
} else {
    $manager->setVerbose(ApplicationManager::VERBOSE_OFF);
    $manager->display(" > Mode VERBOSE OFF : Le programme n affiche pas le détail des opérations");
}

if ($config['debugMode'] == true) {
    $manager->setMode(ApplicationManager::MODE_DEBUG);
    $manager->display(' > Mode DEBUG : Les SMS ne seront pas envoyés');
} else {
    $manager->setMode(ApplicationManager::MODE_PRODUCTION);
    $manager->display(" > Mode PRODUCTION : Les SMS seront envoyés");
}
$manager->setSourceFile($config['sourceFile']['name']);
$manager->display(" > Fichier Source : " . $manager->getSourceFile());
$manager->setSuccessOutputFile($config['successOutputFile']);
$manager->display(" > Fichier des envois SMS  : " . $manager->getSuccessOutputFile());
$manager->setErrorsOutputFile($config['errorsOutputFile']);
$manager->display(" > Fichier des SMS en erreur : " . $manager->getErrorsOutputFile());
$manager->display("");
$manager->display("Vérification de la configuration");
$statusInputFile = false;
$statusOutputErrosFile = false;
$statusOutputSuccessFile = false;
if (file_exists($manager->getSourceFile())) {
    $inputFile = fopen($manager->getSourceFile(), 'r');
    if ($inputFile) {
        $statusInputFile = true;
    }
}
if ($statusInputFile == true) {
    $manager->display(" > Ouverture du fichier source : OK");
} else {
    $manager->display(" > Ouverture du fichier source : ECHEC ");
    throw new \Exception("Fichier source introuvable ou droits d'accès insuffisants :" . $manager->getSourceFile());
}

if (file_exists($manager->getErrorsOutputFile())) {
    $outputSuccessFile = fopen($manager->getErrorsOutputFile(), 'a');
    if ($outputSuccessFile) {
        $statusOutputSuccessFile = true;
    }
}
if ($statusOutputSuccessFile == true) {
    $manager->display(" > Ouverture du fichier des envois SMS : OK");
} else {
    $manager->display(" > Ouverture du fichier des envois SMS : ECHEC ");
}

if (file_exists($manager->getErrorsOutputFile())) {
    $outputErrorsFile = fopen($manager->getErrorsOutputFile(), 'a');
    if ($outputErrorsFile) {
        $statusOutputErrorsFile = true;
    }
}
if ($statusOutputErrorsFile == true) {
    $manager->display(" > Ouverture du fichier des SMS en erreur : OK");
} else {
    $manager->display(" > Ouverture du fichier des SMS en erreur : ECHEC ");
}

$listeAnomalies = array(); // Liste des Rendez Vous en erreur
$nbEnvois = 0;  // Nb d'Envois réalisés
$nbErreurs = 0; // Nb d'erreurs détectées
$compteurRdv = array();
$listeRendezVous = new Campaign();
$smsProvider = new SMSMode($config['debugMode']);
$smsProvider->setApiToken($config['smsMode']['apiToken']);
$listeRendezVous->setSMSProvider($smsProvider);
$firstLine = true;
// Vérification de l'ouverture du fichier
if ($inputFile) {
    // Lit et affiche chaque ligne du fichier
    while (($data = fgetcsv($inputFile, 0, $config['csvSeparator'])) !== false) {
        if (($firstLine == false) || ($config['sourceFile']['ignoreFirstLine'] == false)) {
            $rendezVous = new RendezVous();
            $rendezVous->setStructure($data[0]);
            $rendezVous->setPhoneNumber($data[1]); // Numéro de téléphone
            $rendezVous->setDoctorName($data[2]);  // Nom du médecin
            $rendezVous->setService($data[3]);     // Service
            $rendezVous->setDateAppointment($data[4]); // Date du rendez-vous
            $rendezVous->setTimeAppointment($data[5]); // Heure du rendez-vous
            $listeRendezVous->addRendezVous($rendezVous);
        }
        $firstLine = false;
    }
    $manager->display("");
    $manager->display("Chargement des " . $listeRendezVous->NumberOfRendezVous() . " rendez-vous");
    $manager->display("Traitement des Rendez Vous");
    $listeRendezVous->send($manager);
    $manager->display("");
    $manager->display("Nb de SMS de rappels de rendez vous transmis: " . $listeRendezVous->getNbEnvois());
    $manager->display("Nb d'anomalies identifiées: " . $listeRendezVous->getNbErreurs());
    // Ferme le fichier
    fclose($inputFile);
}

?>