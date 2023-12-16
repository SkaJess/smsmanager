<?php
require_once("./config/config.inc.php");
// Chemin vers le fichier CSV
$csvFile = $config['fichierSource'];

echo "$csvFile";

// Vérification de l'existence du fichier
if (file_exists($csvFile)) {
    // Ouvre le fichier en lecture
    $file = fopen($csvFile, 'r');

    // Vérification de l'ouverture du fichier
    if ($file) {
        // Lit et affiche chaque ligne du fichier
        while (($data = fgetcsv($file,0,";")) !== false) {
            var_dump($data);
            $phoneNumber = $data[0]; // Numéro de téléphone
            $doctorName = $data[1];  // Nom du médecin
            $service = $data[2];     // Service
            $dateAppointment = $data[3]; // Date du rendez-vous
            $timeAppointment = $data[4]; // Heure du rendez-vous

            // Affichage des données
            echo "Numéro de téléphone : $phoneNumber<br>";
            echo "Nom du médecin : $doctorName<br>";
            echo "Service : $service<br>";
            echo "Date du rendez-vous : $dateAppointment<br>";
            echo "Heure du rendez-vous : $timeAppointment<br>";
            echo "<hr>";
        }

        // Ferme le fichier
        fclose($file);
    } else {
        echo "Impossible d'ouvrir le fichier CSV.";
    }
} else {
    echo "Le fichier CSV n'existe pas.";
}
?>


