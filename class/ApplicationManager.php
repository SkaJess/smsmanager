<?php

class ApplicationManager
{

    public const MODE_DEBUG = 'DEBUG';
    public const MODE_PRODUCTION = 'PRODUCTION';
    private $mode; // Indique le mode de fonctionnement de l'application
    private $sourceFile; // Indique le fichier source à traiter
    private $successOutputFile; // Indique le fichier où seront stockés les envois qui auront été correectement envoyés.
    private $errorsOutputFile;  // Indique le fichier où seront stockés les envois qui sont en échec.

    public function __construct()
    {
        $this->mode = self::MODE_PRODUCTION;
    }
    public function setMode(string $mode): ApplicationManager
    {
        $this->mode = $mode;
        return $this;
    }
    public function display(string $message)
    {
        if ($this->mode === self::MODE_DEBUG) {
            echo $message . "\n";
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
    public function getSuccessOutputFile()
    {
        return $this->successOutputFile;
    }

    /**
     * Set the value of successOutputFile
     */
    public function setSuccessOutputFile($successOutputFile): self
    {
        $this->successOutputFile = $successOutputFile;

        return $this;
    }

    /**
     * Get the value of failsOutputFile
     */
    public function getErrorsOutputFile()
    {
        return $this->errorsOutputFile;
    }

    /**
     * Set the value of failsOutputFile
     */
    public function setErrorsOutputFile($errorsOutputFile): self
    {
        $this->errorsOutputFile = $errorsOutputFile;

        return $this;
    }
}