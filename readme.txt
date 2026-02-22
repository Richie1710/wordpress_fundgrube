=== Fundgrube ===
Contributors: danielrichardt
Donate link: https://richardt-digitalsolutions.de/
Tags: fundgrube, lost-and-found, fundstücke, verwaltung, custom-post-type
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 7.4
Stable tag: v1.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Ein WordPress-Plugin zur Verwaltung von Fundstücken mit Bildern und Metadaten. Ermöglicht das einfache Anlegen, Verwalten und Anzeigen von gefundenen Gegenständen.

== Description ==

Das Fundgrube-Plugin ist die perfekte Lösung für Organisationen, Unternehmen oder Gemeinden, die eine professionelle Verwaltung von Fundstücken benötigen. Mit diesem Plugin können Sie:

**Hauptfunktionen:**
* 🔍 **Fundstücke verwalten**: Erstellen und bearbeiten Sie Fundstücke mit detaillierten Informationen
* 📸 **Bilder hochladen**: Fügen Sie Fotos zu jedem Fundstück hinzu
* 🏷️ **Kategorisierung**: Organisieren Sie Fundstücke nach Kategorien (verloren, gefunden, zurückgegeben)
* 📍 **Fundort-Tracking**: Dokumentieren Sie, wo Gegenstände gefunden wurden
* 📅 **Datumserfassung**: Halten Sie Funddatum und andere wichtige Termine fest
* 🎨 **Responsive Design**: Funktioniert perfekt auf Desktop und mobilen Geräten

**Für Administratoren:**
* Übersichtliches Dashboard mit Statistiken
* Einfache Verwaltung über das WordPress-Backend
* Konfigurierbare Einstellungen für Anzeige und Verhalten
* Export-/Import-Funktionen für Datenübertragung

**Für Besucher:**
* Suchfunktion für Fundstücke
* Filterbare Auflistung aller verfügbaren Items
* Detailansichten mit allen wichtigen Informationen
* Kontaktmöglichkeiten für Rückgabe

**Shortcodes:**
* `[fundgrube_liste]` - Zeigt eine Liste aller Fundstücke an
* `[fundgrube_suche]` - Fügt eine Suchmaske ein
* `[fundgrube_einzeln id="123"]` - Zeigt ein einzelnes Fundstück an

**Entwicklerfreundlich:**
* PSR-4 Autoloading mit Composer
* WordPress Coding Standards
* Erweiterbare Architektur
* Hooks und Filter für Anpassungen
* PHPUnit-Tests

== Installation ==

1. **Automatische Installation:**
   - Gehen Sie zu `Plugins > Installieren` in Ihrem WordPress-Backend
   - Suchen Sie nach "Fundgrube"
   - Klicken Sie auf "Jetzt installieren"
   - Aktivieren Sie das Plugin

2. **Manuelle Installation:**
   - Laden Sie die Plugin-Dateien in das Verzeichnis `/wp-content/plugins/fundgrube/` hoch
   - Aktivieren Sie das Plugin über das 'Plugins'-Menü in WordPress

3. **Nach der Aktivierung:**
   - Besuchen Sie `Fundgrube > Einstellungen` um das Plugin zu konfigurieren
   - Erstellen Sie Ihr erstes Fundstück unter `Fundgrube > Neues Fundstück`

== Frequently Asked Questions ==

= Kann ich eigene Felder zu den Fundstücken hinzufügen? =

Ja, das Plugin ist entwicklerfreundlich aufgebaut. Sie können über WordPress-Hooks eigene Meta-Felder hinzufügen.

= Funktioniert das Plugin mit jedem WordPress-Theme? =

Das Plugin ist so entwickelt, dass es mit den meisten Themes kompatibel ist. Falls Anpassungen nötig sind, können Sie die Template-Dateien in Ihrem Theme überschreiben.

= Kann ich die Anzeige der Fundstücke anpassen? =

Ja, das Plugin bietet verschiedene Einstellungsmöglichkeiten. Zusätzlich können Entwickler die Ausgabe über Filter anpassen.

= Sind die Daten DSGVO-konform? =

Das Plugin speichert nur die Daten, die Sie eingeben. Stellen Sie sicher, dass Sie die DSGVO-Bestimmungen in Ihrer Datenschutzerklärung berücksichtigen.

= Kann ich Fundstücke importieren/exportieren? =

Aktuell gibt es keine direkte Import/Export-Funktion, aber da es sich um WordPress Posts handelt, können Sie Standard WordPress-Tools verwenden.

= Welche Bildformate werden unterstützt? =

Alle von WordPress unterstützten Bildformate (JPEG, PNG, GIF, WebP) können verwendet werden.

== Screenshots ==

1. **Dashboard-Übersicht** - Das Hauptdashboard mit Statistiken und Schnellaktionen
2. **Fundstück bearbeiten** - Die Bearbeitungsansicht mit allen Metafeldern
3. **Fundstück-Liste im Backend** - Übersichtliche Verwaltung aller Fundstücke
4. **Frontend-Anzeige** - Wie Fundstücke für Besucher angezeigt werden
5. **Plugin-Einstellungen** - Konfigurationsmöglichkeiten für Administratoren
6. **Shortcode-Beispiele** - Verschiedene Darstellungsmöglichkeiten im Frontend

== Changelog ==

= 1.0.6 =
* Version auf 1.0.6 angehoben

= 1.0.5 =
* Version auf 1.0.5 angehoben

= 1.0.4 =
* Version auf 1.0.4 angehoben

= 1.0.0 =
* Initial release
* Custom Post Type für Fundstücke
* Admin-Interface mit Meta-Boxen
* Frontend-Shortcodes für Anzeige und Suche
* Responsive Templates
* Mehrsprachigkeit (Deutsch/Englisch)
* WordPress 6.3 Kompatibilität

== Upgrade Notice ==

= 1.0.6 =
Version 1.0.6 – aktuelle stabile Version.

= 1.0.5 =
Version 1.0.5.

= 1.0.4 =
Version 1.0.4.

= 1.0.0 =
Erste Veröffentlichung des Fundgrube-Plugins. Nach der Installation besuchen Sie die Plugin-Einstellungen zur Konfiguration.

== Developer Notes ==

**Hooks & Filters:**
* `fundgrube_init` - Fired when plugin initializes
* `fundgrube_activate` - Fired on plugin activation
* `fundgrube_deactivate` - Fired on plugin deactivation

**Template Hierarchy:**
* `single-fundgrube-item.php` - Single fundstück template
* `archive-fundgrube-item.php` - Archive template for fundstücke

**Custom Post Type:**
* Post Type: `fundgrube_item`
* Meta Fields: `_fundgrube_*`

**Requirements:**
* PHP 7.4+
* WordPress 5.0+
* MySQL 5.6+

== Support ==

Für Support und Fragen:
* WordPress.org Support Forum
* GitHub Issues: https://github.com/Richie1710/wordpress_fundgrube/issues
* Email: info@richardt-digitalsolutions.de

== Roadmap ==

**Version 1.1 (geplant):**
* Erweiterte Suchfilter
* Email-Benachrichtigungen
* CSV Import/Export
* Mehrsprachige Unterstützung

**Version 1.2 (geplant):**
* REST API Erweiterungen
* Mobile App Unterstützung
* Erweiterte Berichtsfunktionen
* Integration mit externen Systemen

== Credits ==

Entwickelt mit ❤️ für die WordPress-Community

**Verwendete Technologien:**
* WordPress Plugin API
* PHP 7.4+
* Composer für Autoloading
* PHPUnit für Testing
* WordPress Coding Standards
