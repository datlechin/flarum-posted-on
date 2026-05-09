# Changelog

[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) format, [Semantic Versioning](https://semver.org/).

## [2.0.0-beta.2] - 2026-05-09

### Added

- User-Agent Client Hints support. New middleware advertises `Accept-CH` and `Critical-CH` on forum and api responses so Chromium browsers attach `Sec-CH-UA-Platform-Version` and friends to subsequent requests.
- `posted_on_meta` JSON column on posts. Stores a structured snapshot (OS name/version/family, browser name/version, device type/brand/model, bot flag) used by the frontend for icons and a multi-line tooltip.
- Admin settings:
  - `display_mode`: `os_only` (e.g. "Windows 11") or `os_browser` (e.g. "Windows 11 · Chrome 121").
  - `skip_guests`: drops platform metadata for guest posts.
- `matomo/device-detector` dependency replaces the hand-rolled regex sniffer.
- Unit tests covering Win 10/11 disambiguation, Mac UA freeze, Firefox without Client Hints, iPhone, Android, and bot detection.

### Changed

- OS detection rewritten. Windows 10 and Windows 11 are now distinguished correctly; macOS reports the real version when Client Hints are present.
- Frontend tooltip shows OS, browser, and device on separate lines instead of a single sentence.
- Icons are now picked from the resolved OS / browser family rather than a `startsWith` of the rendered string.

### Fixed

- Windows 11 was reported as Windows 10 because Microsoft froze the User-Agent at `Windows NT 10.0`. With Client Hints we now read `Sec-CH-UA-Platform-Version` directly.
- macOS was reported as 10.15 since macOS 11 because Apple froze the UA. Same fix via Client Hints.
- Firefox / Safari / Tor users no longer get a guessed OS version. The frontend shows "Windows" or "macOS" with no version, which is the honest answer when the browser doesn't ship UA-CH.
- Bots are no longer recorded.

### Notes

- Posts authored before this version keep their existing `posted_on` string and render unchanged. Only posts created after upgrade get the rich metadata.
- The first POST after install may still miss the platform version because the browser hasn't yet seen the `Accept-CH` header for the origin. After one page load the browser caches the policy and subsequent posts include the version.

## [2.0.0-beta.1] - 2026-02-24

- First Flarum 2.x compatible release.

[2.0.0-beta.2]: https://github.com/datlechin/flarum-posted-on/releases/tag/v2.0.0-beta.2
[2.0.0-beta.1]: https://github.com/datlechin/flarum-posted-on/releases/tag/v2.0.0-beta.1
