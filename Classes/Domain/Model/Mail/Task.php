<?php

namespace Quicko\Clubmanager\Domain\Model\Mail;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Task extends AbstractEntity
{

    const SEND_STATE_WILL_SEND = 0;
    const SEND_STATE_DONE = 1;
    const SEND_STATE_STOPPED = 2;

    const PRIORITY_LEVEL_MIN = 0;
    const PRIORITY_LEVEL_MEDIUM = 5;
    const PRIORITY_LEVEL_HIGHT = 10;
    
    /**
     * sendState
     *
     * @var \integer
     */
    protected $sendState;

    /**
     * priorityLevel
     *
     * @var \integer
     */
    protected $priorityLevel;

    /**
     * generatorClass
     *
     * @var \string
     */
    protected $generatorClass;


    /**
     * generatorArguments
     *
     * @var \string
     */
    protected $generatorArguments;


    /**
     * generatorTime
     *
     * @var \DateTime
     */
    protected $processedTime;

    /**
     * errorTime
     *
     * @var \DateTime
     */
    protected $errorTime;

    /**
     * errorMessage
     *
     * @var \string
     */
    protected $errorMessage;

    /**
     * openTries
     *
     * @var \integer
     */
    protected $openTries;


    /**
     * Returns the openTries.
     *
     * @return \integer $openTries
     */
    public function getOpenTries()
    {
        return $this->openTries;
    }

    /**
     * Sets the openTries.
     *
     * @param \integer $openTries
     */
    public function setOpenTries($openTries)
    {
        $this->openTries = $openTries;
    }


    /**
     * Returns the errorMessage.
     *
     * @return \string $errorTime
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Sets the errorMessage.
     *
     * @param \string $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * Returns the errorTime.
     *
     * @return \DateTime $errorTime
     */
    public function getErrorTime()
    {
        return $this->processedTime;
    }

    /**
     * Sets the errorTime.
     *
     * @param \DateTime $errorTime
     */
    public function setErrorTime($errorTime)
    {
        $this->errorTime = $errorTime;
    }

    /**
     * Returns the processedTime.
     *
     * @return \DateTime $processedTime
     */
    public function getProcessedTime()
    {
        return $this->processedTime;
    }

    /**
     * Sets the processedTime.
     *
     * @param \DateTime $processedTime
     */
    public function setProcessedTime($processedTime)
    {
        $this->processedTime = $processedTime;
    }

    /**
     * Returns the generatorArguments.
     *
     * @return \string $generatorArguments
     */
    public function getGeneratorArguments()
    {
        return $this->generatorArguments;
    }

    /**
     * Sets the generatorArguments.
     *
     * @param \string $generatorArguments
     */
    public function setGeneratorArguments($generatorArguments)
    {
        $this->generatorArguments = $generatorArguments;
    }

    /**
     * Returns the generatorClass.
     *
     * @return \string $generatorClass
     */
    public function getGeneratorClass()
    {
        return $this->generatorClass;
    }

    /**
     * Sets the generatorClass.
     *
     * @param \string $generatorClass
     */
    public function setGeneratorClass($generatorClass)
    {
        $this->generatorClass = $generatorClass;
    }

    /**
     * Returns the sendState.
     *
     * @return \integer $sendState
     */
    public function getSendState()
    {
        return $this->sendState;
    }

    /**
     * Sets the sendState.
     *
     * @param \integer $sendState
     */
    public function setSendState($sendState)
    {
        $this->sendState = $sendState;
    }

    /**
     * Returns the priorityLevel.
     *
     * @return \integer $priorityLevel
     */
    public function getPriorityLevel()
    {
        return $this->priorityLevel;
    }

    /**
     * Sets the priorityLevel.
     *
     * @param \integer $priorityLevel
     */
    public function setPriorityLevel($priorityLevel)
    {
        $this->priorityLevel = $priorityLevel;
    }
}
