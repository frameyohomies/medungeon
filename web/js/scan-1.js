// Booking dialog: find a product (camera, hand scanner, typed barcode or "Buchen"
// on a card), choose a quantity, book it. Server validates everything again.

let aktuelleProduktId = null;
let aktuellerBestand = 0;
let aktuellerMindestbestand = 0;
let html5QrCode = null;
let buchungLaeuft = false;

document.addEventListener('DOMContentLoaded', function () {
  const barcodeInput = document.getElementById('barcodeInput');
  if (barcodeInput) {
    // hand scanners type the code and send Enter
    barcodeInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        barcodeLookup(barcodeInput.value.trim());
      }
    });
  }

  const deltaInput = document.getElementById('deltaInput');
  if (deltaInput) {
    deltaInput.addEventListener('input', aktualisiereVorschau);
    deltaInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        buchungAbsenden();
      }
    });
  }

  const scanModal = document.getElementById('scanModal');
  if (scanModal) {
    scanModal.addEventListener('shown.bs.modal', function () {
      if (document.getElementById('scanStep').style.display !== 'none') {
        document.getElementById('barcodeInput').focus();
      }
    });
    scanModal.addEventListener('hidden.bs.modal', resetScanModal);
  }
});

function barcodeLookup(code) {
  if (!code) {
    return;
  }
  produktLaden('/produkt/lookup?barcode=' + encodeURIComponent(code));
}

// "Buchen" on a product card / detail page: open the dialog with that product preselected
function buchungOeffnen(id) {
  const modalEl = document.getElementById('scanModal');
  bootstrap.Modal.getOrCreateInstance(modalEl).show();
  produktLaden('/produkt/lookup?id=' + encodeURIComponent(id));
}

function produktLaden(url) {
  fetch(url, { headers: { 'Accept': 'application/json' } })
    .then(response => response.json())
    .then(zeigeProdukt)
    .catch(() => zeigeProdukt({ success: false }));
}

function zeigeSchritt(id) {
  ['scanStep', 'produktStep', 'fehlerStep'].forEach(function (s) {
    document.getElementById(s).style.display = (s === id) ? 'block' : 'none';
  });
}

function zeigeProdukt(data) {
  stoppeKamera();

  if (!data.success) {
    zeigeSchritt('fehlerStep');
    return;
  }

  aktuelleProduktId = data.id;
  aktuellerBestand = parseInt(data.quantitaet, 10) || 0;
  aktuellerMindestbestand = parseInt(data.mindestbestand, 10) || 0;

  document.getElementById('produktName').innerText = data.name;
  document.getElementById('produktMeta').innerText =
    [data.standort, data.barcode].filter(Boolean).join(' · ');
  document.getElementById('produktQuantitaet').innerText = aktuellerBestand;
  document.getElementById('deltaInput').value = 0;

  const links = document.getElementById('produktCrudButtons');
  links.innerHTML = '';
  links.appendChild(link('/produkt/view?id=' + data.id, 'Details'));
  if (window.istAdmin) {
    links.appendChild(link('/produkt/update?id=' + data.id, 'Bearbeiten'));
  }

  zeigeSchritt('produktStep');
  aktualisiereVorschau();
  document.getElementById('deltaInput').focus();
  document.getElementById('deltaInput').select();
}

function link(href, text) {
  const a = document.createElement('a');
  a.href = href;
  a.className = 'booking__link';
  a.textContent = text;
  return a;
}

function aktuellesDelta() {
  return parseInt(document.getElementById('deltaInput').value, 10) || 0;
}

function mengeAnpassen(schritt) {
  mengeSetzen(aktuellesDelta() + schritt);
}

function mengeSetzen(wert) {
  // never allow taking more than is in stock
  document.getElementById('deltaInput').value = Math.max(wert, -aktuellerBestand);
  aktualisiereVorschau();
}

