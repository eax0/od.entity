<?
namespace Od\Entity\DBManager;

use Od\Entity\DBResult\OldDBResult;

class IBElementDBManager extends IBEntityDBManager
{
    /** @var \CIBlockElement */
    protected $oldEntityInstance;

    protected function getBXDBResult($bxParams = [])
    {
        return $this->oldEntityInstance->GetList($bxParams['order'], $bxParams['filter'], $bxParams['group'], $bxParams['nav_params'], $bxParams['select']);
    }

    public function getSections($elementIds, $sectionSelectFields)
    {
        $dbRes = $this->oldEntityInstance->getElementGroups($elementIds, true, $sectionSelectFields);
        
        return $this->convertDBResult($dbRes);
    }

    public function setElementSections($elementId, $newSectionsIds, $iblockId = null, $mainSectionId = null)
    {
        return $this->oldEntityInstance->setElementSection($elementId, $newSectionsIds, false, $iblockId, $mainSectionId);
    }

    public function setPropertyValue($elemId, $iblockId, $propCode, $value)
    {
        $this->oldEntityInstance->setPropertyValues($elemId, $iblockId, $value, $propCode);
    }

    public function getProperty($elemId, $propCode, $iblockId)
    {
        $dbRes = $this->oldEntityInstance->getProperty($iblockId, $elemId, [], ["CODE" => $propCode]);
        
        return new OldDBResult($dbRes);
    }

    protected function getOldClassInstance()
    {
        return new \CIBlockElement();
    }

    public function getDateFieldNames()
    {
        return ['active_to', 'active_from', 'timestamp_x', 'date_active_from', 'date_active_to', 'date_create'];
    }
}