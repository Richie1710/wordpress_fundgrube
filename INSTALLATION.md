# Fundgrube WordPress-Plugin - Installationsanleitung

## Systemanforderungen

- **WordPress:** Version 5.0 oder höher
- **PHP:** Version 7.4 oder höher
- **MySQL:** Version 5.6 oder höher
- **Composer:** Für Dependency Management (optional, aber empfohlen)

## Installation

### Methode 1: Standard WordPress-Installation

1. **Plugin herunterladen/kopieren**
   ```bash
   # Das komplette Verzeichnis in wp-content/plugins/ kopieren
   cp -r wordpress_fundgrube /pfad/zu/wordpress/wp-content/plugins/fundgrube
   ```

2. **Plugin aktivieren**
   - WordPress-Admin aufrufen
   - Zu `Plugins > Installierte Plugins` navigieren
   - "Fundgrube" Plugin aktivieren

### Methode 2: Development-Installation mit Composer

1. **In das Plugin-Verzeichnis wechseln**
   ```bash
   cd /pfad/zu/wordpress/wp-content/plugins/fundgrube
   ```

2. **Dependencies installieren**
   ```bash
   composer install
   ```

3. **Plugin aktivieren** (siehe Methode 1, Schritt 2)

## Erste Schritte nach der Installation

### 1. Plugin-Einstellungen konfigurieren

Nach der Aktivierung erscheint im WordPress-Admin ein neuer Menüpunkt "Fundgrube":

1. `Fundgrube > Einstellungen` aufrufen
2. Grundeinstellungen konfigurieren:
   - Anzahl Fundstücke pro Seite
   - Bildergalerie aktivieren/deaktivieren
   - Kontaktinformationen eintragen

### 2. Erstes Fundstück erstellen

1. `Fundgrube > Neues Fundstück` aufrufen
2. Fundstück-Details eingeben:
   - **Titel**: Kurze Beschreibung des Gegenstands
   - **Beschreibung**: Detaillierte Beschreibung
   - **Kategorie**: Verloren/Gefunden/Zurückgegeben
   - **Fundort**: Wo wurde es gefunden?
   - **Funddatum**: Wann wurde es gefunden?
   - **Weitere Details**: Farbe, Größe, Zustand, etc.

3. **Hauptbild hochladen** (empfohlen)
4. **Veröffentlichen** klicken

### 3. Shortcodes in Seiten/Beiträge einfügen

#### Fundstück-Liste anzeigen
```shortcode
[fundgrube_liste anzahl="10" kategorie="gefunden"]
```

**Parameter:**
- `anzahl`: Anzahl der angezeigten Items (Standard: 10)
- `kategorie`: Filterung nach Kategorie (verloren/gefunden/zurueckgegeben)
- `sortierung`: datum/titel (Standard: datum)
- `reihenfolge`: asc/desc (Standard: desc)

#### Suchformular einfügen
```shortcode
[fundgrube_suche platzhalter="Fundstück suchen..."]
```

#### Einzelnes Fundstück anzeigen
```shortcode
[fundgrube_einzeln id="123"]
```

## Entwickler-Setup (Optional)

### Testing-Umgebung einrichten

1. **WordPress Test-Suite installieren**
   ```bash
   bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
   ```

2. **Tests ausführen**
   ```bash
   composer test
   ```

### Code-Standards prüfen

```bash
# WordPress Coding Standards prüfen
composer run cs

# Automatische Code-Korrekturen
composer run cbf
```

### Development-Server starten

Wenn Sie das Plugin in einer lokalen Entwicklungsumgebung testen möchten:

```bash
# Mit Local by Flywheel, MAMP, XAMPP oder ähnlich
# Plugin-Ordner in wp-content/plugins/ verlinken

# Oder mit wp-cli
wp server --host=localhost --port=8080
```

## Konfiguration

### Template-Überschreibung

Um die Darstellung anzupassen, können Sie die Plugin-Templates in Ihrem Theme überschreiben:

1. **Ordner erstellen**: `ihr-theme/fundgrube/`
2. **Templates kopieren** aus `plugins/fundgrube/templates/`
3. **Nach Bedarf anpassen**

Verfügbare Templates:
- `single-fundgrube-item.php` - Einzelansicht
- `archive-fundgrube-item.php` - Archive-Ansicht

### Custom CSS hinzufügen

Eigene Styles können über den WordPress Customizer oder in der `style.css` Ihres Themes hinzugefügt werden:

```css
/* Beispiel-Anpassungen */
.fundgrube-item {
    border-color: #your-color;
}

.fundgrube-mehr-button {
    background-color: #your-brand-color;
}
```

## Fehlerbehebung

### Häufige Probleme

1. **Plugin erscheint nicht im Admin-Menü**
   - Prüfen Sie, ob Sie Administratorrechte haben
   - Deaktivieren und erneut aktivieren

2. **Shortcodes werden nicht verarbeitet**
   - Cache leeren (falls Caching-Plugin aktiv)
   - Plugin-Aktivierung prüfen

3. **Bilder werden nicht angezeigt**
   - Upload-Berechtigungen prüfen (`wp-content/uploads/`)
   - Bildgrößen in WordPress regenerieren

4. **CSS-Styles werden nicht geladen**
   - Theme-Kompatibilität prüfen
   - Browser-Cache leeren

### Debug-Modus aktivieren

Fügen Sie diese Zeilen in Ihre `wp-config.php` ein:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Log-Dateien finden Sie unter: `/wp-content/debug.log`

### Plugin komplett entfernen

1. Plugin deaktivieren
2. Plugin löschen
3. Optional: Datenbank-Bereinigung
   ```sql
   DELETE FROM wp_posts WHERE post_type = 'fundgrube_item';
   DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT id FROM wp_posts);
   DELETE FROM wp_options WHERE option_name LIKE 'fundgrube_%';
   ```

## Support und Weiterentwicklung

- **Issues**: Probleme über GitHub Issues melden
- **Dokumentation**: Weitere Infos in `readme.txt`
- **Development**: Pull Requests willkommen

## Sicherheit

- Regelmäßige Updates installieren
- Starke Passwörter verwenden
- Sicherheits-Plugins nutzen
- Regelmäßige Backups erstellen

---

**Viel Erfolg mit dem Fundgrube-Plugin! 🔍📦**
