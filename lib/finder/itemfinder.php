<?
namespace Od\Entity\Finder;

use Od\Entity\DBManager\IDBManager;
use Od\Entity\DBResult\BaseDBResult;
use Od\Entity\Utils\ArrayUtils;
use Od\Entity\Utils\DateUtils;

class ItemFinder implements IItemFinder
{
    protected $dbManager;
    protected $idFieldName;
    protected $slugFieldName;
    protected $dateFieldNames;

    protected $lowercaseFields = true;

    private $cacheEnabled = false;
    private $defaultParams = [];

    private static $_cache = [];

    public function __construct(IDBManager $dbManager)
    {
        $this->dbManager      = $dbManager;
        $this->idFieldName    = $dbManager->getPrimaryFieldName();
        $this->slugFieldName  = $dbManager->getSlugFieldName();
        $this->dateFieldNames = $dbManager->getDateFieldNames();
    }

    public function item($filter = [], $fields = [], $orderBy = [], $offset = null)
    {
        $items = $this->limited(1, $filter, $fields, $orderBy, $offset);

        return is_array($items) && count($items) > 0 ? array_shift($items) : [];
    }

    public function items($filter = [], $fields = [], $orderBy = [], $extendedParams = [])
    {
        return $this->selectAsArray($filter, $fields, $orderBy, $extendedParams);
    }

    public function map($filter = [], $fields = [], $orderBy = [], $extendedParams = [])
    {
        if (!empty($fields)) {
            $fields   = (array)$fields;
            $fields[] = $this->idFieldName;
        }

        $items = $this->items($filter, $fields, $orderBy, $extendedParams);
        $map   = [];

        foreach ($items as $item) {
            $map[$item[$this->idFieldName]] = $item;
        }

        return $map;
    }

    public function limited($limit, $filter = [], $fields = [], $orderBy = [], $offset = null)
    {
        return $this->selectAsArray($filter, $fields, $orderBy, ['limit' => $limit, 'offset' => $offset]);
    }

    public function grouped($groupBy, $filter = [], $orderBy = [], $limit = null)
    {
        return $this->selectAsArray($filter, $groupBy, $orderBy, ['group' => $groupBy, 'limit' => $limit]);
    }

    public function exists($filter = [])
    {
        return $this->count($filter) > 0;
    }

    public function count($filter = [])
    {
        return count($this->ids($filter));
    }

    public function id($filter = [], $orderBy = [])
    {
        $item = $this->item($filter, [$this->idFieldName], $orderBy);

        return $item ? $item[$this->idFieldName] : null;
    }

    public function ids($filter = [], $orderBy = [], $limit = null)
    {
        $items = $this->selectAsArray($filter, [$this->idFieldName], $orderBy, ['limit' => $limit]);
        $ids   = $this->_mapIds($items);

        return array_map('intval', $ids);
    }

    public function select($filter = [], $fields = [], $orderBy = [], $extendedParams = [])
    {
        $params = $this->makeParams($filter, $fields, $orderBy, $extendedParams);
        $this->modifyParams($params);

        if (!$this->validateParams($params)) {
            return new BaseDBResult();
        }

        $dbRes = $this->dbManager->select($params);
        $dbRes->setLowercaseKeys($this->lowercaseFields);

        if (is_array($params['select']) && ArrayUtils::isAssoc($params['select'])) {
            $dbRes->setAliasMap($params['select']);
        }

        return $dbRes;
    }

    /* ------------ internal ------------ */
    protected function selectAsArray($filter = [], $fields = [], $orderBy = [], $extendedParams = [])
    {
        $cacheKey = serialize(func_get_args());
        $cacheRes = $this->_cacheDbRes($cacheKey);
        if (!is_null($cacheRes)) {
            return $cacheRes;
        }

        $items = $this->select($filter, $fields, $orderBy, $extendedParams)->fetchAll();

        $this->_cacheDbRes($cacheKey, $items);

        return $items;
    }