function aktualisiereVorschau() {
  const delta = aktuellesDelta();
  const danach = aktuellerBestand + delta;
  const btn = document.getElementById('buchenButton');
  const hinweis = document.getElementById('buchungHinweis');
  const danachEl = document.getElementById('bestandDanach');

  danachEl.innerText = danach;
  danachEl.classList.toggle('is-low', danach <= aktuellerMindestbestand);

  btn.classList.remove('booking__submit--in', 'booking__submit--out');
  hinweis.innerText = '';
  hinweis.className = 'booking__hint';

  if (delta === 0) {
    btn.disabled = true;
    btn.innerText = 'Menge wählen';
    return;
  }

  if (danach < 0) {
    btn.disabled = true;
    btn.innerText = 'Nicht genug Bestand';
    hinweis.innerText = 'Es sind nur ' + aktuellerBestand + ' Stück vorhanden.';
    hinweis.classList.add('booking__hint--error');
    return;
  }

  btn.disabled = buchungLaeuft;
  if (delta > 0) {
    btn.classList.add('booking__submit--in');
    btn.innerText = delta + ' zubuchen';
  } else {
    btn.classList.add('booking__submit--out');
    btn.innerText = Math.abs(delta) + ' entnehmen';
  }

  if (danach <= aktuellerMindestbestand) {
    hinweis.innerText = 'Danach unter Mindestbestand (' + aktuellerMindestbestand + ') – bitte nachbestellen.';
    hinweis.classList.add('booking__hint--warn');
  }
}

function buchungAbsenden() {
  const delta = aktuellesDelta();
  if (!aktuelleProduktId || delta === 0 || buchungLaeuft || aktuellerBestand + delta < 0) {
    return;
  }

  buchungLaeuft = true;
  const btn = document.getElementById('buchenButton');
  btn.disabled = true;
  btn.innerText = 'Wird gebucht…';

  fetch('/produkt/buchen?id=' + encodeURIComponent(aktuelleProduktId), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
    body: 'delta=' + encodeURIComponent(delta),
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
        return;
      }
      buchungLaeuft = false;
      if (typeof data.neuerBestand !== 'undefined') {
        aktuellerBestand = parseInt(data.neuerBestand, 10) || 0;
        document.getElementById('produktQuantitaet').innerText = aktuellerBestand;
      }
      aktualisiereVorschau();
      const hinweis = document.getElementById('buchungHinweis');
      hinweis.innerText = data.message || 'Buchung fehlgeschlagen.';
      hinweis.className = 'booking__hint booking__hint--error';
    })
    .catch(() => {
      buchungLaeuft = false;
      aktualisiereVorschau();
      const hinweis = document.getElementById('buchungHinweis');
      hinweis.innerText = 'Keine Verbindung – bitte erneut versuchen.';
      hinweis.className = 'booking__hint booking__hint--error';
    });
}

function startScan() {
  if (html5QrCode && html5QrCode.getState() === Html5QrcodeScannerState.SCANNING) {
    return;
  }

  html5QrCode = new Html5Qrcode('reader', {
    formatsToSupport: [
      Html5QrcodeSupportedFormats.QR_CODE,
      Html5QrcodeSupportedFormats.EAN_13,
      Html5QrcodeSupportedFormats.EAN_8,
      Html5QrcodeSupportedFormats.CODE_128,
      Html5QrcodeSupportedFormats.CODE_39,
      Html5QrcodeSupportedFormats.UPC_A,
      Html5QrcodeSupportedFormats.UPC_E,
      Html5QrcodeSupportedFormats.UPC_EAN_EXTENSION,
    ]
  });

  document.getElementById('reader').classList.add('is-active');

  html5QrCode.start(
    { facingMode: 'environment' },
    {
      fps: 20,
      qrbox: { width: 260, height: 130 },
      aspectRatio: 1.7777778,
      disableFlip: true,
      videoConstraints: {
        facingMode: 'environment',
        width: { ideal: 1920 },
        height: { ideal: 1080 },
      },
    },
    (decodedText) => {
      stoppeKamera();
      barcodeLookup(decodedText);
    }
  ).catch(err => {
    console.error('Kamera konnte nicht gestartet werden:', err);
    document.getElementById('reader').classList.remove('is-active');
    alert('Kamera konnte nicht gestartet werden. Bitte Berechtigung prüfen oder Barcode eintippen.');
  });
}

function stoppeKamera() {
  if (html5QrCode && html5QrCode.getState() === Html5QrcodeScannerState.SCANNING) {
    html5QrCode.stop().catch(() => {});
  }
  const reader = document.getElementById('reader');
  if (reader) {
    reader.classList.remove('is-active');
  }
}

function resetScanModal() {
  stoppeKamera();
  aktuelleProduktId = null;
  aktuellerBestand = 0;
  aktuellerMindestbestand = 0;
  buchungLaeuft = false;

  document.getElementById('barcodeInput').value = '';
  document.getElementById('produktName').innerText = '';
  document.getElementById('produktMeta').innerText = '';
  document.getElementById('produktQuantitaet').innerText = '0';
  document.getElementById('deltaInput').value = 0;
  document.getElementById('buchungHinweis').innerText = '';

  zeigeSchritt('scanStep');
}
