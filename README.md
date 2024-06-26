Version 1.1 - 20-06-2024

Bienvenue sur le projet SMSManager ! Ce projet est conçu pour faciliter l'envoi de messages SMS en utilisant des données provenant de fichiers CSV. Ce programme a été conçu pour permettre d'envoyer à l'origine des rappels de rendez-vous par SMS, mais il peut désormais être utilisé pour d'autres usages. C'est désormais très simple, il suffit de définir des modèles de messages en y insérant des parametres provenant du fichier CSV.

Objectif
Le but principal de SMSManager est de fournir une solution simple et fiable pour envoyer des rappels de rendez-vous via SMS. Cette fonctionnalité est utile pour les entreprises et les professionnels qui cherchent à améliorer la communication avec leurs clients ou patients.

Fonctionnalités
 - Envoi automatique de SMS : Le système lit les données de rendez-vous depuis un fichier CSV et envoie des SMS de rappel aux contacts listés.
 - Intégration avec SMSMode : SMSManager utilise l'API de SMSMode, un fournisseur de services SMS, pour assurer un envoi fiable et sécurisé des messages. Une clé API sera nécessaire pour faire fonctionner le programme. LE programme a été développé avec une interface SMSManager pour permettre l'intégration de nouveaux fournisseurs de SMS en développant de nouvelles classes.

Comment ça marche ?

Le programme fonctionne avec un fichier de configuration au format JSON. Un exemple est fourni dans le dossier ./config

Pour utiliser le programme, il suffit de lancer la commande suivante : /path/to/php/php /path/to/file.csv /path/config.json 

Ce fiohier JSON contient l'ensemble des informations pour permettre le traitement d'un fichier CSV.
Il va définir, le nom du fichier source, le délimiteur CSV, le nom de lignes à ignorer (notamment pour ne pas traiter les entetes de fichiers), la limite du nombre de SMS par numéro de téléphone (si cette limite est atteinte, cela neutralisera l'envoi de SMS au numéro concerné afin d'éviter l'envoi massif de SMS vers un numéro de téléphone), le nombre de jours maximum par rapport à la date du jour (il n'enverra pas de SMS si la date du rendez vous est trop éloignée)

Le fichier de configuration JSON

La configuration des paramètres du programme sont fournis par un fichier Json . Cela permet d’avoir la possibilité de définir plusieurs paramétrages différents en fonction des cas d’usage.
 
Les différents paramètres sont les suivants : 

campaignID : Identifiant de Campagne. Ce paramètre sera restitué si le fichier de Rapport (summaryFile) est activé.
mappingField : le tableau mappingField contient le mappage des différentes zones du fichier CSV. La valeur donnée en parametre indique la position du champ dans le fichier csv (0 étant la 1ère position, 1 étant la deuxième….)
	Les champs attendus sont : 
-	dateAppointment : Date du rendez-vous au format jj/mm/aaaa
-	timeAppointment : Heure du rendez-vous
-	phoneNumber : Numéro de téléphone portable
-	structure : Nom de l’établissement
-	service : Nom du service
-	smsAgreement : Indique si l’utilisateur a donné son accord pour l’envoi de SMS. Si ce paramètre est spécifié et si filterAgreement est activé alors le programme tiendra compte de ce paramètre pour envoyer (ou pas) des SMS.
-	parameter1,parameter2,parameter3,parameter4,parameter5 : Ces champs déterminent des zones complémentaires qui pourront être utilisées dans le modèle de message (templateMessage)

filterAgreement : Le tableau filterAgreement indique si le fichier contient l’information d’accord pour l’envoi de SMS. Si c’est le cas, la zone smsAgreement sera évaluée et considérée comme valide si sa valeur correspond à la valeur agreementValue
	Les paramètre de ce tableau sont : 
