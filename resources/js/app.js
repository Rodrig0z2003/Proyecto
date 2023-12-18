import './bootstrap';
import { Popover } from 'bootstrap';

const cacheFetchUrls = {};
const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    const fetchUrl = popoverTriggerEl.getAttribute('data-bs-fetchurl');

    if (fetchUrl) {
        const popover = new Popover(popoverTriggerEl, {
            html: true,
            boundary:'window',
            content: function () {
                return '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            }
        })

        popoverTriggerEl.addEventListener('shown.bs.popover', function () {
            fetchPopoverUrl(fetchUrl, popover);
        });

        return;
    }

    return new Popover(popoverTriggerEl)
})

function fetchPopoverUrl(url, popover) {
    if (cacheFetchUrls[url]) {
        return;
    }

    fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
        .then(response => response.json())
        .then(data => {
            setPopoverWeatherData(popover, data);
            cacheFetchUrls[url] = data;
        });
}

function setPopoverWeatherData(popover, data) {
    popover._config.content = `<div class="card" style="width: 18rem;">
        <div class="card-body">
            <h5 class="card-title">${data.name}</h5>
            <p class="card-text">Temperatura: ${data.main.temp}°C</p>
            <p class="card-text">Se siente como: ${data.main.feels_like}°C</p>
            <p class="card-text">Humedad: ${data.main.humidity}%</p>
            <p class="card-text">Viento: ${data.wind.speed} m/s</p>
        </div>
    </div>`;
    popover.setContent();
}
