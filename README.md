Version 1.1 - 20-06-2024

Bienvenue sur le projet SMSManager ! Ce projet est conçu pour faciliter l'envoi de messages SMS en utilisant des données provenant de fichiers CSV. Ce programme a été conçu pour permettre d'envoyer des rappels de rendez-vous par SMS, mais il pourrait facilement être modifié pour envoyer d'autres types de messages.

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

Le programme a été conçu pour pouvoir indiquer le chemin du fichier json en argument. Si aucun argument n'est fourni, il va essayer de trouver un fichier de configuration JSON par défaut ./config/config.json

- Préparation des données : un fichier CSV est fourni, contenant les informations des rendez-vous auxquels nous souhaitons envoyer des SMS. Le programme va charger l'ensemble des rendez-vous et pour chaque rendez vous va controler les informations (date du rendez-vous, format du numéro de téléphone)

- Envoi des SMS : Le système lit le fichier CSV, extrait les informations nécessaires, et envoie les messages via l'API du fournisseur SMS
- Envoi d'un rapport de synthèse par mail  pour indiquer le résultat du traitement 

SMSManager est un outil essentiel pour ceux qui cherchent à optimiser la gestion des rappels de rendez-vous. En tirant parti de l'API de SMSMode, ce projet offre une solution efficace et facile à utiliser pour améliorer la communication avec vos clients ou patients.
