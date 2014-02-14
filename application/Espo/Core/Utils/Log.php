<?php

namespace Espo\Core\Utils;

class Log
{

    /**
	* @var string $defaultLevel - default level, uses only if level is not defined by user
	*/
    public $defaultLevel = 'INFO';

    /**
	* @var array $errorLevels
	* @link http://www.php.net/manual/en/errorfunc.constants.php
	*/
	protected $errorLevels = array (
				'FATAL EXCEPTION' => -1,
				'EXCEPTION' => 0,
				'FATAL' => 1,
				'WARNING' => 2,
				'NOTICE' => 8,
				'ERROR' => 32767,
				'INFO' => 50000,
				'DEBUG' => 55000,
	);

	/**
	* @var array $phpErrorTypes
	*/
	protected $phpErrorTypes = array (
				-1 => 'FATAL EXCEPTION',
				0 => 'EXCEPTION',
				E_ERROR              => 'FATAL',
				E_WARNING            => 'WARNING',
				E_PARSE              => 'PARSE',
				E_NOTICE             => 'NOTICE',
				E_CORE_ERROR         => 'CORE_ERROR',
				E_CORE_WARNING       => 'CORE_WARNING',
				E_COMPILE_ERROR      => 'COMPILE_ERROR',
				E_COMPILE_WARNING    => 'COMPILE_WARNING',
				E_STRICT             => 'STRICT',
				E_RECOVERABLE_ERROR  => 'RECOVERABLE',
				E_DEPRECATED         => 'DEPRECATED',
				E_USER_ERROR         => 'USER_ERROR',
				E_USER_WARNING       => 'USER_WARNING',
				E_USER_NOTICE        => 'USER_NOTICE',
				E_USER_DEPRECATED    => 'USER_DEPRECATED',
	);

    /*
    * @var array $dieErrors - errors when should stop program execution
	*/
	protected $dieErrors = array (
				'FATAL EXCEPTION',
				'FATAL',
	);


	private $fileManager;
	private $output;
	private $params;


	public function __construct(\Espo\Core\Utils\File\Manager $fileManager, \Espo\Core\Utils\Api\Output $output, array $params)
	{
		$this->fileManager = $fileManager;
		$this->output = $output;
		$this->params = $params;
	}



	protected function getFileManager()
	{
		return $this->fileManager;
	}

	protected function getOutput()
	{
		return $this->output;
	}

	protected function getParams()
	{
		return $this->params;
	}


	/**
    * Catch error and save it to the log file
	*
	* @param integer $errNo - the level of the error
	* @param string $errStr - the error message
	* @param string $errFile - the filename that the error was raised in
	* @param integer $errLine - the line number the error was raised at
	* @return bool
	*/
	function catchError($errNo, $errStr, $errFile, $errLine)
    {
        $errorType = $this->phpErrorTypes[$errNo];
		if (empty($errorType)) {
        	$errorType = $errNo;
		}
        $errorMessage = $errStr . " - " . $errFile . ":" . $errLine;

        return $this->add($errorType, $errorMessage);
    }

	/**
    * Catch exeption and save it to the log file
	*
	* @param integer $Exception
	* @return bool
	*/
	public function catchException($Exception)
    {
		$errNo = $Exception->getCode();
		$errorMessage = get_class($Exception).' - '.$Exception->getMessage();

 		//try to resolve the problem automatically
 		/*if ($useResolver) {
        	$this->getResolver()->handle($Exception);
 		} */

        $errorType= $this->phpErrorTypes[$errNo];
		if (empty($errorType)) {
        	$errorType= $errNo;
		}

        return $this->add($errorType, $errorMessage);
    }


	/**
    * Saved error to the file
	*
	* @param string $text
	* @return bool
	*/
	protected function logError($text)
	{
        $text = date('Y-m-d H:i:s') . ' ' . $text;

        $params = $this->getParams();
		return $this->getFileManager()->appendContent($text, $params['options']['dir'], $params['options']['file']);
	}

	/**
    * Add custom item to the log file
	*
	* @param string $errorType
	* @param string $text
	* @return bool
	*/
	public function add($errorType, $text='')
	{
		if (empty($text)) {
        	$text = $errorType;
			$errorType = '';
		}
		if (!empty($errorType)){
			$errorType = mb_strtoupper($errorType);
		}

        $text = "[".$errorType."]: ".$text."\n";

		//CHECK Levels here
		$status = true;
		if ($this->isSave($errorType)) {
			$status = $this->logError($text);
		}

		if (in_array($errorType, $this->dieErrors)) {
			$this->getOutput()->displayError($text, 500);
		}

		return $status;
	}

	/**
    * Check if save the error to log file according to error level
	*
	* @param string $errorType
	* @return bool
	*/
	protected function isSave($errorType)
	{
		$params = $this->getParams();
		$configLevel= $this->getLevelValue($params['options']['level']);
		$errorLevel= $this->getLevelValue($errorType);

		if ($configLevel >= $errorLevel) {
			return true;
		}
		return false;
	}


	/**
    * Get Level value (int) from the name
	*
	* @param string $errorName
	* @return int
	*/
	function getLevelValue($name)
	{
		if (empty($name)) {
			return $this->errorLevels[$this->defaultLevel];
		}

		if (is_int($name)) {
			return $name;
		}

		$name= mb_strtoupper($name);
		$levelValue= $this->errorLevels[$this->defaultLevel];

		//check into errorLevels
        if (array_key_exists($name, $this->errorLevels)) {
        	return $this->errorLevels[$name];
        }

		//check into phpErrorTypes
		if (in_array($name, $this->phpErrorTypes)) {
        	foreach($this->phpErrorTypes as $key => $val) {
				if ($name==$val) {
                	return $key;
				}
			}
        }

		return $this->errorLevels[$this->defaultLevel];
	}

    /**
    * Get Level name from the int value
	*
	* @param int $intValue
	* @return string
	*/
	function getLevelName($intValue)
	{

	}

}