    protected function modifyParams(&$params)
    {
        $filter            = $params['filter'];
        $isFilterByPrimary = !empty($filter) && !empty($filter[$this->idFieldName]);

        if ($isFilterByPrimary) {
            $limit = is_array($filter[$this->idFieldName]) ? count($filter[$this->idFieldName]) : 1;

            $params['limit'] = min($limit, $params['limit']);
        }
    }

    private function makeParams($filter = [], $fields = [], $orderBy = [], $extendedParams = [])
    {
        $params = array_filter([
            'filter' => $filter,
            'select' => $fields,
            'order'  => $orderBy,
        ]);

        $params += (array)$extendedParams;

        $params['filter'] = $this->makeFilter($params['filter']);
        $params['select'] = $this->makeSelect($params['select']);
        $params['order']  = $this->makeOrder($params['order']);

        if (!empty($this->defaultParams['filter'])) {
            $params['filter'] = (array)$params['filter'] + $this->defaultParams['filter'];
        }

        if (!empty($this->defaultParams['select'])) {
            $params['select'] = array_merge($this->defaultParams['select'], (array)$params['select']);
        }

        if (!empty($this->defaultParams['order'])) {
            $params['order'] = array_merge($this->defaultParams['order'], (array)$params['order']);
        }

        return $params;
    }

    final protected function makeFilter($filter = null)
    {
        if (empty($filter)) {
            return $filter;
        }

        if ($this->idFieldName) {
            if (is_numeric($filter) && $filter > 0) {
                $filter = [$this->idFieldName => $filter];
            } elseif (is_array($filter) && !ArrayUtils::isAssoc($filter)) {
                $ids = array_filter($filter, 'is_numeric');

                if (count($ids) === count($filter)) {
                    $filter = [$this->idFieldName => $filter];
                }
            }
        }

        // if its symbolic string
        if ($this->slugFieldName && is_string($filter)) {
            $filter = [$this->slugFieldName => $filter];
        }

        if (is_array($filter) && !empty($filter)) {
            foreach ($filter as $field => $value) {
                $fieldName = str_replace(['>', '<', '>=', '<=', '><', '!><', '=', '%', '?'], '', $field);
                if (in_array($fieldName, $this->dateFieldNames) && $value) {
                    $filter[$field] = DateUtils::toFilterDate($value);
                }
            }
        }

        return $filter;
    }

    final protected function makeSelect($select = null)
    {
        return (array)$select;
    }

    final protected function makeOrder($order = null)
    {
        if (is_string($order) && strlen($order) > 0) {
            $order = [$order => 'asc'];
        }

        return (array)$order;
    }

    protected function validateParams($params = [])
    {
        return true;
    }

    private function _cacheDbRes($params, $value = null)
    {
        $cacheId = md5(serialize($params));

        if (isset($value)) {
            if ($this->cacheEnabled) {
                self::$_cache[$cacheId] = $value;
            }

            return $this->cacheEnabled;
        }

        if ($res = self::$_cache[$cacheId]) {
            return $res;
        }

        return null;
    }

    /* ------------ settings ------------ */
    public function setDefaultParamValue($paramName, $value)
    {
        $this->defaultParams[$paramName] = $value;
    }

    public function addDefaultParamValue($paramName, $value)
    {
        if (!$this->defaultParams[$paramName]) {
            $this->defaultParams[$paramName] = [];
        }

        $this->defaultParams[$paramName] = array_merge($this->defaultParams[$paramName], (array)$value);
    }

    public function setCacheEnabled($value)
    {
        $this->cacheEnabled = !!$value;
    }

    public function setLowercaseFields($value)
    {
        $this->lowercaseFields = $value;
    }

    /* ------------ utils ------------ */
    protected function _mapIds($array)
    {
        return ArrayUtils::mapField($array, $this->idFieldName);
    }
}
