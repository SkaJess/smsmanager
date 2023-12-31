<?php

require_once(dirname(__FILE__) . "/RendezVous.php");
require_once(dirname(__FILE__) . "/SMSInterface.php");
class Campaign
{
    private $listeRendezVous = array();
    private $nbRendezVous = array();
    private SMSInterface $smsProvider;
    private int $nbEnvois = 0;
    private int $nbErreurs = 0;
    private int $maxIntervalDate = 0;
    private DateTime $now;

    public function __construct()
    {
        $this->now = new DateTime();
    }

    public function addRendezVous(RendezVous $rdv)
    {
        $this->listeRendezVous[] = $rdv;
        if (isset($this->nbRendezVous[$rdv->getFormatedPhoneNumber()])) {
            $this->nbRendezVous[$rdv->getFormatedPhoneNumber()]++;
        } else {
            $this->nbRendezVous[$rdv->getFormatedPhoneNumber()] = 1;
        }
    }

    public function getListeRendezVous()
    {
        return $this->listeRendezVous;
    }

    public function NumberOfRendezVous()
    {
        return count($this->listeRendezVous);
    }

    public function getNbRdvByPhoneNumber($number)
    {
        if (isset($this->nbRendezVous[$number]))
            return ($this->nbRendezVous[$number]);
        else
            return 0;
    }
    public function setSMSProvider($smsProvider)
    {
        if ($smsProvider instanceof SMSInterface) {
            $this->smsProvider = $smsProvider;
        } else {
            throw new \InvalidArgumentException('Le paramêtre doit implémenter la classe SMSInterface');
        }
    }

    public function send(ApplicationManager $manager)
    {
        // Traitement de la liste des rendez vous  
        foreach ($this->getListeRendezVous() as $rdv) {
            $manager->display("");
            $manager->display(" > Numéro de téléphone  : " . $rdv->getPhoneNumber() . " -> Numéro de téléphone formaté : " . $rdv->getFormatedPhoneNumber());
            $manager->display(" > Nb de Rdv Programmés : " . $this->getNbRdvByPhoneNumber($rdv->getFormatedPhoneNumber()));
            $checkDate = $rdv->isDateOk($this->getNow(), $this->getMaxIntervalDate());
            if ($checkDate['statusCode'] == true) {
                if ($rdv->isMobilePhone()) {
                    $request = $this->smsProvider->prepareSMS($rdv->preparationMessage(), $rdv->getFormatedPhoneNumber());
                    if ($manager->getMode() == ApplicationManager::MODE_PRODUCTION) {
                        $response = $this->smsProvider->sendSMS();
                        $manager->display(" > Envoi du SMS : PRODUCTION");
                        $rdv->setSmsStatusCode($response->getCode());
                        $rdv->setSmsstatusDescription($response->getDescription());
                        $rdv->setSmsId($response->getSMSId());
                        $manager->display(" > Code Réponse : " . $response->getCode());
                        $manager->display(" > Description Réponse : " . $response->getDescription());
                        $manager->display(" > ID SMS : " . $response->getSMSId());
                        if ($rdv->getSmsStatusCode() == 0) {
                            $manager->display(" > SMS Correctement envoyé ");
                            $this->nbEnvois++;
                        } else {
                            $manager->display(" > Erreur lors de l'envoi du SMS ");
                            $this->nbErreurs++;
                            $listeAnomalies[] = $rdv;
                        }
                    } else {
                        $manager->display(" > Simulation d'envoi de SMS");
                        $rdv->setSmsStatusCode(-1);
                        $rdv->setSmsstatusDescription('non envoyé:mode debug');
                        $rdv->setSmsId(0);
                        $this->nbErreurs++;
                    }
                } else {
                    $manager->display(" > Numéro de Téléphone incorrect ");
                    $rdv->setSmsStatusCode(-2);
                    $rdv->setSmsstatusDescription('Numéro de téléphone incorrect');
                    $this->nbErreurs++;
                    $listeAnomalies[] = $rdv;
                    $rdv->setSmsId(0);
                }
            } else {
                $manager->display(" > Date non valide : " . $checkDate['description']);
                $rdv->setSmsStatusCode(-3);
                $rdv->setSmsstatusDescription($checkDate['description']);
                $this->nbErreurs++;
                $listeAnomalies[] = $rdv;
                $rdv->setSmsId(0);
            }

        }

    }

    /**
     * Get the value of nbEnvois
     *
     * @return int
     */
    public function getNbEnvois(): int
    {
        return $this->nbEnvois;
    }

    /**
     * Get the value of nbErreurs
     *
     * @return int
     */
    public function getNbErreurs(): int
    {
        return $this->nbErreurs;
    }

    /**
     * Get the value of maxIntervalDate
     *
     * @return int
     */
    public function getMaxIntervalDate(): int
    {
        return $this->maxIntervalDate;
    }

    /**
     * Set the value of maxIntervalDate
     * Défini le nombre de jours maximum d'écart avec la dare du jour à laquelle le SMS va etre envoyé.
     * Par exemple, si défini à 7, tous les rendez vous à plus de 7 jours ne seront pas envoyés.
     * Si défini à 0, pas de controle sur l'intervalle de date.
     * @param int $maxIntervalDate
     *
     * @return self
     */
    public function setMaxIntervalDate(int $maxIntervalDate): self
    {
        $this->maxIntervalDate = $maxIntervalDate;

        return $this;
    }

    /**
     * Get the value of now
     *
     * @return DateTime
     */
    public function getNow(): DateTime
    {
        return $this->now;
    }
}