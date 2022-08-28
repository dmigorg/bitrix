<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $USER;
$userId = $USER->GetID();
if(empty($userId)) {
  echo 'Необходимо авторизоваться в системе';
  exit;
}

const BR = '<br/>';
if(!CModule::IncludeModule("iblock")) {
  exit;
}

$iblockType = "directories";
$iblockTypeName = "Справочники";
$iblockName = "Организации";

$obIBlockType =  new CIBlockType;
$arFields = Array(
  "ID"=>$iblockType,
  "SECTIONS"=>"Y",
  "LANG" =>Array(
    "ru"=>Array(
      "NAME"=> $iblockTypeName,
    )
  )
);
echo "Создание типа инфоблока $iblockTypeName".BR;
$res = $obIBlockType->Add($arFields);

if(!$res){
  echo "Ошибка: ", $obIBlockType->LAST_ERROR;
  exit;
}

$obIblock = new CIBlock;
$arFields = Array(
  "NAME"=> $iblockName,
  "ACTIVE" => "Y",
  "IBLOCK_TYPE_ID" => $iblockType,
  "SITE_ID" => SITE_ID
);
echo "Создание инфоблока $iblockName".BR;
$iblockId = $obIblock->Add($arFields);

$arProperty[] = ['NAME' => 'Телефон', 'SORT' => '1', 'CODE' => 'PHONE', 'TYPE' => 'S'];
$arProperty[] = ['NAME' => 'Email', 'SORT' => '2', 'CODE' => 'EMAIL', 'TYPE' => 'S'];
$arProperty[] = ['NAME' => 'Город', 'SORT' => '3', 'CODE' => 'CITY', 'TYPE' => 'S'];
$arProperty[] = ['NAME' => 'Координаты', 'SORT' => '4', 'CODE' => 'MAP', 'TYPE' => 'S', 'USER_TYPE' => 'map_yandex'];

$arPropID = [];
foreach($arProperty as $prop) {
  $arFields = Array(
    "NAME" => $prop['NAME'],
    "ACTIVE" => 'Y',
    "SORT" => $prop['SORT'],
    "CODE" => $prop['CODE'],
    "PROPERTY_TYPE" => $prop['TYPE'],
    'USER_TYPE' => $prop['USER_TYPE']??'',
    'IS_REQUIRED' => 'Y',
    "IBLOCK_ID" => $iblockId
  );
  $ibp = new CIBlockProperty;
  echo "Добавление свойства элемента ${prop['NAME']}".BR;
  $PropID = $ibp->Add($arFields);
  $arPropID[$prop['CODE']] = $PropID;
}

// Prepare arrays for elements load
$bWorkFlow = CModule::IncludeModule('workflow');
$el = new CIBlockElement;
$el->CancelWFSetMove();
$tmpid = md5(uniqid(""));
$arIBlockProperty = array();

$filePath = 'sample.csv';
$csvFile = new CCSVData('R'); // Создаём объект – экземпляр класса CCSVData
$csvFile->LoadFile($filePath); // Указываем методу LoadFile путь до CSV файла
$csvFile->SetDelimiter(';'); // Устанавливаем разделитель для CSV файла

$header = $csvFile->Fetch();
//удаляем первый элемент "Название офиса"
array_shift($header);
// Сравниваем имена полей с первой строкой
if(!empty(array_diff(array_values($header), array_keys($arPropID)))) {
  echo "Ошибка: Не правильный порядок полей (NAME;PHONE;EMAIL;CITY;MAP)";
  exit;
}

$i = 0;
while($row = $csvFile->Fetch()){
  $arLoadProductArray = [
    "MODIFIED_BY" => $userId,
    "IBLOCK_ID" => $iblockId,
    "TMP_ID" => $tmpid,
    "NAME" => $row[0],
    "PROPERTY_VALUES" => [
      $arPropID['PHONE'] => $row[1],
      $arPropID['EMAIL'] => $row[2],
      $arPropID['CITY'] => $row[3],
      $arPropID['MAP'] => $row[4]
    ],
    "ACTIVE" => "Y"
  ];

  echo 'Добавление в инфоблок строки #'.++$i.BR;
  $PRODUCT_ID = $el->Add($arLoadProductArray, $bWorkFlow, true, false);
  $res = $PRODUCT_ID > 0;
  if (!($res))
  {
    echo "Ошибка: ", $el->LAST_ERROR;
    exit;
  }
}

