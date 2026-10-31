# relyz-ag/pulse-reporter-craft

Craft CMS 5 plugin that reports a project's runtime and dependency versions to Relyz Pulse. Read-only release mirror.

## Install

```jsonc
// composer.json
"repositories": [
  { "type": "vcs", "url": "https://github.com/relyz-ag/pulse-reporter-craft" }
],
"require": {
  "relyz-ag/pulse-reporter-craft": "^1.0"
}
```

Then `composer update relyz-ag/pulse-reporter-craft`, `php craft plugin/install pulse-reporter`, and commit the project config change.

## Set up

1. Set `PULSE_TOKEN` in production to the project's token from Pulse. Without it the endpoint answers `404`.
2. In Pulse, set the report endpoint URL to `https://<craft-domain>/actions/pulse-reporter/report` (headless: the backend domain) and click **Test reporter**.

If the test shows `401` with the right token, Apache is dropping the `Authorization` header. Add `CGIPassAuth On` to `web/.htaccess`.
