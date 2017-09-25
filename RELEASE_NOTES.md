# Advertising Entity: Release notes

8.x-1.0-alpha25:
- Replaced Xss::filter with Html::escape to avoid a possibly broken
  HTML structure by given user input.
- Added GPLv2 license.

8.x-1.0-alpha24:
- ad_entity_adtech: Switched to asynchronous loading of Advertisement.
- Added defensive checks for existing field items.
  Issue: https://github.com/BurdaMagazinOrg/module-ad_entity/issues/8
