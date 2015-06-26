<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

/**
 * Entity type
 */
class EntityType
{
    protected $type;

    private $allowed_types = array(
      'event',
      'production'
    );

    /**
     * @param $type
     */
    public function __construct($type)
    {
        if (!in_array($type, $this->allowed_types)) {
            throw new \InvalidArgumentException(
                'Invalid entity type ' . $type
            );
        }
        $this->type = $type;
    }

    public function __toString()
    {
        return $this->type;
    }

    public function getType()
    {
        return $this->type;
    }
}
