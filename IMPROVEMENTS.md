# Verbesserungen & Offene Punkte (Stand: aktuelles Arbeitspaket)

## Erledigt
- PHP 8.5 Zielplattform, Composer v2 im Dockerfile; `composer.json`/`composer.lock` bereinigt.
- Abhängigkeiten `parsedown`/`metaparsedown` entfernt, eigener `Simpla\Markdown\MarkdownParser`.
- Fehlerhärtung: `EntityFactory` (Datei-Checks, Pflichtfelder), `ContentIterator` (Pfadprüfung, nur `.md`, versteckte Dateien raus), `AssetHandler` (klare Fehlermeldungen, sichere Kopie), typed Properties/Dynamic-Property-Fixes.
- Template-Rendering gekapselt via `TemplateRendererTrait`; Generatoren nutzen scoped Includes mit Fehlermeldung bei fehlenden Templates.
- Tests hinzugefügt (PHPUnit):
  - `MarkdownParserTest`, `EntityFactoryTest`, `ContentIteratorTest`
  - `ContentGeneratorTest`, `ContentIndexGeneratorTest`, `TagIndexGeneratorTest`
- Composer-Script `composer test` und `phpunit.xml.dist` ergänzt.

## Noch zu tun / Ideas
- Weitere Tests: FeedGenerator (RSS-Snapshot mit stabilisiertem Timestamp), MenuGenerator (intern/extern Links), AssetHandler (Copy/Write-Failure-Pfade).
- Parser-Erweiterungen je nach Bedarf: Tabellen/Code-Highlighting/strengeres Frontmatter-Parsing (YAML-kompatibel), optionale Escaping-Strategie dokumentieren.
- CI-Pipeline aufsetzen (Docker build + `composer test`).
- Lizenz-Header konsistent bei neuen PHP-Dateien beibehalten.

## Befehle (Lokal / Docker)
- Lokal (im Verzeichnis `engine/`):  
  - `composer install` (inkl. dev)  
  - `composer build` (schreibt nach `../dist`)  
  - `composer test`
- Docker-Build wie vorgesehen (von Projektroot):
  ```bash
  mkdir -p dist
  docker run --rm \
    -v "$PWD/page":/usr/src/simpla/page \
    -v "$PWD/dist":/usr/src/simpla/dist \
    -w /usr/src/simpla/engine \
    fanatique/simpla:latest \
    composer build
  ```

