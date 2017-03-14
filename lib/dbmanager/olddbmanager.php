<?
namespace Od\Entity\DBManager;

use Od\Entity\DBResult\OldDBResult;

abstract class OldDBManager implements IDBManager
{
    protected $oldEntityInstance;
    
    public function __construct()
    {
        $this->oldEntityInstance = $this->getOldClassInstance();
    }
    
    public function select($params = [])
    {
        $bxParams = $this->makeBXParams($params);

        $dbRes = $this->getBXDBResult($bxParams);

        return $this->convertDBResult($dbRes, $params);
    }

    public function add($fields)
    {
        $instance = $this->oldEntityInstance;

        return $instance && method_exists($instance, 'Add') ? $instance->Add($fields) : false;
    }

    public function update($id, $fields)
    {
        $instance = $this->oldEntityInstance;

        return $instance && method_exists($instance, 'Update') ? $instance->Update($id,  $fields) : false;
    }

    public function delete($id)
    {
        $instance = $this->oldEntityInstance;

        return $instance && method_exists($instance, 'Delete') ? $instance->Delete($id) : false;
    }

    public function convertDBResult($bxDBResult, $params)
    {
        return $bxDBResult ? new OldDBResult($bxDBResult, $params) : null;
    }

    public function makeBXParams($params = [])
    {
        return [
            'filter'     => array_change_key_case((array)$params['filter'], CASE_UPPER),
            'select'     => array_map('strtoupper', (array)$params['select']),
            'order'      => (array)$params['order'],
            'group'      => $params['group'] ? $params['group'] : false,
            'nav_params' => $params['limit'] ? ['nTopCount' => $params['limit']] : false
        ];
    }

    public function getPrimaryFieldName()
    {
        return 'id';
    }

    public function getSlugFieldName()
    {
        return 'code';
    }

    public function getDateFieldNames()
    {
        return [];
    }

    /** @return \CAllDBResult */
    abstract protected function getBXDBResult($bxParams = []);

    abstract protected function getOldClassInstance();
}