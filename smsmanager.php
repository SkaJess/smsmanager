<?php
require_once("./config/config.inc.php");
require_once('./class/RendezVous.php');


// Chemin vers le fichier CSV
$csvFile = $config['fichierSource'];
$listeRendezVous = array(); // Liste des Rendez Vous à traiter
$listeAnomalies  = array(); // Liste des Rendez Vous en erreur
$nbEnvois = 0;  // Nb d'Envois réalisés
$nbErreurs = 0; // Nb d'erreurs détectés

echo "Fichier Source : $csvFile\n";

// Vérification de l'existence du fichier
if (file_exists($csvFile)) {
    // Ouvre le fichier en lecture
    $file = fopen($csvFile, 'r');

    // Vérification de l'ouverture du fichier
    if ($file) {
        // Lit et affiche chaque ligne du fichier
        while (($data = fgetcsv($file,0, $config['delimitateurCSV'])) !== false) {
            $rendezVous  = new RendezVous(); 
            $rendezVous->setStructure($data[0]);
            $rendezVous->setPhoneNumber($data[1]); // Numéro de téléphone
            $rendezVous->setDoctorName($data[2]);  // Nom du médecin
            $rendezVous->setService($data[3]);     // Service
            $rendezVous->setDateAppointment($data[4]); // Date du rendez-vous
            $rendezVous->setTimeAppointment($data[5]); // Heure du rendez-vous
            $listeRendezVous[] = $rendezVous;
        }

        echo "Chargement de ".count($listeRendezVous)." rendez-vous .\n";
        echo "Traitement des Rendez Vous \n";


        // Traitement du fichier 
        foreach($listeRendezVous as $rdv) {
            if ($rdv->envoyerSMSRappel() == true) { 
                $nbEnvois++;
            }else {
                $listeAnomalies[] = $rdv;
                $nbErreurs++;
             }
        }
        echo "Nb de SMS de rappels de rendez vous transmis: ".$nbEnvois."\n";
        echo "Nb d'anomalies identifiées: ".$nbErreurs."\n";
        // Ferme le fichier
        fclose($file);
    } else {
        echo "Impossible d'ouvrir le fichier CSV.";
    }
} else {
    echo "Le fichier CSV n'existe pas.";
}
?>


