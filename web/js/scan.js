let aktuelleProduktId = null;

document.addEventListener('DOMContentLoaded', function () {
  const barcodeInput = document.getElementById('barcodeInput');
  if (barcodeInput) {
    barcodeInput.addEventListener('keyup', function (e) {
      if (e.key === 'Enter') {
        barcodeLookup(barcodeInput.value);
      }
    });
  }


});

function barcodeLookup(code) {
  fetch('/produkt/lookup?barcode=' + encodeURIComponent(code))
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        aktuelleProduktId = data.id;
        document.getElementById('produktName').innerText = data.name;
        document.getElementById('produktQuantitaet').innerText = data.quantitaet;
        document.getElementById('deltaInput').value = 0;

        document.getElementById('scanStep').style.display = 'none';
        document.getElementById('fehlerStep').style.display = 'none';
        document.getElementById('produktStep').style.display = 'block';
      } else {
        document.getElementById('scanStep').style.display = 'none';
        document.getElementById('fehlerStep').style.display = 'block';
      }
    });
}

function mengeAnpassen(richtung) {
  const feld = document.getElementById('deltaInput');
  feld.value = parseInt(feld.value || 0) + richtung;
}

function buchungAbsenden() {
  const delta = document.getElementById('deltaInput').value;

  fetch('/produkt/buchen?id=' + aktuelleProduktId, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
    body: 'delta=' + delta,
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Buchung fehlgeschlagen.');
      }
    });
}

let html5QrCode = null;

function startScan() {
  const formatsToSupport = [
    Html5QrcodeSupportedFormats.QR_CODE,
    Html5QrcodeSupportedFormats.EAN_13,
    Html5QrcodeSupportedFormats.EAN_8,
    Html5QrcodeSupportedFormats.CODE_128,
    Html5QrcodeSupportedFormats.CODE_39,
    Html5QrcodeSupportedFormats.UPC_A,
    Html5QrcodeSupportedFormats.UPC_E,
    Html5QrcodeSupportedFormats.UPC_EAN_EXTENSION,
  ];

  html5QrCode = new Html5Qrcode("reader");
  html5QrCode.start(
    {facingMode: "environment"},
    {
      fps: 20,
      qrbox: {width: 300, height: 150},
      aspectRatio: 1.7777778,
      videoConstraints: {
        facingMode: "environment",
        width: {ideal: 1920},
        height: {ideal: 1080},
      },
    },
    (decodedText) => {
      html5QrCode.stop();
      barcodeLookup(decodedText);
    }
  ).catch(err => {
    console.error("Kamera konnte nicht gestartet werden:", err);
  });
}
document.addEventListener('DOMContentLoaded', function () {
  const scanModal = document.getElementById('scanModal');
  if (scanModal) {
    scanModal.addEventListener('hidden.bs.modal', resetScanModal);
  }

  // dein bestehender barcodeInput-Listener bleibt hier
});

function resetScanModal() {
  aktuelleProduktId = null;

  document.getElementById('barcodeInput').value = '';
  document.getElementById('produktName').innerText = '';
  document.getElementById('produktQuantitaet').innerText = '';
  document.getElementById('deltaInput').value = 0;

  document.getElementById('scanStep').style.display = 'block';
  document.getElementById('produktStep').style.display = 'none';
  document.getElementById('fehlerStep').style.display = 'none';

  if (html5QrCode) {
    html5QrCode.stop().catch(() => {});
  }
}
