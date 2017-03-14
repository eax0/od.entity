<?
namespace Od\Entity\Utils;

class DateUtils
{
    private static $siteDateFormat;

    public static function getSiteFormat($short)
    {
        if (!self::$siteDateFormat) {
            global $DB;
            self::$siteDateFormat = $DB->dateFormatToPHP(\CSite::getDateFormat($short ? 'SHORT' : 'FULL'));
        }

        return self::$siteDateFormat;
    }

    /**
     * @param $date - timestamp / string time for 'strtotime'
     * @return bool|string
     */
    public static function toFilterDate($date)
    {
        $timestamp = is_numeric($date) ? $date : strtotime($date);

        return convertTimeStamp($timestamp, "FULL", "ru");
    }
}
