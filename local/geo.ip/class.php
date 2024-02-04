<?php

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\SystemException;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Web\HttpClient;

class GeoIPComponent extends CBitrixComponent implements Controllerable, Errorable
{
    /** @var ErrorCollection $errorCollection */
    private ErrorCollection $errorCollection;

    private const SYPEX_GEO_REQUEST_URL = 'http://api.sypexgeo.net/';

    /**
     * @param CBitrixComponent|null $component
     *
     * @throws LoaderException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        if (!Loader::includeModule('highloadblock')) {
            ShowError('Не подключен модуль highloadblock');
        }

        parent::__construct($component);
    }

    /**
     * @param $arParams
     *
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        $arParams['HLBLOCK_ID'] = (int)($arParams['HLBLOCK_ID'] ?? 0);

        $this->errorCollection = new ErrorCollection();

        return $arParams;
    }

    /**
     * @return void
     */
    public function executeComponent(): void
    {
        if (!$this->arParams['HLBLOCK_ID']) {
            ShowError('Не указан идентификатор hl-блока');
            return;
        }

        $this->includeComponentTemplate();
    }

    /**
     * @param string $ip
     *
     * @return array|string[]
     */
    public function getIPDataAction(string $ip): array
    {
        try {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                throw new ArgumentException('Некорректный IP адрес');
            }

            $ipData = $this->getIPDataFromHighload($ip);

            if (!$ipData) {
                $ipData = $this->getIPDataFromSypexGeo($ip);
            }

            if (!$ipData) {
                throw new ArgumentException('Ошибка при получении данных об IP-адресе');
            }

            return $ipData;
        } catch (Throwable $e) {
            $this->errorCollection->add([new Error($e->getMessage())]);
            return ['RESULT' => 'Ошибка при получении данных об IP-адресе'];
        }
    }

    /**
     * Метод возвращает информацию об ип из сервиса Sypex Geo
     *
     * @param string $ip
     *
     * @return array
     */
    private function getIPDataFromSypexGeo(string $ip): array
    {
        $ipData = $this->getSypexGeoJsonResponse($ip);
        $this->addIPDataFromHighload([
            'UF_IP' => $ipData['ip'],
            'UF_CITY' => $ipData['city']['name_ru'],
            'UF_REGION' => $ipData['region']['name_ru'],
            'UF_COUNTRY' => $ipData['country']['name_ru'],
        ]);

        return [
            'IP' => $ipData['ip'],
            'CITY' => $ipData['city']['name_ru'],
            'REGION' => $ipData['region']['name_ru'],
            'COUNTRY' => $ipData['country']['name_ru'],
        ];
    }

    /**
     * Метод добавляет данные об ип в hl-блок
     *
     * @param array $ipData
     *
     * @return AddResult
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function addIPDataFromHighload(array $ipData): AddResult
    {
        $hlblock = HighloadBlockTable::getById($this->arParams['HLBLOCK_ID'])->fetch();
        $hlblockEntity = HighloadBlockTable::compileEntity($hlblock);
        $hlblockEntityClass = $hlblockEntity->getDataClass();

        return $hlblockEntityClass::add($ipData);
    }

    /**
     * Метод возвращает информацию об ип из hl-блока
     *
     * @param string $ip
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getIPDataFromHighload(string $ip): array
    {
        $hlblock = HighloadBlockTable::getById($this->arParams['HLBLOCK_ID'])->fetch();
        $hlblockEntity = HighloadBlockTable::compileEntity($hlblock);
        $hlblockEntityClass = $hlblockEntity->getDataClass();

        $ipData = $hlblockEntityClass::getList(['filter' => ['UF_IP' => $ip]])->fetch();

        if (!$ipData) {
            return [];
        }

        return [
            'IP' => $ipData['UF_IP'],
            'CITY' => $ipData['UF_CITY'],
            'REGION' => $ipData['UF_REGION'],
            'COUNTRY' => $ipData['UF_COUNTRY'],
        ];
    }

    /**
     * Метод возвращает результат GET запроса
     *
     * @param string $ip
     *
     * @return array
     */
    private function getSypexGeoJsonResponse(string $ip): array
    {
        $httpClient = new HttpClient();
        $urlRequest = self::SYPEX_GEO_REQUEST_URL . "json/$ip";
        $jsonResponse = $httpClient->get($urlRequest);

        return (array)json_decode($jsonResponse, true);
    }

    /**
     * @return string[]
     */
    protected function listKeysSignedParameters(): array
    {
        return
            [
                'HLBLOCK_ID',
            ];
    }

    /**
     * @return array[]
     */
    public function configureActions(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    /**
     * @inheritdoc
     */
    public function getErrorByCode($code): Error
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    /**
     * Метод возвращает hl-блоки для параметров компонента
     *
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getHlblocks(): array
    {
        Loader::includeModule('highloadblock');

        $highloadblockQuery = HighloadBlockTable::getList([
            'filter' => ['LANG.LID' => LANGUAGE_ID],
            'select' => ['ID', 'LANG_NAME' => 'LANG.NAME'],
        ]);
        $highloadblocks = [];

        while ($highloadblock = $highloadblockQuery->fetch()) {
            $highloadblocks[$highloadblock['ID']] = "{$highloadblock['LANG_NAME']} [{$highloadblock['ID']}]";
        }

        return $highloadblocks;
    }
}