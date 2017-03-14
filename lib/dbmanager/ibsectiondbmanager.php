<?
namespace Od\Entity\DBManager;

class IBSectionDBManager extends IBEntityDBManager
{
    /** @var \CIBlockSection */
    protected $oldEntityInstance;

    public function getChain($iblockId, $fromSectionId, $selectFields)
    {
        $dbRes = $this->oldEntityInstance->getNavChain($iblockId, $fromSectionId, $selectFields);
        
        return $this->convertDBResult($dbRes);
    }

    protected function getBXDBResult($bxParams = [])
    {
        return $this->oldEntityInstance->GetList($bxParams['order'], $bxParams['filter'], false, $bxParams['select'], $bxParams['nav_params']);
    }

    protected function getOldClassInstance()
    {
        return new \CIBlockSection();
    }

    public function getDateFieldNames()
    {
        return ['date_create', 'timestamp_x'];
    }
}