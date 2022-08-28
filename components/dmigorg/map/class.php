<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
  die();
}

//Необходимо для корректного поиска класса
CBitrixComponent::includeComponentClass("dmigorg:map");

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
Loc::loadMessages(__FILE__);
Loader::includeModule("iblock");

class CMap extends CBitrixComponent
{
  private array $arItems;

  public function executeComponent()
  {
    if($this->startResultCache())//startResultCache используется не для кеширования html, а для кеширования arResult
    {
      $this->getIBlockData();
      $this->arResult["PLACEMARKS"] = $this->formatPlacemarks();
      $this->includeComponentTemplate();
    }
    return $this->arResult["PLACEMARKS"];
  }

  private function getIBlockData()
  {
    $data = [];
    $iterator = CIBlockElement::GetList(array('SORT'=>'ASC'), array("IBLOCK_ID" => $this->arParams["IBLOCK_ID"]));
    while ($obj = $iterator->GetNextElement())
    {
      $fields = $obj->GetFields();
      $properties = $obj->GetProperties();
      [$mapLAT, $mapLON] = explode(',', $properties["MAP"]['VALUE']);
      $data[] = [
        'NAME' => [Loc::getMessage('FIELD_NAME'), $fields['NAME']],
        'PHONE' => [$properties['PHONE']['NAME'], $properties["PHONE"]['VALUE']],
        'EMAIL' => [$properties['EMAIL']['NAME'], $properties["EMAIL"]['VALUE']],
        'CITY' => [$properties['CITY']['NAME'], $properties["CITY"]['VALUE']],
        "mapLAT" => $mapLAT,
        "mapLON" => $mapLON
      ];
    }
    $this->arItems = $data;
  }

  private function formatPlacemarks() : array
  {
    $arPlacemarks = [];
    foreach($this->arItems as $arItem) {
      $arDISPLAYPROPERTIES[] = implode(':', $arItem['NAME']);
      $arDISPLAYPROPERTIES[] = implode(':', $arItem['PHONE']);
      $arDISPLAYPROPERTIES[] = implode(':', $arItem['EMAIL']);
      $arDISPLAYPROPERTIES[] = implode(':', $arItem['CITY']);

      $arPlacemarks[] = [
        "LAT" => $arItem['mapLAT'],
        "LON" => $arItem['mapLON'],
        "TEXT" => implode('</br>', $arDISPLAYPROPERTIES)
      ];
      unset($arDISPLAYPROPERTIES);
    }
    return $arPlacemarks;
  }
}
