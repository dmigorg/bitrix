<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
  die();
}

$arComponentDescription = array(
  "NAME" => GetMessage("IBLOCK_NAME"),
  "DESCRIPTION" => GetMessage("IBLOCK_DESC"),
  "ICON" => "",
  "PATH" => array(
    "ID" => "dmigorg",
    "NAME" => GetMessage("IBLOCK_PATH_NAME"),
  ),
  "CACHE_PATH" => "Y",
  "COMPLEX" => "Y"
);
