<?
namespace Od\Entity\DBResult;

use CAllDBResult;

class IBEntityDBResult extends OldDBResult
{
    private $selectFields = [];

    public function __construct(CAllDBResult &$dbResult, $params)
    {
        parent::__construct($dbResult, $params);

        $this->selectFields = is_array($params['select']) ? $params['select'] : [];
    }

    /** @return bool|array */
    public function fetchInternal()
    {
        if (!$this->dbResult) return false;

        $detailPageUrlExists = in_array('DETAIL_PAGE_URL', $this->selectFields) || in_array('detail_page_url', $this->selectFields);
        $detailPageUrlExists |= in_array('SECTION_PAGE_URL', $this->selectFields) || in_array('section_page_url', $this->selectFields);
        $detailPageUrlExists |= empty($this->selectFields) || in_array('*', $this->selectFields);

        if ($detailPageUrlExists && $this->dbResult instanceof \CIBlockResult) {
            $fetchResult = $this->dbResult->GetNext();

            $this->filterFields($fetchResult);

            return $fetchResult;
        }

        return parent::fetchInternal();
    }

    private function filterFields(&$fetchResult)
    {
        $fetchResult = array_filter($fetchResult, function ($key) {
            return strpos($key, '~') !== 0;
        }, ARRAY_FILTER_USE_KEY);
    }
}
