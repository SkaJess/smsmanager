<?php

class ApplicationManager
{

    public const MODE_DEBUG = 'DEBUG';
    public const MODE_PRODUCTION = 'PRODUCTION';
    public const VERBOSE_ON = 'VERBOSE_ON';
    public const VERBOSE_OFF = 'VERBOSE_OFF';
    private $mode; // Indique le mode de fonctionnement de l'application
    private $sourceFile; // Indique le fichier source à traiter
    private $outputFile; // Indique le fichier de sortie ou seront enregistrés les données avec le statut des envois.
    private $verbose; // Si true, alors le programme affiche des informations sur le traitement.

    public function __construct()
    {
        $this->mode = self::MODE_PRODUCTION;
    }
    public function setMode(string $mode): ApplicationManager
    {
        $this->mode = $mode;
        return $this;
    }
    public function getMode()
    {
        return $this->mode;
    }
    public function display(string $message)
    {
        if ($this->verbose == true) {
            if ($this->mode === self::MODE_DEBUG) {
                echo "[DEBUG] " . $message . "\n";
            } else {
                echo $message . "\n";

            }
        }
    }

    /**
     * Get the value of sourceFile
     */
    public function getSourceFile()
    {
        return $this->sourceFile;
    }

    /**
     * Set the value of sourceFile
     */
    public function setSourceFile($sourceFile): self
    {
        $this->sourceFile = $sourceFile;

        return $this;
    }

    /**
     * Get the value of successOutputFile
     */
    public function getOutputFile()
    {
        return $this->outputFile;
    }

    /**
     * Set the value of successOutputFile
     */
    public function setOutputFile($outputFile): self
    {
        $this->outputFile = $outputFile;

        return $this;
    }

    /**
     * Get the value of verbose
     */
    public function isVerbose()
    {
        return $this->verbose;
    }

    /**
     * Set the value of verbose
     */
    public function setVerbose($verbose): self
    {
        if ($verbose == self::VERBOSE_ON) {
            $this->verbose = true;
        } else {
            $this->verbose = false;
        }

        return $this;
    }
}