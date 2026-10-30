# relyz-ag/pulse-reporter-craft

Craft CMS 5 plugin that reports the project's versions to Relyz Pulse ([contract](docs/report-contract.md)).

Read-only release mirror: developed in the private Relyz Pulse repo and published here by tag. Issues and changes go there.

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

No credentials needed; the repo is public.

## Set up

1. `composer update relyz-ag/pulse-reporter-craft`, then `php craft plugin/install pulse-reporter` and commit the project config change.
2. Set `PULSE_TOKEN` in the production environment to the token shown in Pulse (project → Reporter → Reveal / Copy).
3. In Pulse, set the project's report endpoint URL to `https://<craft-domain>/actions/pulse-reporter/report` (for headless projects the backend domain, e.g. `content.example.ch`) and click **Test reporter**.

Without `PULSE_TOKEN` the endpoint answers `404`, so it is off by default (e.g. on staging). There are no CP settings.

## What is reported

PHP version, Craft version and edition, and every package in the project's `composer.json` `require` (plugins included) with its installed version. `require-dev`, platform requirements (`php`, `ext-*`) and transitive packages are not reported. Versions are read live from Composer, so no build step is needed.

## Troubleshooting

"Test reporter" shows `401` although the token is right: the web server drops the `Authorization` header. Current nginx and Apache + PHP-FPM setups pass it; some CGI/FastCGI Apache setups don't. Add to `web/.htaccess` (Apache 2.4.13+):

```apache
CGIPassAuth On
```

## Development (Pulse repo)

Tests need no Craft install:

```sh
docker run --rm -v "$PWD":/repo -w /repo/packages/reporter-craft composer:2 \
  sh -c "composer install --ignore-platform-req='ext-*' && composer test"
```

## Releasing (Pulse repo)

1. Change `src/`, bump `Report::VERSION` in `src/Report.php`, commit and push.
2. `git tag -a reporter-craft-vX.Y.Z -m "reporter-craft X.Y.Z"` and `git push origin reporter-craft-vX.Y.Z`.
3. The "Release reporter" workflow tests and publishes `vX.Y.Z` to `relyz-ag/pulse-reporter-craft`. Published versions are never overwritten.
