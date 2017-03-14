<?
namespace Od\Entity\Finder;

trait DefaultItemFinderTrait
{
    public static function find($filter = [], $fields = [], $orderBy = [])
    {
        return static::getFinder()->item($filter, $fields, $orderBy);
    }

    public static function findItems($filter = [], $fields = [], $orderBy = [], $extendedParams = [])
    {
        return static::getFinder()->items($filter, $fields, $orderBy, $extendedParams);
    }
    
    public static function findItemsMap($filter = [], $fields = [], $orderBy = [], $extendedParams = [])
    {
        return static::getFinder()->map($filter, $fields, $orderBy, $extendedParams);
    }

    public static function findLimited($limit, $filter = [], $fields = [], $orderBy = [], $offset = null)
    {
        return static::getFinder()->limited($limit, $filter, $fields, $orderBy, $offset);
    }

    public static function groupItems($groupBy, $filter = [], $orderBy = [], $limit = null)
    {
        return static::getFinder()->grouped($groupBy, $filter, $orderBy, $limit);
    }

    public static function exists($filter = [])
    {
        return static::getFinder()->exists($filter);
    }

    public static function count($filter = [])
    {
        return static::getFinder()->count($filter);
    }

    public static function findId($filter = [], $orderBy = [])
    {
        return static::getFinder()->id($filter, $orderBy);
    }

    public static function findIds($filter = [], $orderBy = [], $limit = null)
    {
        return static::getFinder()->ids($filter, $orderBy, $limit);
    }

    public static function select($filter = [], $fields = [], $orderBy = [], $extendedParams = [])
    {
        return static::getFinder()->select($filter, $fields, $orderBy, $extendedParams);
    }

    /** @return IItemFinder */
    abstract protected static function getFinder();
}