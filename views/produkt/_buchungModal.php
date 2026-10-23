<?php

/**
 * Booking dialog (scan or pick a product, then book +/-).
 * Used on the product list and on the product detail page.
 *
 * @var yii\web\View $this
 */
?>
<div class="modal fade" id="scanModal" tabindex="-1" aria-labelledby="scanModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content booking">

            <div class="booking__head">
                <h5 class="booking__title" id="scanModalTitle">Buchen</h5>
                <button type="button" class="booking__close" data-bs-dismiss="modal" aria-label="Schließen">&times;</button>
            </div>

            <!-- step 1: find product -->
            <div id="scanStep" class="booking__body">
                <div id="reader" class="booking__reader"></div>

                <button type="button" class="booking__camera" onclick="startScan()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M15 12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1.172a3 3 0 0 0 2.12-.879l.83-.828A1 1 0 0 1 6.827 3h2.344a1 1 0 0 1 .707.293l.828.828A3 3 0 0 0 12.828 5H14a1 1 0 0 1 1 1zM2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4z"/><path d="M8 11a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5m0 1a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg>
                    Mit Kamera scannen
                </button>

                <div class="booking__divider"><span>oder</span></div>

                <div class="d-flex gap-2">
                    <input type="text" id="barcodeInput" class="form-control booking__input"
                           placeholder="Barcode eingeben oder Handscanner" autocomplete="off" inputmode="numeric">
                    <button type="button" id="searchButton" class="btn btn-primary"
                            onclick="barcodeLookup(document.getElementById('barcodeInput').value)">Suchen</button>
                </div>
            </div>

            <!-- step 2: book -->
            <div id="produktStep" class="booking__body" style="display:none;">
                <div class="booking__product">
                    <div class="booking__product-name" id="produktName"></div>
                    <div class="booking__product-meta" id="produktMeta"></div>
                </div>

                <div class="booking__stock">
                    <div>
                        <div class="booking__stock-label">Aktuell</div>
                        <div class="booking__stock-value" id="produktQuantitaet">0</div>
                    </div>
                    <div class="booking__arrow" aria-hidden="true">&rarr;</div>
                    <div>
                        <div class="booking__stock-label">Danach</div>
                        <div class="booking__stock-value" id="bestandDanach">0</div>
                    </div>
                </div>

                <div class="stepper">
                    <button type="button" class="stepper__btn" onclick="mengeAnpassen(-1)" aria-label="Eins weniger">&minus;</button>
                    <input type="number" id="deltaInput" class="stepper__input" value="0" inputmode="numeric" aria-label="Menge">
                    <button type="button" class="stepper__btn" onclick="mengeAnpassen(1)" aria-label="Eins mehr">+</button>
                </div>

                <div class="quick" role="group" aria-label="Schnellauswahl">
                    <button type="button" class="quick__btn quick__btn--out" onclick="mengeAnpassen(-10)">&minus;10</button>
                    <button type="button" class="quick__btn quick__btn--out" onclick="mengeAnpassen(-5)">&minus;5</button>
                    <button type="button" class="quick__btn" onclick="mengeSetzen(0)">0</button>
                    <button type="button" class="quick__btn quick__btn--in" onclick="mengeAnpassen(5)">+5</button>
                    <button type="button" class="quick__btn quick__btn--in" onclick="mengeAnpassen(10)">+10</button>
                </div>

                <div class="booking__hint" id="buchungHinweis" role="status"></div>

                <button type="button" class="booking__submit" id="buchenButton" onclick="buchungAbsenden()" disabled>
                    Menge wählen
                </button>

                <div id="produktCrudButtons" class="booking__links"></div>
            </div>

            <!-- not found -->
            <div id="fehlerStep" class="booking__body text-center" style="display:none;">
                <div class="booking__notfound-icon" aria-hidden="true">?</div>
                <p class="mb-3">Kein aktives Produkt mit diesem Barcode gefunden.</p>
                <button type="button" class="btn btn-ghost-light" onclick="resetScanModal()">Erneut versuchen</button>
            </div>

        </div>
    </div>
</div>
