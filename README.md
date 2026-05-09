# Posted On

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/datlechin/flarum-posted-on.svg)](https://packagist.org/packages/datlechin/flarum-posted-on) [![Total Downloads](https://img.shields.io/packagist/dt/datlechin/flarum-posted-on.svg)](https://packagist.org/packages/datlechin/flarum-posted-on)

Show the operating system, browser, and device next to each post. Backed by [matomo/device-detector](https://github.com/matomo-org/device-detector) and User-Agent Client Hints.

![](https://i.imgur.com/erQjzYD.png)

## What it does

Each post records who it was authored from. The post header gets a small chip with an OS icon and a one-line label. Hover for a tooltip that splits OS, browser, and device on three lines.

The detection combines two signals:

- **Legacy `User-Agent`** for everything that has historically worked: iOS / iPadOS / Android / Linux versions, browser families, mobile device brands.
- **User-Agent Client Hints** (`Sec-CH-UA-*`) for the things the legacy UA cannot tell us, namely Windows 10 vs Windows 11 (both report `Windows NT 10.0`) and the real macOS version (Apple froze the UA at `10.15.7` since macOS 11).

When Client Hints are not available (Firefox, Safari, Tor, older browsers), the version is dropped rather than guessed. A user on Windows 11 in Firefox will see "Windows", not "Windows 10".

## Installation

```sh
composer require datlechin/flarum-posted-on
```

Enable from Admin → Extensions, then run:

```sh
php flarum migrate
php flarum cache:clear
```

## Configuration

Admin → Extensions → Posted On.

| Setting | Default | What it does |
|---|---|---|
| `display_mode` | `os_only` | What renders next to the post. `os_only` shows "Windows 11"; `os_browser` shows "Windows 11 · Chrome 121". The full breakdown is always in the tooltip. |
| `skip_guests` | `false` | When on, posts authored by guests get no platform metadata. |

Each user can also opt out from their own posts being labelled, under their account settings.

## Privacy

The extension stores a coarse fingerprint of the poster's environment: OS family + version, browser family + version, device type + brand + model. This is derived from the request's User-Agent and Client Hints headers; no IP address is recorded by this extension.

You can:

- Toggle `skip_guests` to ignore guest posts entirely.
- Let users hide their own metadata via the privacy switch in their settings.
- Drop the `posted_on_meta` column or null it out to wipe historical metadata.

## Updating from earlier versions

Posts authored before this version keep their existing `posted_on` string and render unchanged. Only posts created after the upgrade pick up the rich metadata.

The first POST a user makes after install may still miss the platform version: the browser needs to see the `Accept-CH` advertisement once before it starts attaching high-entropy hints. After the first page load the policy is cached and subsequent posts include the version.

## Development

```sh
composer test:unit
cd js && npm install && npm run build
```

## Links

- [Packagist](https://packagist.org/packages/datlechin/flarum-posted-on)
- [GitHub](https://github.com/datlechin/flarum-posted-on)
- [Discuss](https://discuss.flarum.org/d/30067)
