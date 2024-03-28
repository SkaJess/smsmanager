<?php

require_once('./vendor/autoload.php');
require_once("./config/config.inc.php");
require_once('./class/RendezVous.php');
require_once('./class/Campaign.php');
require_once('./class/ApplicationManager.php');
require_once('./class/SMSMode.php');

use PHPMailer\PHPMailer\PHPMailer;

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

// Prise en compte de l'argument si celui si est passé en paramétre
if ($argv[1]) {
    $sourceFile = $argv[1];
} else {
    $sourceFile = $config['sourceFile']['name'];
}
$manager->setSourceFile($sourceFile);
$manager->display(" > Fichier Source : " . $manager->getSourceFile());

if ($argv[2]) {
    $outputFile = $argv[2] . DIRECTORY_SEPARATOR . basename($manager->getSourceFile(), ".csv") . "-synthese.csv";
} else {
    $outputFile = $config['outputFile']['name'];
}

$manager->setOutputFile($outputFile);
$manager->display(" > Fichier des envois SMS  : " . $manager->getOutputFile());
$manager->display("");
$manager->display("Vérification de la configuration");
$statusInputFile = false;
$statusOutputFile = false;
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

$outputSuccessFile = fopen($manager->getOutputFile(), 'w');
if ($outputSuccessFile) {
    fprintf($outputSuccessFile, chr(0xEF) . chr(0xBB) . chr(0xBF)); // Pour encodage UTF8
    $statusOutputFile = true;
}

if ($statusOutputFile == true) {
    $manager->display(" > Ouverture du fichier des envois SMS : OK");
} else {
    $manager->display(" > Ouverture du fichier des envois SMS : ECHEC ");
}

$listeAnomalies = array(); // Liste des Rendez Vous en erreur
$nbEnvois = 0;  // Nb d'Envois réalisés
$nbErreurs = 0; // Nb d'erreurs détectées
$compteurRdv = array();
$listeRendezVous = new Campaign();
$listeRendezVous->setMaxIntervalDate($config['sourceFile']['maxIntervalDate']);
$smsProvider = new SMSMode($config['smsTrace']);
$smsProvider->setApiToken($config['smsMode']['apiToken']);
$listeRendezVous->setSMSProvider($smsProvider);
$idLigne = 1;
// Vérification de l'ouverture du fichier
if ($inputFile) {
    // Lit et affiche chaque ligne du fichier
    while (($data = fgetcsv($inputFile, 0, $config['csvSeparator'])) !== false) {
        if ($idLigne > $config['sourceFile']['ignoreFirstLines']) {
            $rendezVous = new RendezVous();
            $rendezVous->setDateAppointment($data[0]);      // Date du rendez-vous
            $rendezVous->setTimeAppointment($data[1]);      // Heure du rendez-vous
            $rendezVous->setPhoneNumber($data[2]);          // Numéro de téléphone
            $rendezVous->setStructure($data[3]);            // Libellé de la structure
            $rendezVous->setService($data[4]);              // Service            
            $listeRendezVous->addRendezVous($rendezVous);
        }
        $idLigne++;
    }
    $manager->display("");
    $manager->display("Chargement des " . $listeRendezVous->NumberOfRendezVous() . " rendez-vous");
    $manager->display("Traitement des Rendez Vous");
    $listeRendezVous->send($manager);
    $manager->display("");
    $manager->display("Fin de traitement des Rendez Vous");
    $manager->display(" > Nb de SMS de rappels de rendez vous transmis: " . $listeRendezVous->getNbEnvois());
    $manager->display(" > Nb d'anomalies identifiées: " . $listeRendezVous->getNbErreurs());
    $manager->display("");
    $manager->display("Génération du fichier de sortie");
    $entete = array("Structure", "Numéro de Téléphone", "Nom Médecin", "Service", "Date Rendez Vous", "Heure Rendez Vous", "Numéro de téléphone formatté", "Nombre de Rendez vous", "Code Statut SMS", "Description Statut SMS", "ID SMS");
    fputcsv($outputSuccessFile, $entete, ";");
    foreach ($listeRendezVous->getListeRendezVous() as $rendezVous) {
        $ligne = array();
        $ligne[] = $rendezVous->getStructure();
        $ligne[] = $rendezVous->getPhoneNumber();
        $ligne[] = $rendezVous->getDoctorName();
        $ligne[] = $rendezVous->getService();
        if ($rendezVous->getDateAppointment()) {
            $ligne[] = $rendezVous->getDateAppointment()->format("Y-m-d");
        } else {
            $ligne[] = $rendezVous->getOriginalDateAppointment();
        }
        $ligne[] = $rendezVous->getTimeAppointment();
        $ligne[] = $rendezVous->getFormatedPhoneNumber();
        $ligne[] = $listeRendezVous->getNbRdvByPhoneNumber($rendezVous->getFormatedPhoneNumber());
        $ligne[] = $rendezVous->getSmsStatusCode();
        $ligne[] = $rendezVous->getSmsstatusDescription();
        $ligne[] = $rendezVous->getSmsId();
        fputcsv($outputSuccessFile, $ligne, ";");
    }
    // Ferme le fichier
    fclose($inputFile);
    fclose($outputSuccessFile);

    // Envoi du mail de synthèse
    if (($config['mail']['sendReport'] == true)) {


        $manager->display('Envoi du mail du rapport de synthèse');
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = $config['mail']['server'];
        if (isset($config['mail']['port'])) {
            $mail->Port = $config['mail']['port'];
        }
        $mail->SMTPAuth = $config['mail']['SMTPAuth'];
        $mail->CharSet = 'UTF-8';
        $mail->Username = $config['mail']['username'];
        $mail->Password = $config['mail']['password'];
        $mail->setFrom($config['mail']['username'], 'smsmanager');
        $mail->addReplyTo($config['mail']['username'], 'smsmanager');
        $mail->addAddress($config['mail']['to'], '');
        $mail->isHTML(true);
        $mail->Subject = '[SMS Mode] Synthèse des envois de confirmation de rendez-vous par SMS';
        $mail->Body = 'Nombre de rendez-vous  : ' . $listeRendezVous->NumberOfRendezVous() . "<br/>Nb de SMS de rappels de rendez-vous envoyés : " . $listeRendezVous->getNbEnvois() . "<br/>Nb d'anomalies identifiées : " . $listeRendezVous->getNbErreurs();
        $mail->addAttachment($manager->getOutputFile());
        if (!$mail->send()) {
            $manager->display("Erreur lors de l'envoi du mail de synthèse " . $mail->ErrorInfo);
        } else {
            $manager->display('Le mail de synthèse a été envoyé.');
        }
    }
}