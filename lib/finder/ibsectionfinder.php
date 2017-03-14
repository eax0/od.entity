<?
namespace Od\Entity\Finder;

use Od\Entity\DBManager\IBSectionDBManager;
use Od\Entity\Utils\ArrayUtils;

class IBSectionFinder extends IBEntityFinder
{
    /** @var IBSectionDBManager */
    protected $dbManager;

    protected $iblockId;

    private static $MARGIN_FIELDS = ['left_margin', 'right_margin'];

    public function __construct(IBSectionDBManager $dbManager = null)
    {
        parent::__construct($dbManager ?? new IBSectionDBManager());

        $this->setCacheEnabled(true);
        $this->addDefaultParamValue('filter', ['active' => 'Y']);
    }

    /**
     * @param $parent - field values or filter
     */
    public function children($parent, $childrenFilter = [], $childrenFields = [], $childrenOrder = [])
    {
        $parentRequiredFields   = self::$MARGIN_FIELDS;
        $parentRequiredFields[] = 'iblock_id';

        $parent = $this->_getFields($parent, $parentRequiredFields);

        if (!$parent) {
            return [];
        }

        $childrenFilter = $this->makeFilter($childrenFilter);

        $childrenFilter += [
            'left_margin'  => +$parent['left_margin'] + 1,
            'right_margin' => $parent['right_margin'],
            'iblock_id'    => $parent['iblock_id']
        ];

        return $this->items($childrenFilter, $childrenFields, $childrenOrder);
    }

    public function child($parent, $childFilter = [], $selectFields = [], $childOrder = [])
    {
        $children = $this->children($parent, $childFilter, $selectFields, $childOrder);

        return $children ? current($children) : [];
    }

    public function childrenIds($parent, $childrenFilter = [], $childrenOrder = [])
    {
        $children = $this->children($parent, $childrenFilter, ['id'], $childrenOrder);

        return $this->_mapIds($children);
    }

    public function childId($parentFilter, $childFilter)
    {
        $ids = $this->childrenIds($parentFilter, $childFilter);

        return $ids ? current($ids) : null;
    }

    /**
     * @param $child - field values or filter
     */
    public function parents($child, array $parentFilter = [], $parentFields = [], $parentsOrder = [])
    {
        $child = $this->_getFields($child, array_merge(self::$MARGIN_FIELDS, ['iblock_id']));

        if (!$child) {
            return false;
        }

        $parentFilter += [
            'iblock_id'      => $child['iblock_id'],
            '<=left_border'  => $child['left_margin'],
            '>=right_border' => $child['right_margin']
        ];

        return $this->items($parentFilter, $parentFields, $parentsOrder);
    }

    public function parentsIds($childFilter, array $parentFilter = [], $parentsOrder = [])
    {
        $parents = $this->parents($childFilter, $parentFilter, ['id'], $parentsOrder);

        return $this->_mapIds($parents);
    }

    public function parent($child, $parentFields, $parentDepthLvl = null)
    {
        $parentFilter = [];

        if (isset($parentDepthLvl)) {
            $parentFilter['depth_level'] = $parentDepthLvl;
        }

        $parents = $this->parents($child, $parentFilter, $parentFields, ['depth_level' => 'asc']);

        return empty($parents) ? [] : current($parents);
    }

    public function parentsByLvl($childrenFilter, $parentDepthLvl = 1, $parentFilter = [], $parentFields = [])
    {
        $parentFilter['depth_level'] = $parentDepthLvl;

        if ($childrenFilter['iblock_id']) {
            $parentFilter['iblock_id'] = $childrenFilter['iblock_id'];
        }

        if (!empty($parentFields)) {
            $parentFields[] = 'left_margin';
        }

        $availableParents = $this->items($parentFilter, $parentFields, ['left_margin' => 'desc']);

        $children = $this->items($childrenFilter);
        $parents  = [];

        foreach ($children as $child) {
            $parent = $this->_findParent($child['left_margin'], $availableParents);

            if ($parent) {
                $parents[$parent['id']] = $parent;
            }
        }

        return array_values($parents);
    }

    public function parentByLvl($childrenFilter, $parentDepthLvl = 1, $parentFilter = [], $parentFields = [])
    {
        $parents = $this->parentsByLvl($childrenFilter, $parentDepthLvl, $parentFilter, $parentFields);

        return $parents ? current($parents) : [];
    }

    public function mainParents($childrenFilter, $parentsFilter = [], $parentFields = [])
    {
        return $this->parentsByLvl($childrenFilter, 1, $parentsFilter, $parentFields);
    }

