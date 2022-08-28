<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arPlacemarks = $arResult["PLACEMARKS"];
$APPLICATION->IncludeComponent(
  "bitrix:map.yandex.view",
  "",
  Array(
    "INIT_MAP_TYPE" => "MAP",
    "MAP_DATA" => serialize(array(
      'yandex_lat' => $arPlacemarks[0]["LAT"],
      'yandex_lon' => $arPlacemarks[0]["LON"],
      'yandex_scale' => 10,
      'PLACEMARKS' => $arPlacemarks
    )),
    "MAP_WIDTH" => "350",
    "MAP_HEIGHT" => "350",
    "CONTROLS" => array("ZOOM", "TYPECONTROL", "SCALELINE"),
    "OPTIONS" => array("DESABLE_SCROLL_ZOOM", "ENABLE_DBLCLICK_ZOOM", "ENABLE_DRAGGING"),
    "MAP_ID" => ""
  ),
  false
);



