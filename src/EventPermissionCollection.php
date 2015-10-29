<?php

namespace CultuurNet\Entry;

class EventPermissionCollection
{
    /**
     * @var array
     */
    private $eventPermissions;

    /**
     * @return EventPermission[]
     */
    public function getEventPermissions()
    {
        return $this->eventPermissions;
    }

    /**
     * @param $eventPermissions
     */
    public function __construct($eventPermissions)
    {
        $this->eventPermissions = $eventPermissions;
    }

    /**
     * @return string
     */
    public function toXml()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $rootElement = $dom->createElement('events');
        $dom->appendChild($rootElement);

        foreach ($this->getEventPermissions() as $eventPermission) {
            $eventElement =$dom->createElement('event');

            $cdbidNode = $dom->createTextNode($eventPermission->getCdbid());
            $cdbidElement = $dom->createElement('cdbid');
            $cdbidElement->appendChild($cdbidNode);
            $eventElement->appendChild($cdbidElement);

            $isEditableNode = $dom->createTextNode(
                $eventPermission->isEditable() ? 'true' : 'false'
            );
            $isEditableElement = $dom->createElement('editable');
            $isEditableElement->appendChild($isEditableNode);
            $eventElement->appendChild($isEditableElement);

            $rootElement->appendChild($eventElement);
        }

        return $dom->saveXML();
    }
}
