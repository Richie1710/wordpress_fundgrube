# Fundgrube Plugin - Fehlerbehebung

## "Publishing failed. No route was found matching the URL and request method"

Dieser Fehler tritt auf, wenn die WordPress REST API-Routen für das Custom Post Type nicht korrekt registriert sind.

### Lösungsschritte:

#### 1. Plugin neu aktivieren
```
WordPress Admin → Plugins → Fundgrube deaktivieren → Aktivieren
```

#### 2. Permalinks aktualisieren
```
WordPress Admin → Einstellungen → Permalinks → "Änderungen speichern" klicken
```

#### 3. Debug-Modus aktivieren
Fügen Sie in `wp-config.php` hinzu:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

#### 4. Debug-Informationen prüfen
```
WordPress Admin → Fundgrube → Debug Info (nur bei aktiven WP_DEBUG)
```

#### 5. REST API manuell testen
Öffnen Sie in Ihrem Browser:
```
http://ihre-domain.com/wp-json/wp/v2/fundstuecke
```

Erwartete Antwort: JSON-Array (auch wenn leer: `[]`)

### Docker-spezifische Lösungen:

#### Container neu starten
```bash
cd /path/to/wordpress_compose
docker-compose restart wordpress
```

#### Plugin-Volume prüfen
Stellen Sie sicher, dass das Volume korrekt gemountet ist:
```yaml
volumes:
  - /Users/danielrichardt/Projects/wordpress_fundgrube:/var/www/html/wp-content/plugins/fundgrube
```

#### Container-Logs prüfen
```bash
docker-compose logs -f wordpress
```

## Häufige Ursachen und Lösungen

### 1. Permalink-Struktur
**Problem:** Standard-Permalinks (z.B. `?p=123`) funktionieren nicht mit REST API
**Lösung:** 
- Gehen Sie zu `Einstellungen > Permalinks`
- Wählen Sie eine andere Struktur als "Einfach" (z.B. "Beitragsname")
- Speichern Sie die Änderungen

### 2. Plugin-Ladung fehlgeschlagen
**Symptome:** Plugin erscheint nicht im Admin-Menü
**Prüfung:**
```bash
# In Docker-Container
docker exec -it wordpress_compose_wordpress_1 php -l /var/www/html/wp-content/plugins/fundgrube/fundgrube.php
```

### 3. Custom Post Type nicht registriert
**Prüfung:** Gehen Sie zu `Fundgrube > Debug Info` und prüfen Sie "Post Type Status"
**Lösung:** Plugin deaktivieren und wieder aktivieren

### 4. REST API deaktiviert
**Prüfung:** Testen Sie `http://ihre-domain.com/wp-json/`
**Mögliche Ursachen:**
- Plugin "Disable REST API" aktiv
- `.htaccess`-Probleme
- Server-Konfiguration

### 5. Berechtigungsprobleme
**Symptome:** "Sie haben nicht die Berechtigung..."
**Lösung:** Stellen Sie sicher, dass Sie als Administrator angemeldet sind

## Erweiterte Fehlerbehebung

### REST API Route manuell registrieren
Fügen Sie temporär in `functions.php` Ihres Themes hinzu:
```php
add_action('rest_api_init', function() {
    error_log('Available routes: ' . print_r(rest_get_server()->get_routes(), true));
});
```

### Plugin-Kompatibilität prüfen
1. Alle anderen Plugins deaktivieren
2. Fundgrube-Plugin testen
3. Plugins einzeln wieder aktivieren, um Konflikte zu identifizieren

### Gutenberg-Probleme
Wenn der Block-Editor nicht funktioniert:
```php
// Temporär in wp-config.php hinzufügen
define('CLASSIC_EDITOR', true);
```

## Docker-Entwicklungsumgebung

### WordPress-Container debuggen
```bash
# In Container einloggen
docker exec -it wordpress_compose_wordpress_1 bash

# WordPress-Version prüfen
wp core version --allow-root --path=/var/www/html

# Plugin-Status prüfen
wp plugin list --allow-root --path=/var/www/html

# REST API testen
curl http://localhost/wp-json/wp/v2/fundstuecke
```

### Datenbank prüfen
```bash
# MySQL-Container
docker exec -it wordpress_compose_db_1 mysql -u wordpress -pwordpress wordpress

# Post Type prüfen
SELECT post_type, COUNT(*) FROM wp_posts GROUP BY post_type;

# Plugin-Optionen prüfen
SELECT * FROM wp_options WHERE option_name LIKE 'fundgrube_%';
```

## Performance-Optimierung

### Plugin-Caching
```php
// Cache für REST API-Antworten
add_filter('rest_post_dispatch', function($response, $server, $request) {
    if (strpos($request->get_route(), '/fundstuecke') !== false) {
        $response->header('Cache-Control', 'max-age=300');
    }
    return $response;
}, 10, 3);
```

### Lazy Loading für Admin
```javascript
// Nur bei Bedarf laden
if (window.pagenow && window.pagenow.indexOf('fundgrube') !== -1) {
    // Admin-Scripts laden
}
```

## Checkliste für Produktionsumgebung

- [ ] WP_DEBUG auf `false` setzen
- [ ] SSL-Zertifikat aktiv
- [ ] Permalinks konfiguriert
- [ ] Backup-Strategie implementiert
- [ ] Sicherheits-Plugins installiert
- [ ] Performance-Caching aktiv
- [ ] Bildoptimierung konfiguriert

## Support-Informationen sammeln

Für Support-Anfragen sammeln Sie folgende Informationen:
```php
// Debug-Informationen
echo 'WordPress Version: ' . get_bloginfo('version') . "\n";
echo 'PHP Version: ' . phpversion() . "\n";
echo 'Plugin Version: ' . FUNDGRUBE_VERSION . "\n";
echo 'Theme: ' . wp_get_theme()->get('Name') . "\n";
echo 'Permalink Structure: ' . get_option('permalink_structure') . "\n";

// Aktive Plugins
$plugins = get_option('active_plugins');
foreach ($plugins as $plugin) {
    echo 'Plugin: ' . $plugin . "\n";
}
```

---

Bei weiteren Problemen erstellen Sie ein Issue mit:
1. Fehlermeldung (vollständig)
2. WordPress-Version
3. PHP-Version
4. Aktive Plugins
5. Theme-Name
6. Browser-Entwicklerkonsole-Ausgabe
