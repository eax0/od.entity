<?
namespace Od\Entity\Finder;

interface IHasItemFinder
{
    /** @return IItemFinder */
    public static function getFinder();
}