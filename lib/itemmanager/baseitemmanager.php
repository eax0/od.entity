<?
namespace Od\Entity\ItemManager;

use Od\Entity\DataManager\DataManager;
use Od\Entity\DBManager\IDBManager;
use Od\Entity\Finder\ItemFinder;

abstract class BaseItemManager
{
    protected static $dbManagers = [];
    protected static $finders = [];
    protected static $dataManagers = [];

    protected function __construct()
    {
    }

    protected static function getFinder()
    {
        $class = get_called_class();
        if (self::$finders[$class]) {
            return self::$finders[$class];
        }

        return self::$finders[$class] = static::createFinder();
    }

    protected static function getDatamanager()
    {
        $class = get_called_class();
        if (self::$dataManagers[$class]) {
            return self::$dataManagers[$class];
        }

        return self::$dataManagers[$class] = static::createDataManager();
    }

    protected static function getDBManager()
    {
        $class = get_called_class();
        if (self::$dbManagers[$class]) {
            return self::$dbManagers[$class];
        }

        return self::$dbManagers[$class] = static::_createDBManager();
    }

    public static function createFinder()
    {
        return new ItemFinder(static::getDBManager());
    }

    public static function createDataManager()
    {
        return new DataManager(static::getDBManager(), static::getFinder());
    }

    /** @return IDBManager */
    abstract protected static function _createDBManager();
}
