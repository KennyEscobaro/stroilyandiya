class GeoIp {
    #signedParameters = '';
    #componentName = '';
    #geoIPModal = undefined;

    #ids = {
        geoIPModal: 'geoIPModal',
        errorAlert: 'errorAlert',
        errorMessage: 'errorMessage',
        modalFieldIPAddress: 'ipAddress',
        modalFieldCity: 'city',
        modalFieldRegion: 'region',
        modalFieldCountry: 'country',
    }

    #classes = {
        geoIPModalClose: 'js-modal-close',
        getIPData: 'js-get-ip-data',
    }

    #selectors = {
        geoIPModal: document.getElementById(`${this.#ids.geoIPModal}`),
        errorAlert: document.getElementById(`${this.#ids.errorAlert}`),
        errorMessage: document.getElementById(`${this.#ids.errorMessage}`),
        modalFieldIPAddress: document.getElementById(`${this.#ids.modalFieldIPAddress}`),
        modalFieldCity: document.getElementById(`${this.#ids.modalFieldCity}`),
        modalFieldRegion: document.getElementById(`${this.#ids.modalFieldRegion}`),
        modalFieldCountry: document.getElementById(`${this.#ids.modalFieldCountry}`),
        geoIPModalClose: document.querySelectorAll(`.${this.#classes.geoIPModalClose}`),
        getIPData: document.querySelector(`.${this.#classes.getIPData}`),
    }

    #actions = {
        getIPData: 'getIPData',
    }

    constructor(signedParameters, componentName) {
        this.#signedParameters = signedParameters;
        this.#componentName = componentName;
        this.#geoIPModal = new bootstrap.Modal(this.#selectors.geoIPModal);

        this.#addEventHandler();
    }

    #addEventHandler() {
        this.#selectors.geoIPModalClose.forEach(element => {
            element.addEventListener('click', () => {
                this.#closeGeoIPModal();
            });
        });


        this.#selectors.getIPData.addEventListener('click', () => {
            this.#showGeoIPInfo();
        });
    }

    #closeGeoIPModal() {
        this.#geoIPModal.hide();
    }

    #showGeoIPInfo() {
        const ipInputValue = document.getElementById('ipInput').value;
        const selectors = this.#selectors;
        const geoIPModal = this.#geoIPModal;

        BX.ajax.runComponentAction(this.#componentName, this.#actions.getIPData, {
            mode: 'class',
            data: {ip: ipInputValue},
            signedParameters: this.#signedParameters
        }).then(function (response) {
            const data = response.data;

            selectors.modalFieldIPAddress.innerText = `IP-адрес: ${data.IP || 'N/A'}`;
            selectors.modalFieldCity.innerText = `Город: ${data.CITY || 'N/A'}`;
            selectors.modalFieldRegion.innerText = `Регион: ${data.REGION || 'N/A'}`;
            selectors.modalFieldCountry.innerText = `Страна: ${data.COUNTRY || 'N/A'}`;

            geoIPModal.show();
        }, function (response) {
            selectors.errorMessage.innerText = response.data.RESULT;
            selectors.errorAlert.classList.add('show');
        });
    }
}

BX.GeoIp = GeoIp;