<?php
class RendezVous {
    private $phoneNumber;
    private $doctorName;
    private $service;
    private $dateAppointment;
    private $timeAppointment;

    public function __construct($phoneNumber=null, $doctorName=null, $service=null, $dateAppointment=null, $timeAppointment=null) {
        $this->phoneNumber = $phoneNumber;
        $this->doctorName = $doctorName;
        $this->service = $service;
        $this->dateAppointment = $dateAppointment;
        $this->timeAppointment = $timeAppointment;
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
}