<?
namespace Od\Entity\ItemManager;

use Bitrix\Main\Loader;
use Od\Entity\DBManager\IDBManager;
use Od\Entity\DBManager\OldDBManager;

Loader::includeModule('iblock');

class IBlock extends BaseItemManager
{
    public static function id($code, $type = null)
    {
        $finder = static::getFinder();
        $filter = ['code' => $code];

        if ($type) {
            $filter['type'] = $type;
        }

        return $finder->id($filter);
    }

    public static function createFinder()
    {
        $finder = parent::createFinder();
        $finder->setCacheEnabled(true);
        
        return $finder;
    }

    /** @return IDBManager */
    protected static function _createDBManager()
    {
        return new class extends OldDBManager {
            /** @return \CAllDBResult */
            protected function getBXDBResult($bxParams = [])
            {
                return \CIBlock::GetList($bxParams['order'], $bxParams['filter']);
            }

            protected function getOldClassInstance()
            {
                return new \CIBlock;
            }
        };
    }
}