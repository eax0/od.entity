<?
namespace Od\Entity\ItemManager;

use Od\Entity\DataManager\DataManager;
use Od\Entity\DataManager\DefaultDataManagerTrait;
use Od\Entity\DBManager\IBSectionDBManager;
use Od\Entity\Finder\DefaultItemFinderTrait;
use Od\Entity\Finder\IBSectionFinder;

class IBSection extends BaseItemManager
{
    use DefaultItemFinderTrait;
    use DefaultDataManagerTrait;
    
    public static function createFinder()
    {
        return new IBSectionFinder(static::getDBManager());
    }

    public static function createDataManager()
    {
        $finder = static::getFinder();
        $finder->setDefaultParamValue('filter', []);

        return new DataManager(static::getDBManager(), $finder);
    }

    /** @return IBSectionFinder */
    public static function getFinder()
    {
        return parent::getFinder();
    }

    protected static function _createDBManager()
    {
        return new IBSectionDBManager();
    }
}
