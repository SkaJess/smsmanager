<?php

require_once(dirname(__FILE__) . "/RendezVous.php");
require_once(dirname(__FILE__) . "/SMSInterface.php");
class Campagne
{
    private $listeRendezVous = array();
    private $nbRendezVous = array();
    private SMSInterface $smsProvider;

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

}