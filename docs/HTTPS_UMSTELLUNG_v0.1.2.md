# HTTPS nach dem HTTP-Test aktivieren

Der HTTP-Modus ist ausschließlich eine temporäre Testmöglichkeit. DB-Zugangsdaten, Admin-Passwörter und Session-Cookies werden dabei nicht verschlüsselt übertragen. Vor der Umstellung dürfen keine echten Schul- oder Personendaten in die Anwendung gelangen.

1. Beim Hoster ein Zertifikat für die Domain aktivieren und HTTPS-Aufruf prüfen.
2. Eine Sicherung der Dateien und Datenbank anlegen.
3. `config.local.php` außerhalb des öffentlichen Dokumentenstamms bearbeiten und diese beiden Werte ändern:

   ```php
   'environment' => 'production',
   'secure_cookies' => true,
   ```

4. Am Webserver alle HTTP-Aufrufe auf HTTPS umleiten.
5. Browsercookies für die Domain löschen und neu über HTTPS anmelden.
6. Testzugangsdaten des Datenbankbenutzers austauschen, falls sie zuvor über HTTP übertragen wurden. Wenn möglich, für Produktion einen eigenen DB-Benutzer mit minimal nötigen Rechten verwenden.

`config.local.php` enthält Datenbankpasswort und App-Schlüssel. Nicht in ein öffentliches Verzeichnis, Git oder ungeschützte Backups legen. HTTP-Testbetrieb ist keine Produktionsfreigabe und keine DSGVO-Konformitätszusage.
