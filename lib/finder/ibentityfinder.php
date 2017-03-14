<?
namespace Od\Entity\Finder;

use Bitrix\Main\Loader;
use Od\Entity\DBManager\IDBManager;
use Od\Entity\ItemManager\IBlock;

class IBEntityFinder extends ItemFinder
{
    protected $iblockId;

    public function __construct(IDBManager $dbManager)
    {
        Loader::includeModule('iblock');
        
        parent::__construct($dbManager);
    }
    
    public function setIblockPrimary($primary, $type = null)
    {
        $id = is_numeric($primary) ? $primary : IBlock::id($primary, $type);

        $this->iblockId = $id;
        $this->addDefaultParamValue('filter', ['iblock_id' => $id]);
    }
}