<?
namespace Od\Entity\DataManager;

trait HasDataManagerTrait
{
    public static function addItems(array $fieldsList = [])
    {
        return static::getDataManager()->addItems($fieldsList);
    }

    public static function addOrUpdate($filter, array $fields, $fieldsToUpdate = [])
    {
        return static::getDataManager()->addOrUpdate($filter, $fields, $fieldsToUpdate);
    }

    public static function addUnique($filter, array $fields)
    {
        return static::getDataManager()->addUnique($filter, $fields);
    }

    public static function updateItems($filter, array $fields)
    {
        return static::getDataManager()->updateItems($filter, $fields);
    }

    public static function updateItem($filter, array $fields)
    {
        return static::getDataManager()->updateItem($filter, $fields);
    }

    public static function deleteItem($filter)
    {
        return static::getDataManager()->deleteItem($filter);
    }

    public static function deleteItems($filter)
    {
        return static::getDataManager()->deleteItems($filter);
    }

    public static function add(array $fields)
    {
        return static::getDataManager()->add($fields);
    }

    public static function updateById($id, array $fields)
    {
        return static::getDataManager()->updateById($id, $fields);
    }

    public static function deleteById($id)
    {
        return static::getDataManager()->deleteById($id);
    }

    /** @return IDataManager */
    abstract protected static function getDataManager();
}