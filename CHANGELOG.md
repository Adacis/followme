# Changelog

## 1.3.0

- Native JavaScript interface without Moment, jQuery or a frontend framework.
- Accessible centred dialog, responsive article cards and contributor ranking.
- Application version read directly from Nextcloud application metadata.
- Administrator-only Nextcloud settings section, linked from the bottom settings menu.
- CSRF-protected settings saves; passwords encrypted with Nextcloud ICrypto and never returned to the browser.
- Non-destructive database migration: retain latest duplicate settings, encrypt legacy passwords, add unique setting names and article indexes.
- Portable QueryBuilder queries with configurable table prefixes and UTC year boundaries.
- Optional Rocket.Chat notifications; failures do not undo a saved article.
- Modern tests and automated release checks.
