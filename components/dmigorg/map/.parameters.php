<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
  die();
}
/** @var array $arCurrentValues */

if(!CModule::IncludeModule("iblock")) {
  return;
}
$arIBlocks=array();
$db_iblock = CIBlock::GetList();
while($arRes = $db_iblock->Fetch()) {
  $arIBlocks[$arRes["ID"]] = "[" . $arRes["ID"] . "] " . $arRes["NAME"];
}

$arComponentParameters = array(
  "GROUPS" => array(
    "SETTINGS" => array(
      "NAME" => GetMessage("IBLOCK_GROUP_SETTINGS"),
      "SORT" => "1"
    ),
  ),
  "PARAMETERS" => array(
    "IBLOCK_ID" => array(
      "PARENT" => "SETTINGS",
      "NAME" => GetMessage("IBLOCK_PARAMETERS_IBLOCKID"),
      "TYPE" => "LIST",
      "VALUES" => $arIBlocks,
      "DEFAULT" => ''
    ),
    "CACHE_TIME"  =>  array("DEFAULT"=>36000000),
  )
);

