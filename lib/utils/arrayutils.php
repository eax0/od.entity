<?
namespace Od\Entity\Utils;

class ArrayUtils
{
    public static function mapField($array, $fieldName)
    {
        return array_map(function ($item) use ($fieldName) {
            return $item[$fieldName];
        }, (array)$array);
    }

    public static function filterByFieldValue($array, $fieldName, $fieldValue, $operator = null, $operationResult = true)
    {
        return array_filter((array)$array, function ($item) use ($fieldName, $fieldValue, $operator, $operationResult) {
            switch (strval($operator)) {
                case '===':
                    return $operationResult === ($item[$fieldName] === $fieldValue);
                case 'in':
                    return $operationResult === is_array($fieldValue) && in_array($item[$fieldName], $fieldValue);
                case 'not-in':
                    return $operationResult === is_array($fieldValue) && !in_array($item[$fieldName], $fieldValue);
                case '!=':
                    return $operationResult === ($item[$fieldName] != $fieldValue);
                case '!==':
                    return $operationResult === ($item[$fieldName] !== $fieldValue);
                case '<':
                    return $operationResult === ($item[$fieldName] < $fieldValue);
                case '>':
                    return $operationResult === ($item[$fieldName] > $fieldValue);
                case 'empty':
                    return $operationResult === (empty($item[$fieldName]) === $fieldValue);
                default:
                    return $operationResult === ($item[$fieldName] == $fieldValue);
            }
        });
    }

    public static function &findByFieldValue(&$items, $field, $value, $strict = false)
    {
        foreach ($items as &$item) {
            if (!$strict && $item[$field] == $value || $strict && $item[$field] === $value) {
                return $item;
            }
        }

        return [];
    }

    public static function uniqueByField($items, $field)
    {
        return array_values(self::makeMap($items, $field));
    }

    public static function assocShuffle($arr)
    {
        $keys = array_keys($arr);
        shuffle($keys);
        $shuffled = [];

        foreach ($keys as $id) {
            $shuffled[$id] = $arr[$id];
        }

        return $shuffled;
    }

    public static function sortByFieldValue(&$arr, $fieldName, $asc = true)
    {
        uasort($arr, function ($a, $b) use ($fieldName, $asc) {
            if (!isset($a[$fieldName]) && !isset($b[$fieldName])) {
                return 0;
            }

            if (!isset($a[$fieldName])) {
                $result = -1;
            } else {
                if (!isset($b[$fieldName])) {
                    $result = 1;
                } else {
                    $result = $a[$fieldName] < $b[$fieldName] ? -1 : 1;
                }
            }

            return $asc ? $result : -$result;
        });
    }

    public static function jsEscapeValues($arr)
    {
        if (!is_array($arr)) {
            return $arr;
        }

        foreach ($arr as $i => $val) {
            if (is_array($val)) {
                $arr[$i] = self::jsEscapeValues($val);
            } else {
                if (is_string($val)) {
                    $arr[$i] = \CUtil::JSEscape($val);
                }
            }
        }

        return $arr;
    }

    public static function isAssoc($arr)
    {
        return is_array($arr) && range(0, count($arr) - 1) !== array_keys($arr);
    }

    /**
     * @param $arr - 2 or more items
     * @return array of all possible permutations
     */
    public static function permutations($arr)
    {
        if (count($arr) === 2) {
            return [[$arr[0], $arr[1]], [$arr[1], $arr[0]]];
        }

        $result = [];

        for ($i = 0; $i < count($arr); $i++) {
            $copy = $arr;

            $tempResult = [];
            $beginning  = array_splice($copy, $i, 1);
            $endingList = self::permutations($copy);

            foreach ($endingList as $ending) {
                $tempResult[] = array_merge($beginning, $ending);
            }

            $result = array_merge($result, $tempResult);
        }

        return $result;
    }

    public static function variations($arr, $temp = '', &$result = null)
    {
        if ($result === null) {
            $result = [];
        }

        if ($temp) {
            $result[] = $temp;
        }

        for ($i = 0; $i < sizeof($arr); $i++) {
            $arrcopy = $arr;
            $elem    = array_splice($arrcopy, $i, 1);
            $newTemp = $temp ? "$temp " . $elem[0] : $elem[0];

            if (sizeof($arrcopy) > 0) {
                self::variations($arrcopy, $newTemp, $result);
            } else {
                $result[] = $newTemp;
            }
        }

        return $result;
    }

    public static function changeKeysCase($arr, $case = CASE_LOWER, $deep = true)
    {
        $arr = array_change_key_case($arr, $case);
        foreach ($arr as $key => &$val) {
            if ($deep && is_array($val) && !empty($val)) {
                $val = self::changeKeysCase($val, $case);
            }
        }

        return $arr;
    }

    public static function deleteByKeyPrefix(&$arr, $keyPrefix, $recursive = false)
    {
        foreach ($arr as $key => &$item) {
            if (strpos($key, $keyPrefix) === 0) {
                unset($arr[$key]);
            }

            if ($recursive && is_array($item) && !empty($item)) {
                self::deleteByKeyPrefix($item, $keyPrefix, true);
            }
        }
    }

    public static function filterByKeys($arr, $keys)
    {
        foreach ($arr as $key => $value) {
            if (!in_array($key, $keys)) {
                unset($arr[$key]);
            }
        }

        return $arr;
    }

    public static function makeMap($array, $primaryFieldName)
    {
        $map = [];
        foreach ($array as $item) {
            if (array_key_exists($primaryFieldName, $item)) {
                $map[$item[$primaryFieldName]] = $item;
            }
        }

        return $map;
    }

    /**
     * @param $from - old key or array of replacements
     * @param null $to - new key or null
     */
    public static function replaceKey($array, $from, $to = null)
    {
        if (!is_array($from)) {
            $from = [$from => $to];        
        }

        $values  = array_values($array);
        $oldKeys = array_keys($array);
        $newKeys = $oldKeys;

        foreach ($from as $_from => $to) {
            $pos = array_search($_from, $oldKeys);

            if ($pos !== false) {
                $newKeys[$pos] = $to;
            }
        }
        
        return array_combine($newKeys, $values);
    }
}
