# od.entity
Модуль для CMS 1c-Битрикс, содержащий в себе классы-обертки над классами сущностей битрикса (CIBlockElement, CSaleOrder и т.п.).
Модуль упрощающает жизнь разработчикам, избавляя их от постоянного дублирования кода при решении повседневных задач.

Пример 1. Самая распространенная задача - получить элемент инфоблока по его символьному коду или ID:

привычный код:
$dbRes = CIBlockElement::GetList([], ['CODE' => 'element_code']);
$elem = $dbRes->Fetch();

код с использованием модуля:
$elem = IBElement::find('element_code');

Пример 2. Создать раздел инфоблока с кодом 'section_code' или обновить, если он уже существует:

привычный код:
$fields = ['CODE' => 'section_code', 'NAME' => '...', 'SECTION_ID' => '...', ...];
$dbRes = CIBlockSection::GetList([], ['CODE' => 'section_code']);
if ($section = $dbRes->Fetch()) {
    CIBlockSection::Update($section['ID'], $fields);
} else {
    CIBlockSection::Add($fields);
}

код с использованием модуля:
$fields = ['CODE' => 'section_code', 'NAME' => '...', 'SECTION_ID' => '...', ...];
IBSection::addOrUpdate('section_code', $fields);