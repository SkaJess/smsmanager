<?php
class RendezVous {
    private $phoneNumber;
    private $doctorName;
    private $service;
    private $dateAppointment;
    private $timeAppointment;
    private $message;
    private $structure;

    public function __construct($phoneNumber=null, $doctorName=null, $service=null, $dateAppointment=null, $timeAppointment=null) {
        $this->phoneNumber = $phoneNumber;
        $this->doctorName = $doctorName;
        $this->service = $service;
        $this->dateAppointment = $dateAppointment;
        $this->timeAppointment = $timeAppointment;
    }
    
    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

  public function getStructure() {
        return $this->structure;
    }

    public function setStructure($structure) {
        $this->structure = $structure;
    }

    public function getPhoneNumber() {
        return $this->phoneNumber;
    }

    public function setPhoneNumber($phoneNumber) {
        $this->phoneNumber = $phoneNumber;
    }

    public function getDoctorName() {
        return $this->doctorName;
    }

    public function setDoctorName($doctorName) {
        $this->doctorName = $doctorName;
    }

    public function getService() {
        return $this->service;
    }

    public function setService($service) {
        $this->service = $service;
    }

    public function getDateAppointment() {
        return $this->dateAppointment;
    }

    public function setDateAppointment($dateAppointment) {
        $this->dateAppointment = $dateAppointment;
    }

    public function getTimeAppointment() {
        return $this->timeAppointment;
    }

    public function setTimeAppointment($timeAppointment) {
        $this->timeAppointment = $timeAppointment;
    }

    public function preparationMessage() {
        $this->message = "Bonjour, nous vous rappelons votre RDV avec ".$this->getDoctorName()." le ".$this->getDateAppointment()." à ".$this->getTimeAppointment()." au ".$this->getStructure();
    }
    public function envoyerSMSRappel() {
        $this->preparationMessage();
        echo $this->getMessage()."\n";
    }
}