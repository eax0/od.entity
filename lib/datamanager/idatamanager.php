<?
namespace Od\Entity\DataManager;

/**                                   
 * Производит манипуляции с сущностями.
 */
interface IDataManager
{
    public function add(array $fields);

    public function addItems(array $fieldsList);

    public function addOrUpdate($filter, array $fields, $fieldsToUpdate = []);

    public function addUnique($filter, array $fields);

    public function updateById($id, array $fields);

    public function updateItem($filter, array $fields);

    public function updateItems($filter, array $fields);

    public function deleteById($id);

    public function deleteItem($filter);

    public function deleteItems($filter);
}
