<?php
require_once("./config/config.inc.php");
require_once('./class/RendezVous.php');
require_once('./class/ApplicationManager.php');

// Chargement de la configuration
$manager = new ApplicationManager();
if ($config['debugMode'] == true) {
    $manager->setMode(ApplicationManager::MODE_DEBUG);
    $manager->display('Mode DEBUG : Les SMS ne seront pas envoyés');
} else {
    $manager->setMode(ApplicationManager::MODE_PRODUCTION);
    $manager->display("Mode PRODUCTION : Les SMS seront envoyés");
}
$manager->display("Chargement de la configuration");
$manager->setSourceFile($config['sourceFile']);
$manager->display("> Fichier Source : " . $manager->getSourceFile());
$manager->setSuccessOutputFile($config['successOutputFile']);
$manager->display("> Fichier des envois SMS avec succès : " . $manager->getSuccessOutputFile());
$manager->setErrorsOutputFile($config['errorsOutputFile']);
$manager->display("> Fichier des SMS en erreur : " . $manager->getErrorsOutputFile());
$manager->display('Vérification de la configuration');
$listeRendezVous = array(); // Liste des Rendez Vous à traiter
$listeAnomalies = array(); // Liste des Rendez Vous en erreur
$nbEnvois = 0;  // Nb d'Envois réalisés
$nbErreurs = 0; // Nb d'erreurs détectés


// Vérification de l'existence du fichier
if (file_exists($manager->getSourceFile())) {
    // Ouvre le fichier en lecture
    $file = fopen($manager->getSourceFile(), 'r');

    // Vérification de l'ouverture du fichier
    if ($file) {
        // Lit et affiche chaque ligne du fichier
        while (($data = fgetcsv($file, 0, $config['csvSeparator'])) !== false) {
            $rendezVous = new RendezVous();
            $rendezVous->setStructure($data[0]);
            $rendezVous->setPhoneNumber($data[1]); // Numéro de téléphone
            $rendezVous->setDoctorName($data[2]);  // Nom du médecin
            $rendezVous->setService($data[3]);     // Service
            $rendezVous->setDateAppointment($data[4]); // Date du rendez-vous
            $rendezVous->setTimeAppointment($data[5]); // Heure du rendez-vous
            $listeRendezVous[] = $rendezVous;
        }

        $manager->display("Chargement de " . count($listeRendezVous) . " rendez-vous");
        $manager->display("Traitement des Rendez Vous");


        // Traitement du fichier 
        foreach ($listeRendezVous as $rdv) {
            if ($rdv->envoyerSMSRappel() == true) {
                $nbEnvois++;
            } else {
                $listeAnomalies[] = $rdv;
                $nbErreurs++;
            }
        }
        $manager->display("Nb de SMS de rappels de rendez vous transmis: " . $nbEnvois);
        $manager->display("Nb d'anomalies identifiées: " . $nbErreurs);
        // Ferme le fichier
        fclose($file);
    } else {
        $manager->display("Impossible d'ouvrir le fichier CSV.");
    }
} else {
    $manager->display("Le fichier CSV n'existe pas.");
}
?>