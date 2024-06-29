<?php

require_once ('./vendor/autoload.php');
require_once ('./class/RendezVous.php');
require_once ('./class/Campaign.php');
require_once ('./class/ApplicationManager.php');
require_once ('./class/SMSMode.php');

use PHPMailer\PHPMailer\PHPMailer;

// Chargement de la configuration
$manager = new ApplicationManager();

if ($argc > 3) {
    $manager->display(" > Nombre de paramètres incorrect");
    $manager->display(" > Lancer la commande comme cela : /path/to/php <inputfile> <optional:path/to/file/config.json> ");
    $manager->display(" > Si aucune parametre est fourni, le programme va recherche le fichier config.json dans le dossier /config");
    $manager->display(" > Utiliez le fichier config.json.sample comme exemple pour créer votre propre fichier config.json");

    die();
}
// Chargement du fichier JSON
if ($argc == 3) {
    // Le nom du fichier a été fourni en parametre
    $manager->setJsonConfigFile($argv[2]);
} else {
    // On prend le fichier par défaut qui doit se trouver dans le dossier config.
    $manager->setJsonConfigFile("./config/config.json");
}
$manager->display(" > Fichier Configuration JSON : " . $manager->getJsonConfigFile());

// Vérification de l'existence du fichier
if (file_exists($manager->getJsonConfigFile())) {
    $manager->display(" > Chargement de la configuration du fichier JSON");
    // Chargement du contenu du fichier
    $contenuJson = file_get_contents($manager->getJsonConfigFile());
    // Décodage du JSON en array PHP
    $configJson = json_decode($contenuJson, true);
    // Vérification de la réussite du décodage
    if ($configJson === null) {
        $manager->display("Erreur lors de la lecture du JSON '" . $manager->getJsonConfigFile() . "'");
        die();
    } else {
        if ($configJson['verboseMode'] == true) {
            $manager->setVerbose(ApplicationManager::VERBOSE_ON);
            $manager->display('   + Mode VERBOSE : Le programme affiche le détail des opérations effectuées');
        } else {
            $manager->setVerbose(ApplicationManager::VERBOSE_OFF);
            $manager->display("   + Mode VERBOSE OFF : Le programme n affiche pas le détail des opérations");
        }
        $manager->setSourceFile($argv[1]);
        $manager->display("   + Fichier Source : " . $manager->getSourceFile());
        $outputFile = $configJson["outputDirectory"] . DIRECTORY_SEPARATOR . basename($manager->getSourceFile(), ".csv") . "-synthese.csv";
        $manager->setOutputFile($outputFile);
        $summaryFile = $configJson["outputDirectory"] . DIRECTORY_SEPARATOR . "summary.csv";
        $manager->setSummaryFile($summaryFile);
        $manager->display("   + Fichier détaillé de synthèse des envois SMS  : " . $manager->getOutputFile());
        $manager->display("   + Fichier de synthèse des envois SMS  : " . $manager->getSummaryFile());
        $manager->display("   + Champ Séparateur CSV : '" . $configJson["csvSeparator"] . "'");
        $manager->display("   + Nombre de lignes à ignorer : " . $configJson["ignoreFirstLines"]);
        $manager->display("   + Ecart maximal par rapport à la date du jour  : " . $configJson["maxIntervalDate"]);
        $manager->display("   + Limite SMS par numéro de téléphone. Si dépassé, il n'y aura pas d'envoi SMS au numéro concerné : " . $configJson["maxSMSByPhoneNumber"]);
        $manager->display("   + Affichage de la trace des envois de SMS par le fournisseur  : " . ($configJson["smsTrace"] == 1 ? "Oui" : "Non"));
        $smsProvider = new SMSMode($configJson['smsTrace']);
        $smsProvider->setApiToken($configJson['smsMode']['apiToken']);
    }
} else {
    $manager->display("Fichier de configuration JSON '" . $manager->getJsonConfigFile() . "' non trouvé.");
    die();
}
if ($configJson['debugMode'] == true) {
    $manager->setMode(ApplicationManager::MODE_DEBUG);
    $manager->display(' > Mode DEBUG : Les SMS ne seront pas envoyés');
} else {
    $manager->setMode(ApplicationManager::MODE_PRODUCTION);
    $manager->display(" > Mode PRODUCTION : Les SMS seront envoyés");
}

