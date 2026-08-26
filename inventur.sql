CREATE DATABASE IF NOT EXISTS inventur
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE inventur;

CREATE TABLE benutzer (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entra_oid VARCHAR(100) NOT NULL UNIQUE,
    firstname VARCHAR(150) NOT NULL,
    lastname VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL,
    rolle ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    aktiv TINYINT(1) NOT NULL DEFAULT 1,
    erstellt_am DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produkt (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    barcode VARCHAR(64) NULL UNIQUE,
    standort VARCHAR(150) NULL,
    quantitaet INT NOT NULL DEFAULT 0,
    mindestbestand INT NOT NULL DEFAULT 0,
    erstellt_am DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    aktualisiert_am DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                     ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE bestand_bewegung (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produkt_id INT UNSIGNED NOT NULL,
    benutzer_id INT UNSIGNED NOT NULL,
    delta INT NOT NULL,
    bestand_nach INT NOT NULL,
    gebucht_am DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (produkt_id) REFERENCES produkt(id),
    FOREIGN KEY (benutzer_id) REFERENCES benutzer(id),

    INDEX idx_produkt_zeit (produkt_id, gebucht_am),
    INDEX idx_benutzer_zeit (benutzer_id, gebucht_am)
);

INSERT INTO benutzer (entra_oid, firstname, lastname, email, rolle) VALUES
    ('test-oid-admin-001', 'Admin', 'Test', 'admin.test@medino.at', 'admin'),
    ('test-oid-user-001', 'Max', 'Mustermann', 'max.mustermann@medino.at', 'ordihilfe');

INSERT INTO produkt (name, barcode, standort, quantitaet, mindestbestand) VALUES
    ('Einmalhandschuhe (Box)', '4006381333931', 'Lager Regal A, Fach 1', 50, 10);

INSERT INTO bestand_bewegung (produkt_id, benutzer_id, delta, bestand_nach)
VALUES (1, 2, -5, 45);

UPDATE produkt SET quantitaet = 45 WHERE id = 1;
