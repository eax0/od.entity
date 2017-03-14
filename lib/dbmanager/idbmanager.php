<?
namespace Od\Entity\DBManager;
use Od\Entity\DBResult\BaseDBResult;


/**              
 * Классы для внутренней работы. Являются обертками классов сущностей битрикса
 * (CIBlockELemnt, CSaleOrder и тп). Нужны для универсалиции кода и изоляции обращений к старым 
 * классам битрикса. Никакой логики(алгоритмов) здесь быть не должно.
 */
interface IDBManager
{
    /** @return BaseDBResult */
    public function select($params = []);

    public function add($fields);

    public function update($id, $fields);

    public function delete($id);
    
    public function getPrimaryFieldName();
    
    public function getSlugFieldName();
    
    public function getDateFieldNames();
}