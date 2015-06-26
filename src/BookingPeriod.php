<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

/**
 * Booking period.
 */
class BookingPeriod
{
    protected $startDate;
    protected $endDate;

    /**
     * @param string $startDate
     * @param string $endDate
     */
    public function __construct($startDate, $endDate)
    {
        \CultureFeed_Cdb_Data_Calendar::ValidateDate($startDate);
        \CultureFeed_Cdb_Data_Calendar::ValidateDate($endDate);

        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }
}