$manager->display(" > Vérification de la configuration");
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

$outputSummaryFile = fopen($manager->getSummaryFile(), 'a');
if ($outputSummaryFile) {
    $statusSummaryFile = true;
}


if ($statusSummaryFile == true) {
    $manager->display(" > Ouverture du fichier de Synthèse Historique : OK");
} else {
    $manager->display(" > Ouverture du fichier de Synthèse Historique : ECHEC ");
}

$listeAnomalies = array(); // Liste des Rendez Vous en erreur
$nbEnvois = 0;  // Nb d'Envois réalisés
$nbErreurs = 0; // Nb d'erreurs détectées
$compteurRdv = array();
$listeRendezVous = new Campaign();
$listeRendezVous->setCampaignID($configJson['campaignID']);
$listeRendezVous->setMaxIntervalDate($configJson['maxIntervalDate']);
$listeRendezVous->setMaxSMSByPhoneNumber($configJson['maxSMSByPhoneNumber']);
$listeRendezVous->setSMSProvider($smsProvider);
$idLigne = 1;
// Vérification de l'ouverture du fichier
if ($inputFile) {
    // Lit et affiche chaque ligne du fichier
    while (($data = fgetcsv($inputFile, 0, $configJson['csvSeparator'])) !== false) {
        if ($idLigne > $configJson['ignoreFirstLines']) {
            if (count($data) > 4 && strlen(trim($data[0])) > 1 ) {
                $rendezVous = new RendezVous();
                $rendezVous->setDateAppointment($data[$configJson["mappingField"]["dateAppointment"]]);      // Date du rendez-vous
                $rendezVous->setTimeAppointment($data[$configJson["mappingField"]["timeAppointment"]]);      // Heure du rendez-vous
                $rendezVous->setPhoneNumber($data[$configJson["mappingField"]["phoneNumber"]]);              // Numéro de téléphone
                $rendezVous->setStructure($data[$configJson["mappingField"]["structure"]]);                  // Libellé de la structure
                $rendezVous->setService($data[$configJson["mappingField"]["service"]]);                      // Service    
                    if ($configJson['filterAgreement']['active']== true)  {
                        if ($data[$configJson['mappingField']['smsAgreement']] == $configJson['filterAgreement']['agreementValue']) {
                            $rendezVous->setSmsAgreement(true);
                        } else {
                            $rendezVous->setSmsAgreement(false);
                        }
                }   else {
                    $rendezVous->setSmsAgreement(true);
                }   
                if (isset($data[$configJson["mappingField"]["parameter1"]])) {
                  $rendezVous->setParameter(1,$data[$configJson["mappingField"]["parameter1"]]);
                }
                if (isset($data[$configJson["mappingField"]["parameter2"]])) { 
                    $rendezVous->setParameter(2,$data[$configJson["mappingField"]["parameter2"]]);
                }
                if (isset($data[$configJson["mappingField"]["parameter3"]])) { 
                    $rendezVous->setParameter(2,$data[$configJson["mappingField"]["parameter3"]]);
                }
                if (isset($data[$configJson["mappingField"]["parameter4"]])) { 
                    $rendezVous->setParameter(2,$data[$configJson["mappingField"]["parameter4"]]);
                }
                if (isset($data[$configJson["mappingField"]["parameter5"]])) { 
                    $rendezVous->setParameter(2,$data[$configJson["mappingField"]["parameter5"]]);
                }                                                
                $rendezVous->setTemplateMessage($configJson['messageTemplate']);
                $listeRendezVous->addRendezVous($rendezVous);
            }
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
    $entete = array("Date Rendez Vous", "Heure Rendez Vous", "Numéro de Téléphone", "Structure", "Service", "Numéro de téléphone formaté", "Nombre de Rendez vous", "Code Statut SMS", "Description Statut SMS", "ID SMS");
    fputcsv($outputSuccessFile, $entete, ";");
    foreach ($listeRendezVous->getListeRendezVous() as $rendezVous) {
        $ligne = array();
        if ($rendezVous->getDateAppointment()) {
            $ligne[] = $rendezVous->getDateAppointment()->format("Y-m-d");
        } else {
            $ligne[] = $rendezVous->getOriginalDateAppointment();
        }
        $ligne[] = $rendezVous->getTimeAppointment();
        $ligne[] = $rendezVous->getPhoneNumber();
        $ligne[] = $rendezVous->getStructure();
        $ligne[] = $rendezVous->getService();
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
    // Ecriture de la synthèse de l'envoi dans le fichier synthèse
    $ligne = array();
    $ligne[] =  $listeRendezVous->getCampaignID();
    $ligne[] =  $manager->getSourceFile();
    $ligne[] = date("Y-m-d");
    if ($manager->getMode() == ApplicationManager::MODE_PRODUCTION) {
        $ligne[] = "PRODUCTION";
    } else {
        $ligne[] = "DEBUG";
    }
    $ligne[] = $listeRendezVous->NumberOfRendezVous();
    $nbSMSAEnvoyer = $listeRendezVous->NumberOfRendezVous() - $listeRendezVous->NumberOfRendezVousWithoutSMSAgreement();
    $ligne[] = $nbSMSAEnvoyer;
    $ligne[] = $listeRendezVous->getNbEnvois();
    fputcsv($outputSummaryFile, $ligne,";");
    fclose($outputSummaryFile);
    // Envoi du mail de synthèse
    if (($configJson['mail']['sendReport'] == true)) {
        $manager->display('> Envoi du mail du rapport de synthèse');
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = $configJson['mail']['server'];
        if (isset($configJson['mail']['port'])) {
            $mail->Port = $configJson['mail']['port'];
        }
        $mail->SMTPAuth = $configJson['mail']['SMTPAuth'];
        $mail->CharSet = 'UTF-8';
        $mail->Username = $configJson['mail']['username'];
        $mail->Password = $configJson['mail']['password'];
        $mail->setFrom($configJson['mail']['username'], 'smsmanager');
        $mail->addReplyTo($configJson['mail']['username'], 'smsmanager');
        $mail->addAddress($configJson['mail']['to'], '');
        $mail->isHTML(true);
        if ($listeRendezVous->NumberOfRendezVous() > 0) {
            if ($listeRendezVous->NumberOfRendezVous() == $listeRendezVous->getNbEnvois()) {
                $campaignStatus = "SUCCES";
            } elseif ($listeRendezVous->getNbEnvois() > 0) {
                $campaignStatus = "PARTIEL";
            } else {
                $campaignStatus = "ECHEC";
            }
        }
        else {
              $campaignStatus = "AUCUN RDV";
        }
        if ($manager->getMode() == ApplicationManager::MODE_DEBUG) {
			$prefix = " SIMULATION ";
		} else {
			$prefix = "";
		}
        $mail->Subject = '['.$prefix.'Rapport SMS - ' . $campaignStatus . '] Synthèse des envois SMS '.$configJson['campaignID'];
        $mail->Body = 'Nom du fichier traité : '.basename($manager->getSourceFile(), ".csv").'<br/>Nombre d enregistrements  : ' . $listeRendezVous->NumberOfRendezVous(). "<br/>Nb de SMS à envoyer : ".$nbSMSAEnvoyer."<br/>Nb de SMS de rappels de rendez-vous envoyés : " . $listeRendezVous->getNbEnvois() . "<br/>Nb d'anomalies identifiées : " . $listeRendezVous->getNbErreurs();
        $mail->addAttachment($manager->getOutputFile());
        if (!$mail->send()) {
            $manager->display("Erreur lors de l'envoi du mail de synthèse " . $mail->ErrorInfo);
        } else {
            $manager->display('Le mail de synthèse a été envoyé.');
        }
    }
}
