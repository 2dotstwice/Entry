<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

class Rsp
{
    const LEVEL_INFO = 'INFO';
    const LEVEL_ERROR = 'ERROR';

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $level;

    /**
     * @var string
     */
    protected $message;

    /**
     * @param string $version
     * @param string $level
     * @param string $code
     * @param string $link
     * @param string $message
     */
    public function __construct($version, $level, $code, $link, $message)
    {
        $this->code = $code;
        $this->link = $link;
        $this->version = $version;
        $this->level = $level;
        $this->message = $message;
    }

    /**
     * @param string $xml
     * @return static
     */
    public static function fromResponseBody($xml)
    {
        $simpleXml = new \SimpleXMLElement($xml);

        return new static(
            (string)$simpleXml['version'],
            (string)$simpleXml['level'],
            trim((string)$simpleXml->code),
            trim((string)$simpleXml->link),
            trim((string)$simpleXml->message)
        );
    }

    /**
     * @param string $code
     * @param string $message
     * @param string $version
     * @return Rsp
     */
    public static function error($code, $message, $version = '0.1')
    {
        return new self($version, self::LEVEL_ERROR, $code, null, $message);
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->level == self::LEVEL_ERROR;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function toXml()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $rootElement = $dom->createElement('rsp');
        $rootElement->setAttribute('level', $this->getLevel());
        $rootElement->setAttribute('version', $this->getVersion());

        $codeNode = $dom->createTextNode($this->getCode());
        $codeElement = $dom->createElement('code');
        $codeElement->appendChild($codeNode);

        if ($this->getLink()) {
            $linkNode = $dom->createTextNode($this->getLink());
            $linkElement = $dom->createElement('link');
            $linkElement->appendChild($linkNode);
        }

        if ($this->getMessage()) {
            $messageNode = $dom->createTextNode($this->getMessage());
            $messageElement = $dom->createElement('message');
            $messageElement->appendChild($messageNode);
        }

        return $dom->saveXML();
    }
}
