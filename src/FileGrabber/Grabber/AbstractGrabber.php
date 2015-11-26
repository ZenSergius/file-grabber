<?php

namespace FileGrabber\Grabber;

abstract class AbstractGrabber
{
    abstract public function grabFile($fileUrl, $savePath);
}