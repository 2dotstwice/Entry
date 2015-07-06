<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

use UnexpectedValueException;

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
        $this->validateDate($startDate);
        $this->validateDate($endDate);

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

    /**
     * Validate a date for bookingperiod.
     */
    private function validateDate($date)
    {
        if (!preg_match('/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/', $date)) {
            throw new UnexpectedValueException('Invalid date: ' . $date);
        }
    }
}
