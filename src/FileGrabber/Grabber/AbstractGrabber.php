<?php

namespace FileGrabber\Grabber;

abstract class AbstractGrabber
{
    protected $grabberName;
    abstract public function grabFile($fileUrl);
    abstract protected function isAvailable();
    public function getName()
    {
        return $this->grabberName;
    }
}