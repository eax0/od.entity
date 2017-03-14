<?
namespace Od\Entity\ItemManager;

use Bitrix\Main\Loader;
use Od\Entity\DataManager\DefaultDataManagerTrait;
use Od\Entity\DataManager\IBElementDataManager;
use Od\Entity\DBManager\IBElementDBManager;
use Od\Entity\Finder\DefaultItemFinderTrait;
use Od\Entity\Finder\IBElementFinder;

Loader::includeModule('iblock');

class IBElement extends BaseItemManager
{
    use DefaultItemFinderTrait;
    use DefaultDataManagerTrait;
    
    public static function createFinder()
    {
        return new IBElementFinder(static::getDBManager());
    }

    public static function createDataManager()
    {
        $dbManager = static::getDBManager();
        $finder    = static::getFinder();

        return new IBElementDataManager($dbManager, $finder);
    }

    /** @return IBElementDataManager */
    public static function getDataManager()
    {
        return parent::getDatamanager();
    }

    /** @return IBElementFinder */
    public static function getFinder()
    {
        return parent::getFinder();
    }

    protected static function _createDBManager()
    {
        return new IBElementDBManager();
    }
}
