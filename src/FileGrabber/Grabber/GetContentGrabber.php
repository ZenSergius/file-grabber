<?php

namespace FileGrabber\Grabber;

class GetContentGrabber extends AbstractGrabber
{
    public function grabFile($fileUrl, $savePath)
    {
        $file = file_get_contents($fileUrl);
        file_put_contents('images/'.'getcontent_'.$savePath, $file);
    }
}