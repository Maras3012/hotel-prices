<?php

/**
 * Helper Class
 *
 * Utility methods for various tasks.
 */
class Helper {
    /**
     * Check if a string contains a valid date
     *
     * @param string $string The string to be checked as a date.
     *
     * @return bool True if the string is a valid date, false otherwise.
     */
    public static function isDate($string) {
        return strtotime($string) !== false;
    }

    /**
     * Extract hotel name and number of days from the string
     *
     * @param string $string The input string to extract data from.
     *
     * @return array|false An associative array with 'code' and 'days' keys if
     *                     the extraction is successful, or false otherwise.
     */
    public static function extractHotelAndDays($string) {
        if (preg_match('/([A-Z]+)\s+(\d+)/', $string, $matches)) {
            $hotelCode = $matches[1];
            $days = intval($matches[2]);
            
            return ['code' => $hotelCode, 'days' => $days];
        } else {
            return false;
        }
    }
}
