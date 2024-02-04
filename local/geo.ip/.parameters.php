<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

CBitrixComponent::includeComponentClass('local:geo.ip');

$arComponentParameters =
    [
        'PARAMETERS' =>
            [
                'HLBLOCK_ID' => [
                    'PARENT' => 'BASE',
                    'NAME' => 'Идентификатор hl-блока',
                    'TYPE' => 'LIST',
                    'VALUES' => GeoIPComponent::getHlblocks(),
                ],
            ],
    ];