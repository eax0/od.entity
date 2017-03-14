<?
namespace Od\Entity\DBResult;

use Od\Entity\Utils\ArrayUtils;

class BaseDBResult
{
    protected $aliasMap = null;
    protected $lowecaseKeys = false;
    
    /**
     * @return array
     */
    public function fetchAll()
    {
        $items = [];

        while ($item = $this->fetch()) {
            $items[] = $item;
        }

        return $items;
    }

    /** @return bool|array */
    public function fetch()
    {
        $item = $this->fetchInternal();

        if ($this->aliasMap) {
            $this->manageAliases($item);
        }

        if ($this->lowecaseKeys) {
            $item = ArrayUtils::changeKeysCase($item, CASE_LOWER, true);
        }

        return $item;
    }

    public function setAliasMap($map)
    {
        $this->aliasMap = $map;
    }

    public function setLowercaseKeys($lowercase)
    {
        $this->lowecaseKeys = $lowercase;
    }

    private function manageAliases($aliasMap, &$item)
    {
        foreach ($aliasMap as $alias => $field) {
            if (array_key_exists($field, $item)) {
                $item[$alias] = $item[$field];
                unset($item[$field]);
            }
        }
    }

    protected function fetchInternal() {
        return false;
    }
}
