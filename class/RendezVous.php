<?php
require_once(dirname(__FILE__) . "/SMSInterface.php");
class RendezVous
{

    private $phoneNumber;
    private $formatedPhoneNumber;
    private $doctorName;
    private $service;
    private $dateAppointment;
    private $timeAppointment;
    private $message;
    private $structure;

    private $smsId;
    private $smsStatusCode;
    private $smsstatusDescription;
    private SMSInterface $smsProvider; // Fournisseur de SMS. Sera utilisé si on souhaite envoyer le SMS à partir de cette classe.

    public function __construct($phoneNumber = null, $doctorName = null, $service = null, $dateAppointment = null, $timeAppointment = null)
    {
        $this->setPhoneNumber($phoneNumber);
        $this->doctorName = $doctorName;
        $this->service = $service;
        $this->dateAppointment = $dateAppointment;
        $this->timeAppointment = $timeAppointment;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getStructure()
    {
        return $this->structure;
    }

    public function setStructure($structure)
    {
        $this->structure = $structure;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
        $this->setFormatedPhoneNumber($phoneNumber);
    }

    public function getDoctorName()
    {
        return $this->doctorName;
    }

    public function setDoctorName($doctorName)
    {
        $this->doctorName = $doctorName;
    }

    public function getService()
    {
        return $this->service;
    }

    public function setService($service)
    {
        $this->service = $service;
    }

    public function getDateAppointment()
    {
        return $this->dateAppointment;
    }

    public function setDateAppointment($dateAppointment)
    {
        try {
            $this->dateAppointment = DateTime::createFromFormat("Y-m-d", $dateAppointment);
        } catch (Exception $e) {
            $this->dateAppointment = false;
        }
    }

    public function getTimeAppointment()
    {
        return $this->timeAppointment;
    }

    public function setTimeAppointment($timeAppointment)
    {
        $this->timeAppointment = $timeAppointment;
    }
    /**
     * La fonction va vérifier si la date du rendez vous est positionnée dans le bon intervale, 
     * c'est à dire, si la date est comprise entre la date du jour et l'intervalle défini en paramétre exprimé en nombre de jour.
     * Si pas d'intervalle n'est pas défini, on vérifie seulement que le rendez vous est à venir.
     * @return array
     */
    public function isDateOk(DateTime $now, int $maxIntervalDate): array
    {
        $status = array();
        if ($this->getDateAppointment()) {
            $interval = $now->diff($this->getDateAppointment());
            $nbDays = intval($interval->format('%r%a days'));
            if (($maxIntervalDate > 0) && ($nbDays > $maxIntervalDate)) {
                $status['statusCode'] = false;
                $status['description'] = "Date trop éloignée";

            } elseif ($nbDays < 0) {
                $status['statusCode'] = false;
                $status['description'] = "Date révolue";
            } else {
                $status['statusCode'] = true;
                $status['description'] = "Date OK";
            }
        } else {
            $status['statusCode'] = false;
            $status['description'] = "Format de date non reconnu";
        }
        return $status;
    }

    public function preparationMessage()
    {
        $this->message = "Bonjour, nous vous rappelons votre RDV avec " . $this->getDoctorName() . " le " . $this->getDateAppointment()->format("d/m/Y") . " à " . $this->getTimeAppointment() . " au " . $this->getStructure();
        return $this->message;
    }
    public function envoyerSMSRappel()
    {
        if ($this->isMobilePhone()) {
            $this->preparationMessage();
            return true;
        } else {
            return false;
        }
    }

    public function isMobilePhone()
    {
        $numeroSansSeparateurs = preg_replace('/[-.\/]/', '', $this->getPhoneNumber());

        // Expression régulière pour un numéro de téléphone portable français
        $pattern = "/^(0|\\+33|0033|33)[67]([0-9]{8})$/";

        // Vérifie si le numéro correspond au pattern
        if (preg_match($pattern, $numeroSansSeparateurs)) {
            return true; // Le numéro est un portable
        } else {
            return false; // Le numéro n'est pas un portable
        }
    }

    /**
     * Get the value of formatedPhoneNumber
     */
    public function getFormatedPhoneNumber()
    {
        return $this->formatedPhoneNumber;
    }

    /**
     * Set the value of formatedPhoneNumber
     */
    public function setFormatedPhoneNumber($formatedPhoneNumber): self
    {
        $this->formatedPhoneNumber = preg_replace('/[^\d]/i', '', $formatedPhoneNumber);
        return $this;
    }
    public function setSMSProvider($smsProvider)
    {
        if ($smsProvider instanceof SMSInterface) {
            $this->smsProvider = $smsProvider;
        } else {
            throw new \InvalidArgumentException('Le paramêtre doit implémenter la classe SMSInterface');
        }
    }

    /**
     * Get the value of smsstatusDescription
     */
    public function getSmsstatusDescription()
    {
        return $this->smsstatusDescription;
    }

    /**
     * Set the value of smsstatusDescription
     */
    public function setSmsstatusDescription($smsstatusDescription): self
    {
        $this->smsstatusDescription = $smsstatusDescription;

        return $this;
    }

    /**
     * Get the value of smsStatusCode
     */
    public function getSmsStatusCode()
    {
        return $this->smsStatusCode;
    }

    /**
     * Set the value of smsStatusCode
     */
    public function setSmsStatusCode($smsStatusCode): self
    {
        $this->smsStatusCode = $smsStatusCode;

        return $this;
    }

    /**
     * Get the value of smsId
     */
    public function getSmsId()
    {
        return $this->smsId;
    }

    /**
     * Set the value of smsId
     */
    public function setSmsId($smsId): self
    {
        $this->smsId = $smsId;

        return $this;
    }
}