<?
namespace Od\Entity\Finder;

use Od\Entity\DBResult\BaseDBResult;

interface IItemFinder
{
    public function item($filter = [], $fields = [], $orderBy = []);
    
    public function items($filter = [], $fields = [], $orderBy = [], $extendedParams = []);

    public function map($filter = [], $fields = [], $orderBy = [], $extendedParams = []);
    
    public function limited($limit, $filter = [], $fields = [], $orderBy = [], $offset = null);

    public function grouped($groupBy, $filter = [], $orderBy = [], $limit = null);

    public function exists($filter = []);

    public function count($filter = []);

    public function id($filter = [], $orderBy = []);

    public function ids($filter = [], $orderBy = [], $limit = null);

    /** @return BaseDBResult */
    public function select($filter = [], $fields = [], $orderBy = [], $extendedParams = []);
}
