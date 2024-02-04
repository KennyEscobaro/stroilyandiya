<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arResult
 */
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Введите IP-адрес" id="ipInput">
                <button class="btn btn-primary js-get-ip-data">Получить информацию</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="geoIPModal" tabindex="-1" aria-labelledby="geoIPModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="geoIPModalLabel">Информация о IP-адресе</h5>
                <button type="button" class="btn-close js-modal-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <ul id="geoIPInfoList">
                    <li id="ipAddress">IP-адрес:</li>
                    <li id="city">Город:</li>
                    <li id="region">Регион:</li>
                    <li id="country">Страна:</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary js-modal-close" data-bs-dismiss="modal">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>
<div class="alert alert-danger alert-dismissible fade" role="alert" id="errorAlert">
    <strong>Ошибка!</strong>
    <span id="errorMessage"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<script type="text/javascript">
    BX.ready(function () {
        new BX.GeoIp(
            '<?= $this->getComponent()->getSignedParameters() ?>',
            '<?= $this->getComponent()->getName() ?>',
        );
    });
</script>