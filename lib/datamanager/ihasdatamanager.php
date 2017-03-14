<?
namespace Od\Entity\DataManager;

interface IHasDataManager
{
    /** @return IDataManager */
    public function getDataManager();
}