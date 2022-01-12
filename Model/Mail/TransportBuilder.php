<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Model\Mail;

use function array_merge;
use function method_exists;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    // container for attachments
    protected $parts = [];

    public function addAttachment($attachment)
    {
        $this->message->createAttachment(
            $attachment->getContent(),
            $attachment->getMimeType(),
            $attachment->getDisposition(),
            $attachment->getEncoding(),
            $this->encodedFileName($attachment->getFilename())
        );
    }

    protected function encodedFileName($subject)
    {
        return sprintf('=?utf-8?B?%s?=', base64_encode($subject));
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setParts($parts){
        $this->parts = $parts;
        return $this;
    }

    public function createAttachment($attachment, $type, $disposition, $encoding, $name)
    {
        if (!method_exists($this->message, 'createAttachment')) {
            $this->parts[] = $this->prepareAttachmentPart($attachment, $name);
        } else {
            $this->message->createAttachment($attachment, $type, $disposition, $encoding, $name);
        }
        return $this;
    }

    public function prepareAttachmentPart($content, $filename)
    {
        $attachment              = new \Zend\Mime\Part($content);
        $attachment->type        = \Zend\Mime\Mime::TYPE_OCTETSTREAM;
        $attachment->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding    = \Zend\Mime\Mime::ENCODING_BASE64;
        $attachment->filename    = $filename;
        return $attachment;
    }

    protected function prepareMessage()
    {
        parent::prepareMessage();

        // add file attachments to the message
        if (!method_exists($this->message, 'createAttachment') && count($this->parts)) {
            $mimeMessage = new \Zend\Mime\Message();
            $body        = $this->getMessage()->getBody();
            if (method_exists($body, 'getParts') && method_exists($this->getMessage(), 'setBody')) {
                $bodyParts = $this->getMessage()->getBody()->getParts();
                $mimeMessage->setParts(array_merge($bodyParts, $this->parts));
                $this->getMessage()->setBody($mimeMessage);
            }
        }
        return $this;
    }
}
