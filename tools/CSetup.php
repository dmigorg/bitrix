<?php

use Bitrix\Main\SystemException;

/**
 * Class CSetup
 */
class CSetup
{
  private Params $params;
  private string $iblockId;
  private array $arPropID;
  private array $data;
  private CIBlockElement $el;

  public function __construct(Params $params)
  {
    $this->params = $params;
    $this->el = new CIBlockElement;
    $this->el->CancelWFSetMove();
  }

  /**
   * @return bool
   * @throws SystemException
   */
  public function AddCIBlockType(): bool
  {
    $obIBlockType =  new CIBlockType;
    CIBlockType::Delete($this->params->iblockType); // Удаление всех информационных блоков указанного типа
    $arFields = [
      'ID' => $this->params->iblockType,
      'SECTIONS' => 'Y',
      'LANG' => [
        'ru' => [
          'NAME' => $this->params->iblockTypeName,
        ]
      ]
    ];
    $obIBlockTypeId = $obIBlockType->Add($arFields);

    if ($obIBlockTypeId === false) {
      throw new SystemException($obIBlockType->LAST_ERROR);
    }
    return true;
  }

  /**
   * @return bool
   * @throws SystemException
   */
  public function AddCIBlock(): bool
  {
    $obIblock = new CIBlock;
    $arFields = [
      'NAME' => $this->params->iblockName,
      'ACTIVE' => 'Y',
      'IBLOCK_TYPE_ID' => $this->params->iblockType,
      'SITE_ID' => $this->params->siteID
    ];
    $iblockId = $obIblock->Add($arFields);

    if ($iblockId === false) {
      throw new SystemException($obIblock->LAST_ERROR);
    }
    $this->iblockId = $iblockId;
    return true;
  }

  /**
   * @param array $arProp
   * @return bool
   * @throws SystemException
   */
  public function AddCIBlockProperty(array $arProp): bool
  {
    $arFields = [
      'NAME' => $arProp['NAME'],
      'ACTIVE' => 'Y',
      'SORT' => $arProp['SORT'],
      'CODE' => $arProp['CODE'],
      'PROPERTY_TYPE' => $arProp['TYPE'],
      'USER_TYPE' => $arProp['USER_TYPE'] ?? '',
      'IS_REQUIRED' => 'Y',
      'IBLOCK_ID' => $this->iblockId
    ];
    $ibp = new CIBlockProperty;
    $propID = $ibp->Add($arFields);

    if ($propID === false) {
      throw new SystemException($ibp->LAST_ERROR);
    }
    $this->arPropID[(string)$propID] = $arProp['CODE'];
    return true;
  }

  /**
   * @param $filename
   * @return int
   * @throws SystemException
   */
  public function loadDataFromCsv($filename): int
  {
    $csvFile = new CCSVData('R'); // Создаём объект – экземпляр класса CCSVData
    $csvFile->LoadFile($filename); // Указываем методу LoadFile путь до CSV файла
    $csvFile->SetDelimiter(); // Устанавливаем разделитель для CSV файла

    $header = $csvFile->Fetch();
    $data = [];
    while ($row = $csvFile->Fetch()) {
      $arr = [];
      foreach ($header as $key => $val) {
        $arr[$val] = $row[$key];
      }
      $data[] = $arr;
    }
    $this->data = $data;
    $total = count($this->data);
    if ($total === 0) {
      throw new SystemException('Нет данных для загрузки');
    }
    return $total;
  }

  /**
   * @param int $index
   * @return array
   * @throws SystemException
   */
  public function MapColumns(int $index): array
  {
    $row = $this->data[$index];
    $name = array_shift($row); //Название
    $arPropVals = []; //Значения свойств
    foreach ($row as $key => $val) {
      $propID = array_search($key, $this->arPropID, true);
      if ($propID === false) {
        throw new SystemException('Не найдено свойство поля $key');
      }
      $arPropVals[$propID] = $val;
    }
    return [$name, $arPropVals];
  }

  /**
   * @param string $name
   * @param array $arPropVals
   * @return bool
   * @throws SystemException
   */
  public function insertData(string $name, array $arPropVals): bool
  {
    $arLoadArray = [
      'MODIFIED_BY' => $this->params->userId,
      'IBLOCK_ID' => $this->iblockId,
      'TMP_ID' => md5(uniqid('', true)),
      'NAME' => $name, //Название
      'PROPERTY_VALUES' => $arPropVals, //Значения свойств
      'ACTIVE' => 'Y'
    ];
    $elID = $this->el->Add($arLoadArray);

    if ($elID === false) {
      throw new SystemException($this->el->LAST_ERROR);
    }
    return true;
  }
}

/**
 * Class Params
 */
class Params
{
  /**
   * @var string
   */
  public string $siteID;
  /**
   * @var string
   */
  public string $userId;
  /**
   * @var string
   */
  public string $iblockType;
  /**
   * @var string
   */
  public string $iblockTypeName;
  /**
   * @var string
   */
  public string $iblockName;
}