    public function mainParent($childFilter, $parentsFilter = [], $parentFields = [])
    {
        return $this->parentsByLvl($childFilter, 1, $parentsFilter, $parentFields);
    }

    /**
     * @param $fields - если нужны пользовательские свойства, то обычные поля нужно будет задавать вручную,
     * например ['*', 'UF_*'] - не выберет все обычные поля, нужно каждое указывать отдельно.
     * @param $startFrom
     * @return array развернутое дерево в виде списка начиная от $startFrom.
     */
    public function getTreeList($startFrom, array $fields = [], array $order = null)
    {
        if (empty($startFrom)) {
            return $this->ids();
        }

        if (!empty($fields)) {
            $fields[] = 'iblock_id';
            $fields   = array_merge($fields, self::$MARGIN_FIELDS);
        }

        $startFromSection = $this->item($startFrom, $fields);

        $filter = [
            'left_margin'  => $startFromSection['left_margin'],
            'right_margin' => $startFromSection['right_margin'],
            'iblock_id'    => $startFromSection['iblock_id']
        ];

        $order = $order ?? ['left_margin' => 'asc'];

        return $this->items($filter, $fields, $order);
    }

    public function getChain($sectionFilter, $selectFields = [], $iblockId = null)
    {
        $childId  = is_numeric($sectionFilter) ? $sectionFilter : $this->id($sectionFilter);
        $iblockId = $iblockId ?? $this->iblockId;

        // тут не кэшируем, т.к. кэшируется самим битриксом
        $dbRes = $this->dbManager->getChain($iblockId, $childId, $selectFields);
        $dbRes->setLowercaseKeys(true);

        return $dbRes->fetchAll();
    }

    public function getChainIds($sectionFilter, $iblockId = null)
    {
        $chain = $this->getChain($sectionFilter, [$this->idFieldName], $iblockId);

        return $this->_mapIds($chain);
    }

    public function getTree($parentFilter = ['depth_level' => 1], $treeFields = [], $treeOrder = ['sort' => 'desc'], $childrenFilter = [])
    {
        if (!empty($treeFields)) {
            $treeFields = array_merge($treeFields, [
                'iblock_section_id',
                'id',
                'left_margin',
                'right_margin',
                'iblock_id'
            ]);

            $treeFields = array_unique($treeFields);
        }

        $parents = $this->map($parentFilter, $treeFields, $treeOrder);
        if (!$parents) {
            return [];
        }

        $tree = $parents;

        $allChildrenFilter = (array)$childrenFilter;
        foreach ($parents as $parent) {
            $allChildrenFilter['left_margin']  = min(intval($allChildrenFilter['left_margin']), $parent['left_margin']);
            $allChildrenFilter['right_margin'] = max(intval($allChildrenFilter['right_margin']), $parent['right_margin']);
            $allChildrenFilter['iblock_id']    = $parent['iblock_id'];
        }

        $allChildren = $this->items($allChildrenFilter, $treeFields, $treeOrder);

        if (!$allChildren) {
            return $tree;
        }

        $this->_fillChildren($allChildren, $treeOrder, $tree);

        return $tree;
    }

    /* ------------ utils ------------ */
    private function _findParent($childLeftMargin, $availableParents)
    {
        foreach ($availableParents as $parent) {
            if ($parent['left_margin'] < $childLeftMargin && $parent['right_margin'] > $childLeftMargin + 1) {
                return $parent;
            }
        }

        return [];
    }

    /**
     * @param $section - filter array or field values map
     * @return array со значениями указанных полей. Если этих полей нет в $section, то
     * поля возьмутся из бд
     */
    private function _getFields($section, $requiredFields)
    {
        if (is_array($section) && $this->iblockId) {
            $section['iblock_id'] = $this->iblockId;
        }

        if (!is_array($section) || array_intersect($requiredFields, array_keys($section)) !== $requiredFields) {
            return $this->item($section, $requiredFields);
        }

        return ArrayUtils::filterByKeys($section, $requiredFields);
    }

    private function _fillChildren($all, $treeOrder, &$parents)
    {
        foreach ($parents as &$parent) {
            $children = ArrayUtils::filterByFieldValue($all, 'iblock_section_id', $parent['id']);

            if ($children) {
                $parent['children'] = ArrayUtils::makeMap($children, 'id');
                $this->_fillChildren($all, $treeOrder, $parent['children']);
            }
        }
    }
}