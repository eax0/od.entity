<?
namespace Od\Entity\DataManager;

use Od\Entity\DBManager\IDBManager;
use Od\Entity\Finder\IItemFinder;

class DataManager implements IDataManager
{
    /** @var IDBManager */
    protected $dbManager;
    /** @var IItemFinder */
    protected $finder;
    protected $primaryFieldName;

    public function __construct(IDBManager $dbManager, IItemFinder $finder = null)
    {
        $this->finder           = $finder;
        $this->dbManager        = $dbManager;
        $this->primaryFieldName = $this->dbManager->getPrimaryFieldName();
    }

    /* ------------ ADD ------------ */
    public function add(array $fields)
    {
        return $this->dbManager->add($fields);
    }
    
    public function addItems(array $fieldsList = [])
    {
        $addedItemsIds = [];
        foreach ($fieldsList as $fields) {
            $addedItemsIds[] = $this->add($fields);
        }

        return $addedItemsIds;
    }

    public function addOrUpdate($filter, array $fields, $fieldsToUpdate = [])
    {
        if (!$this->finder) {
            return false;
        }
        
        if (!$id = $this->finder->id($filter)) {
            return $this->add($fields);
        }

        if (empty($fieldsToUpdate)) {
            $fieldsToUpdate = $fields;
        }

        return $this->updateById($id, $fieldsToUpdate);
    }

    public function addUnique($filter, array $fields)
    {
        if (!$this->finder) {
            return false;
        }
        
        if (!$id = $this->finder->id($filter)) {
            return $this->add($fields);
        }

        return $id;
    }

    /* ------------ UPDATE ------------ */
    public function updateById($id, array $fields)
    {
        $res = $this->dbManager->update($id, $fields);

        return $res ? $id : false;
    }

    public function updateItems($filter, array $fields)
    {
        if (!$this->finder) {
            return false;
        }
        
        $ids        = $this->finder->ids($filter);
        $updatedIds = [];

        foreach ($ids as $id) {
            $updatedIds[] = $this->updateItem($id, $fields);
        }

        return array_filter($updatedIds);
    }

    public function updateItem($filter, array $fields)
    {
        if (!$this->finder) {
            return false;
        }
        
        $id = $this->finder->Id($filter);

        return $id ? $this->updateById($id, $fields) : false;
    }

    /* ------------ DELETE ------------ */
    public function deleteById($id)
    {
        return $this->dbManager->delete($id);
    }

    public function deleteItem($filter)
    {
        if (!$this->finder) {
            return false;
        }
        
        $id = $this->finder->id($filter);
        
        return $this->deleteById($id);
    }

    public function deleteItems($filter)
    {
        if (!$this->finder) {
            return false;
        }
        
        $ids        = $this->finder->ids($filter);
        $deletedIds = [];

        foreach ($ids as $id) {
            $deletedIds[] = $this->deleteById($id);
        }

        return array_filter($deletedIds);
    }
}