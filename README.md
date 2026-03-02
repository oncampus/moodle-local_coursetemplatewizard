# Local Plugin coursetemplatewizard (Vorlagenkurs-Assistent)

**Local coursetemplatewizard** ist ein **lokales Plugin** für Moodle, welches die Nutzung und Verwaltung von Kursvorlagen vereinfacht. 
Kursersteller*innen und Lehrkräfte können damit Kursvorlagen auswählen und in ihren eigenen Kursbereich kopieren, um schneller standardisierte Kurse zu erstellen.

## Features

- **Kursvorlagenverwaltung**: Verwalten von Vorlagenkursen in einem konfigurierten Kursbereich.
- **Kursüberschreibung**: Überschreiben von Kursen durch Kursvorlagen in beliebigen Kategorien.
- **Rollen- und Zugriffssteuerung**: Berechtigungen für Manager und Kursersteller*in.

## Installation

1. Kopiere das Plugin in das Verzeichnis:  
   ```bash
   /local/coursetemplatewizard
   ```

2. Starte die Installation über:  
   **Website-Administration → Mitteilungen**  
   oder führe den CLI-Upgrade aus:  
   ```bash
   php admin/cli/upgrade.php
   ```

### Voraussetzungen
- Moodle-Version: `2020061511` oder höher.

## Konfiguration

Nach der Installation ist das Plugin über folgende Seite konfigurierbar:  
**Website-Administration → Plugins → Lokale Plugins → Vorlagenkurs-Assistent**

Wichtige Einstellungen:
- **Vorlagen-Kategorie**: Standard-Kategorie für Kursvorlagen.
- **Service-Nutzer**: Nutzer (per ID), unter dessen Kontext die Backup/Restore-Prozesse laufen. Bei Installation des Plugins wird ein solcher Service-Nutzer bereits angelegt und hier hinterlegt.
- **Vorlagenziel-Ausnahmen**: Kurse (Angabe per kommagetrennter IDs), die nicht von den Vorlagenkursen überschrieben werden dürfen.

## Nutzung

- Lehrkräfte und Kursersteller*innen sehen verfügbare Vorlagenkurse und können diese mit wenigen Klicks zum Überschreiben von bestehenden Kursen nutzen.

## Rechte

Dieses Plugin definiert folgende Rechte:

| Name des Rechts                                                | Beschreibung                                                                | Standardrolle             |
|----------------------------------------------------------------|-----------------------------------------------------------------------------|---------------------------|
| `local/coursetemplatewizard:use`                                | Recht zum Nutzen der Vorlagenkurse zum Überschreiben von bestehenden Kursen | Manager, Kursersteller*in |

## Lizenz

Dieses Plugin ist lizensiert unter [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.en.html).

## Credits

Autor: Jordan Krause ([jordan.krause@oncampus.de](mailto:jordan.krause@oncampus.de))
Inspired by / thanks to: oncampus GmbH, Laurenz Schindler