-	active : Si cette valeur est égale à true, alors la zone smsAgreement sera évaluée pour déterminer si l’accord SMS a été donné.
-	agreementValue : Indique quelle valeur correspond à un accord SMS. Cette information est sensible à la casse (« Oui » est différent de « OUI »)
csvSeparator : Ce champ indique le caractère délimitateur du fichier .csv
ignoreFirstLines : Nombre de lignes du fichier à ignorer, si la valeur est à 1 alors la première ligne est ignorée.
maxIntervalleDate : Ce paramètre définit l’écart maximal entre la date du jour et la date du rendez-vous du SMS. Cela permet de s’assurer que seuls les rendez-vous à courte échéance seront concernés par l’envoi de SMS.
maxSMSByPhoneNumber : Ce paramètre indique la limite de SMS envoyé pour un numéro de téléphone. Si ce paramètre est atteint, aucun SMS ne sera envoyé car cela signifie que plusieurs rendez-vous sont prévus et que l’envoi de multiples SMS n’est pas approprié.
outputDirectory : Indique le dossier de destination où sera stocké le fichier de sortie reprenant les SMS envoyés et leur statut d’envoi. Ce fichier sera envoyé par mail si le paramètre sendReport est activé.
debugMode : Booléen indiquant si positionné à true que le programme fonctionnera en mode Debug et que les SMS ne seront pas réellement envoyés.
smsTrace : Booléen indiquant que l’on souhaite que le fournisseur de SMS affiche la trace des actions réalisées.
verboseMode : Booléen indiquant si positionné à true que l’on affiche à l’écran le détail des actions réalisées. 
smsMode : Ce tableau contient des paramètres concernant  le fournisseur de SMS « SMSMode »
-	apiToken : Indique la clé API nécessaire pour pouvoir utiliser la solution SMSMode. Il est possible d’appliquer à partir de SMSMode des filtres d’adresse IP pour éviter que le jeton soit utilisé en dehors du périmètre du GHT du Rouergue.
messageTemplate : Définition du modèle du message. Les messages à envoyer utiliseront ce paramètre. Pour utiliser un des champs définis dans mappingField, il suffit de le nommer dans le modèle de la façon suivante (#dateAppointment#, #timeAppointment#, #phoneNumber#, #parameter1#,etc….)
summaryFile : indique si le programme génère une synthèse globale de l’envoi de la campagne. Le but du fichier est d’être analysé à des fins statistiques. Si la valeur est à true, le fichier summary.csv est alimenté dans le dossier outputDirectory. Une ligne est ajouté à chaque exécution du programme. Le format du fichier est le suivant : 
 

mail : Ce tableau contient l’ensemble des paramètres concernant l’envoi de rapport de synthèse par email 
-	sendReport :  booléen indiquant si positionné à true, l’envoi de rapport de synthèse par mail
-	SMTPAuth : Indique si on utilise une authentification SMTP
-	port : Port du Serveur
-	server : Nom du Serveur de messagerie
-	username : Nom d’utilisateur utilisé pour l’authentification  SMTP (si nécessaire)
-	password : Mot de Passe utilisé pour l’authentification SMTP (si nécessaire)
-	to :  Adresse mail destinataire du rapport de synthèse.


Le programme a été conçu pour pouvoir indiquer le chemin du fichier json en argument. Si aucun argument n'est fourni, il va essayer de trouver un fichier de configuration JSON par défaut ./config/config.json

- Préparation des données : un fichier CSV est fourni, contenant les informations des rendez-vous auxquels nous souhaitons envoyer des SMS. Le programme va charger l'ensemble des rendez-vous et pour chaque rendez vous va controler les informations (date du rendez-vous, format du numéro de téléphone)

- Envoi des SMS : Le système lit le fichier CSV, extrait les informations nécessaires, et envoie les messages via l'API du fournisseur SMS
- Envoi d'un rapport de synthèse par mail  pour indiquer le résultat du traitement 

SMSManager est un outil essentiel pour ceux qui cherchent à optimiser la gestion des rappels de rendez-vous. En tirant parti de l'API de SMSMode, ce projet offre une solution efficace et facile à utiliser pour améliorer la communication avec vos clients ou patients.
