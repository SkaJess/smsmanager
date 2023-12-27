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

    public function send($manager)
    {
        // Traitement du fichier 
        foreach ($this->getListeRendezVous() as $rdv) {
            $manager->display("");
            $manager->display(" > Numéro de téléphone  : " . $rdv->getPhoneNumber() . " -> Numéro de téléphone formaté : " . $rdv->getFormatedPhoneNumber());
            $manager->display(" > Nb de Rdv Programmés : " . $this->getNbRdvByPhoneNumber($rdv->getFormatedPhoneNumber()));
            if ($rdv->envoyerSMSRappel() == true) {
                $manager->display(" > Envoi de SMS : OK");
                $this->nbEnvois++;
            } else {
                $manager->display(" > Envoi de SMS : Echec");
                $listeAnomalies[] = $rdv;
                $this->nbErreurs++;
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
}