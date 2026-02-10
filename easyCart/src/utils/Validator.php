<?php
namespace App\Utils;

class Validator
{
    /**
     * Validate email format.
     */
    public static function email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate Indian phone number format (+91 followed by 10 digits).
     */
    public static function phone($phone)
    {
        return preg_match("/^(\+91)[6-9][0-9]{9}$/", $phone);
    }

    /**
     * Validate name (alphabets and spaces only, min length 3).
     */
    public static function name($name)
    {
        return preg_match("/^[a-zA-Z\s]{3,50}$/", $name);
    }

    /**
     * Validate pincode (6-digit format).
     */
    public static function pincode($pincode)
    {
        return preg_match("/^[1-9][0-9]{5}$/", $pincode);
    }

    /**
     * Check if a value is provided.
     */
    public static function required($value)
    {
        return !empty(trim($value));
    }

    /**
     * Validate address (min length 10).
     */
    public static function address($address)
    {
        return strlen(trim($address)) >= 10;
    }
}
