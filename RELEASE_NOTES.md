# Advertising Entity: Release notes

8.x-1.0-alpha26:
- AdBlocks have been refactored to AdDisplay configuration entities.
  This change includes a new configuration schema and permissions.
  You'll need to export your config after running the updates.
- Added theme hook suggestions for Advertising entities and Display configs.
- Prevent a double-reset when viewing entities via their main routes.
- Created the service collection class AdEntityServices which offers
  any single service provided by the ad_entity module.
This release is a candidate for the first beta release.

8.x-1.0-alpha25:
- Replaced Xss::filter with Html::escape to avoid a possibly broken
  HTML structure by given user input.
- Added GPLv2 license.

8.x-1.0-alpha24:
- ad_entity_adtech: Switched to asynchronous loading of Advertisement.
- Added defensive checks for existing field items.
  Issue: https://github.com/BurdaMagazinOrg/module-ad_entity/issues/8
