<?php

namespace FileGrabber\Grabber;

//require_once __DIR__ . '/AbstractGrabber.php';

class GetContentGrabber extends AbstractGrabber
{
    public function grabFile($fileUrl, $savePath)
    {
        $file = file_get_contents($fileUrl);
        file_put_contents('getcontent_'.$savePath, $file);
    }
}