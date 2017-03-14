<?
namespace Od\Entity\DBManager;

use Bitrix\Main\Loader;
use Od\Entity\DBResult\IBEntityDBResult;
use Od\Entity\DBResult\OldDBResult;

abstract class IBEntityDBManager extends OldDBManager
{
    public function __construct()
    {
        parent::__construct();

        Loader::includeModule('iblock');
    }

    public function convertDBResult($bxDBResult, $params)
    {
        if ($bxDBResult && $bxDBResult instanceof \CIBlockResult) {
            return new IBEntityDBResult($bxDBResult, $params);
        }
        
        return new OldDBResult($bxDBResult);
    }
}