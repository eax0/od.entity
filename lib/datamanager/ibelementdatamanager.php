<?
namespace Od\Entity\DataManager;

use Od\Entity\DBManager\IBElementDBManager;
use Od\Entity\Finder\IBElementFinder;
use Od\Entity\ItemManager\IBSection;

class IBElementDataManager extends DataManager
{
    /** @var IBElementFinder */
    protected $finder;
    /** @var IBElementDBManager */
    protected $dbManager;

    public function __construct(IBElementDBManager $dbManager = null, IBElementFinder $finder = null)
    {
        $dbManager = $dbManager ?? new IBElementDBManager();
        $finder    = $finder ?? new IBElementFinder($dbManager);
        
        parent::__construct($dbManager, $finder);
    }

    public function updateSectionBinding($elementsIds, $sectionPrimary, $unbind = false)
    {
        if (empty($elementsIds)) return true;

        $targetSectionId = $sectionPrimary;
        if (!is_numeric($sectionPrimary) && is_string($sectionPrimary)) {
            $targetSectionId = IBSection::findId($sectionPrimary);
        }

        if (!$targetSectionId) {
            return false;
        }

        $elementsIds = (array)$elementsIds;
        $elements    = $this->finder->items(['ID' => $elementsIds], ['ID', 'IBLOCK_SECTION_ID', 'IBLOCK_ID']);

        foreach ($elements as $element) {
            $iblockId      = $element['IBLOCK_ID'];
            $id            = $element['ID'];
            $currSectionId = $element['IBLOCK_SECTION_ID'];
            $newSectionId  = $currSectionId != $targetSectionId || !$unbind ? $currSectionId : null;

            $currSectionIds = $this->finder->getSectionsIds($id);
            $newSectionsIds = array_filter(
                $currSectionIds, function ($id) use ($unbind, $targetSectionId) {
                return !$unbind || $id != $targetSectionId;
            }
            );

            if (!$unbind) {
                $newSectionsIds[] = $targetSectionId;
            }

            $newSectionsIds = array_unique($newSectionsIds);

            $this->dbManager->setElementSections($id, $newSectionsIds, $iblockId, $newSectionId);
        }

        return true;
    }

    public function setPropertyValue($elemId, $iblockId, $propCode, $value)
    {
        return $this->dbManager->setPropertyValue($elemId, $iblockId, $propCode, $value);
    }

    public function setPropertyValues($elemId, $iblockId, $propCode, $value)
    {
        return $this->dbManager->setPropertyValue($elemId, $iblockId, $propCode, $value);
    }
}
