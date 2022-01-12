<?php

namespace VladimirPopov\WebForms\Model\Api;

interface AttachmentInterface
{
    public function getMimeType();

    public function getFilename();

    public function getDisposition();

    public function getEncoding();

    public function getContent();
}