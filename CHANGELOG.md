# Changelog

## 1.3.1

- Render default categories in PHP so the selector remains populated before JavaScript loads or when requests fail.
- Increase the sidebar to 380px on desktop and explicitly compact contributor rows to 24px, overriding global Nextcloud table heights.
- Refresh versioned Nextcloud asset URLs with the 1.3.1 version bump.
- Browser regressions cover a long contributor list, global table styles and categories without JavaScript.

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
