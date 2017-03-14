<?
namespace Od\Entity\DBResult;

use CAllDBResult;

class OldDBResult extends BaseDBResult
{
    protected $dbResult;
    protected $params;
    
    public function __construct(CAllDBResult &$dbResult, $params)
    {
        $this->dbResult = &$dbResult;
        $this->params = $params;
    }

    /** @return bool|array */
    public function fetchInternal()
    {
        if ($this->dbResult) {
            return $this->dbResult->Fetch();
        }

        return false;
    }
}
