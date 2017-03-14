<?
namespace Od\Entity\Finder;

use Od\Entity\DBManager\IBElementDBManager;

class IBElementFinder extends IBEntityFinder
{
    /** @var IBElementDBManager */
    protected $dbManager;

    public function __construct(IBElementDBManager $dbManager = null)
    {
        parent::__construct($dbManager ?? new IBElementDBManager());
    }

    public function getSections($elementIds, $sectionSelectFields = [])
    {
        $dbRes = $this->dbManager->getSections($elementIds, $sectionSelectFields);
        $dbRes->setLowercaseKeys(true);

        return $dbRes->fetchAll();
    }

    public function getSectionsIds($elementIds)
    {
        $sections = $this->getSections($elementIds, [$this->idFieldName]);

        return $this->_mapIds($sections);
    }

    public function getPropertyValues($elemId, $propCode, $iblockId)
    {
        $res = $this->dbManager->getProperty($elemId, $propCode, $iblockId);

        $result = [];
        while ($prop = $res->fetch()) {
            if (!empty($prop['VALUE'])) {
                $result[] = $prop['VALUE'];
            }
        }

        return $result;
    }

    public function getPropertyValue($elemId, $propCode, $iblockId)
    {
        $values = $this->getPropertyValues($elemId, $propCode, $iblockId);

        return empty($values) ? null : current($values);
    }

    /* ------------ internal ------------ */
    protected function modifyParams(&$params)
    {
        $params['filter'] = $this->processFilter($params['filter']);
    }

    protected function processFilter($filter)
    {
        if ($filter['LOGIC']) {
            foreach ($filter as $i => $f) {
                if (stripos('LOGIC', $i) !== 0) {
                    $filter[$i] = $this->processFilterSimple($f);
                }
            }
        } else {
            $filter = $this->processFilterSimple($filter);
        }

        return $filter;
    }

    protected function processFilterSimple($filter)
    {
        if (is_array($filter) && $filter['section_id'] && !isset($filter['include_subsections'])) {
            $filter['include_subsections'] = 'Y';
        }
        
        return $filter;
    }
}