<?php

namespace CultuurNet\Entry;

class EventPermissionCollection
{
    /**
     * @var EventPermission[]
     */
    private $eventPermissions;

    /**
     * @param EventPermission[] $eventPermissions
     * @throws \InvalidArgumentException
     */
    public function __construct(array $eventPermissions)
    {
        $this->eventPermissions = $eventPermissions;

        array_walk(
            $eventPermissions,
            function ($item) {
                if (!$item instanceof EventPermission) {
                    throw new \InvalidArgumentException(
                        'Expected members of $eventPermissions argument to be all instances of EventPermission'
                    );
                }
            }
        );
    }

    /**
     * @return string
     */
    public function toXml()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $rootElement = $dom->createElement('events');
        $dom->appendChild($rootElement);

        array_walk(
            $this->eventPermissions,
            function (EventPermission $eventPermission) use ($dom, $rootElement) {
                $eventElement = $dom->createElement('event');

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
        );

        return $dom->saveXML();
    }
}
