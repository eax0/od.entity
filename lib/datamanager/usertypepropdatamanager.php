<?
namespace Od\Entity\DataManager;

use Od\Core\Utils\StringUtils;
use Od\Entity\DBManager\OldDBManager;
use Od\Entity\Utils\ArrayUtils;

class UserTypePropDataManager extends DataManager
{
    public function __construct()
    {
        parent::__construct(new class extends OldDBManager
        {
            /** @return \CAllDBResult */
            protected function getBXDBResult($bxParams = [])
            {
                return \CUserTypeEntity::GetList($bxParams['order'], $bxParams['filter']);
            }

            protected function getOldClassInstance()
            {
                return new \CUserTypeEntity();
            }
        });
    }

    public static function addSectionProp($iblockId, $fields)
    {
        $fields['entity_id'] = "IBLOCK_$iblockId" . "_SECTION";

        return self::add($fields);
    }

    /**
     * TYPES:
     *
     * enumeration - Список
     * double - Число
     * integer - Целое число
     * boolean - Да/Нет
     * string - Строка
     * file - Файл
     * video - Видео
     * datetime - Дата/Время
     * iblock_section - Привязка к разделам инф. блоков
     * iblock_element - Привязка к элементам инф. блоков
     * string_formatted - Шаблон
     */
    public function add(array $fields)
    {
        $code     = 'UF_' . strtoupper($fields["code"]);
        $lang     = ['ru' => $fields["name"]];
        $settings = [];

        if (is_array($fields['settings'])) {
            $settings += $fields['settings'];
        }

        if ($fields['default_value']) {
            $settings['default_value'] = $fields['default_value'];
        }

        if ($fields['type'] === 'list') {
            $fields['type'] = 'enumeration';
        }

        $settings = ArrayUtils::changeKeysCase($settings, CASE_UPPER);

        $arUserFields = [
            'ENTITY_ID'         => $fields["entity_id"],
            'FIELD_NAME'        => $code,
            'XML_ID'            => $code,
            'USER_TYPE_ID'      => $fields["type"] ?? 'string',
            'SORT'              => $fields["sort"] ?? 500,
            'MULTIPLE'          => $fields["multiple"] ? 'Y' : 'N',
            'MANDATORY'         => $fields["required"] ? 'Y' : 'N',
            /**
             * Показывать в фильтре списка. Возможные значения:
             * не показывать = N, точное совпадение = I,
             * поиск по маске = E, поиск по подстроке = S
             */
            'SHOW_FILTER'       => $fields["filter_params"] ?? 'S',
            // Показывать в списке
            'SHOW_IN_LIST'      => $fields["show_in_list"],
            // Разрешение на редактирование пользователем
            'EDIT_IN_LIST'      => $fields["edit_in_list"],
            'IS_SEARCHABLE'     => $fields["is_searchable"],
            'SETTINGS'          => $settings,
            /* Название */
            'EDIT_FORM_LABEL'   => $lang,
            /* Подпись в списке */
            'LIST_COLUMN_LABEL' => $lang,
            /* Подпись в фильтре */
            'LIST_FILTER_LABEL' => $lang,
        ];

        return $this->dbManager->Add($arUserFields);
    }

    /**
     * @param $values - list of values.
     * Value item - ['code' => code, 'value' => value, 'sort' => 100, 'default' => true]
     */
    public static function addListValues($propId, $values)
    {
        $obEnum = new \CUserFieldEnum;

        $dbValues = [];

        foreach ($values as $i => $value) {
            $dbValues["n$i"] = [
                'XML_ID' => $value['code'],
                'VALUE'  => $value['value'],
                'DEF'    => $value['default'] ? 'Y' : 'N',
                'SORT'   => $value['sort'] ?? $i * 10
            ];
        }

        return $obEnum->SetEnumValues($propId, $dbValues);
    }

    public static function setValues($entityTypeId, $entityId, $values)
    {
        global $USER_FIELD_MANAGER;

        $formattedValues = [];
        foreach ($values as $name => $value) {
            $name = strtoupper($name);
            if (!StringUtils::startsWith('UF_', $name)) {
                $name = "UF_$name";
            }

            $formattedValues[$name] = $value;
        }

        return $USER_FIELD_MANAGER->Update($entityTypeId, $entityId, $formattedValues);
    }

    public static function setSectionPropValues($iblockId, $sectionId, $propCode, $values)
    {
        return self::setSectionPropsValues($iblockId, $sectionId, [$propCode => $values]);    
    }

    public static function setSectionPropsValues($iblockId, $sectionId, $values)
    {
        $entityTypeId = "IBLOCK_$iblockId" . "_SECTION";

        return self::setValues($entityTypeId, $sectionId, $values);
    }
}