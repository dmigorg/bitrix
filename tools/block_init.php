<?php

/** @global CMain $APPLICATION */

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');
require_once('CSetup.php');
CModule::IncludeModule('workflow');

if (!CModule::IncludeModule('iblock')) {
  error('Модуль "iblock" не найден');
}
if (empty($siteID = getSiteID())) {
  error('Не удалось определить ID сайта');
}
global $USER;
$userId = $USER->GetID();
if (empty($userId)) {
  error('Не удалось определить ID пользователя');
}

$params = new Params;
$params->siteID = $siteID;
$params->userId = $userId;
$params->iblockType = 'dmigorg_directories'; //Тип информационного блока
$params->iblockTypeName = 'Справочники'; //Название типа информационного блока
$params->iblockName = 'Организации'; //Название информационного блока

try {
  $CSetup = new CSetup($params);

  info('Создание типа инфоблока ' . $params->iblockTypeName);
  $CSetup->AddCIBlockType();

  info('Создание инфоблока ' . $params->iblockName);
  $CSetup->AddCIBlock();

  $arProperties = [
    ['NAME' => 'Телефон', 'SORT' => '1', 'CODE' => 'PHONE', 'TYPE' => 'S'],
    ['NAME' => 'Email', 'SORT' => '2', 'CODE' => 'EMAIL', 'TYPE' => 'S'],
    ['NAME' => 'Город', 'SORT' => '3', 'CODE' => 'CITY', 'TYPE' => 'S'],
    ['NAME' => 'Координаты', 'SORT' => '4', 'CODE' => 'MAP', 'TYPE' => 'S', 'USER_TYPE' => 'map_yandex']
  ];

  foreach ($arProperties as $arProp) {
    info('Добавление свойства элемента '. $arProp['NAME']);
    $CSetup->AddCIBlockProperty($arProp);
  }

  $numRows = $CSetup->loadDataFromCsv('sample.csv');
  for ($i = 0; $i < $numRows; $i++) {
    info("Добавление в инфоблок строки #$i");
    [$name, $arrPropVals] = $CSetup->MapColumns($i);
    $CSetup->insertData($name, $arrPropVals);
  }
} catch (Bitrix\Main\SystemException $e) {
  error($e->getMessage());
}

/**
 * @param string $value
 * @return void
 */
function info(string $value)
{
  echo "<p>$value</p>";
}

/**
 * @param string $value
 * @return void
 */
function error(string $value)
{
  echo "<p style=\"color:red\">Ошибка: $value</p>";
  exit;
}

/**
 * @return string
 */
function getSiteID(): string
{
  if (defined('SITE_ID')) {
    switch (SITE_ID) {
      case 'en':
      case 'ru':
      case 'ua':
        return getSiteIDByLang(SITE_ID);
      default:
        return SITE_ID;
    }
  }

  return '';
}

/**
 * @param $sSiteLang
 * @return string
 */
function getSiteIDByLang($sSiteLang): string
{
  try {
    $rsSites = Bitrix\Main\SiteTable::getList(
      array(
        'filter' => array(
          '=LANGUAGE_ID' => $sSiteLang
        )
      )
    );
  } catch (Exception $e) {
    return '';
  }

  if ($arSite = $rsSites->fetch()) {
    return $arSite['LID'];
  }

  return '';
}
