import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import Tooltip from 'flarum/common/components/Tooltip';
import Icon from 'flarum/common/components/Icon';

const OS_ICON = {
  Windows: 'fab fa-windows',
  macOS: 'fab fa-apple',
  Mac: 'fab fa-apple',
  iOS: 'fab fa-apple',
  iPadOS: 'fab fa-apple',
  Android: 'fab fa-android',
  Ubuntu: 'fab fa-ubuntu',
  'Chrome OS': 'fab fa-chrome',
  Linux: 'fab fa-linux',
  'GNU/Linux': 'fab fa-linux',
  BlackBerry: 'fab fa-blackberry',
};

const CLIENT_ICON = {
  Chrome: 'fab fa-chrome',
  Chromium: 'fab fa-chrome',
  'Mobile Chrome': 'fab fa-chrome',
  Edge: 'fab fa-edge',
  'Microsoft Edge': 'fab fa-edge',
  Firefox: 'fab fa-firefox-browser',
  'Mobile Firefox': 'fab fa-firefox-browser',
  Safari: 'fab fa-safari',
  'Mobile Safari': 'fab fa-safari',
  Opera: 'fab fa-opera',
  Brave: 'fas fa-shield-halved',
  'Samsung Browser': 'fas fa-globe',
  'Internet Explorer': 'fab fa-internet-explorer',
};

function osIcon(meta) {
  if (!meta || !meta.os) return 'fas fa-globe';
  return OS_ICON[meta.os.family] || OS_ICON[meta.os.name] || 'fas fa-globe';
}

function osLabel(meta) {
  if (!meta || !meta.os) return null;
  return meta.os.version ? `${meta.os.name} ${meta.os.version}` : meta.os.name;
}

function clientLabel(meta) {
  if (!meta || !meta.client) return null;
  return meta.client.version ? `${meta.client.name} ${meta.client.version}` : meta.client.name;
}

function deviceLabel(meta) {
  if (!meta || !meta.device || !meta.device.type) return null;
  const localized = app.translator.trans(`datlechin-posted-on.device_types.${meta.device.type}`);
  const type = typeof localized === 'string' ? localized : meta.device.type;
  const parts = [type];
  if (meta.device.brand && meta.device.model) {
    parts.push(`${meta.device.brand} ${meta.device.model}`);
  } else if (meta.device.brand) {
    parts.push(meta.device.brand);
  }
  return parts.join(' · ');
}

export default class PostedOn extends Component {
  view() {
    const post = this.attrs.post;
    const meta = post.postedOnMeta && post.postedOnMeta();
    const legacy = post.postedOn && post.postedOn();

    if (!meta && !legacy) return null;

    const os = osLabel(meta);
    const client = clientLabel(meta);
    const device = deviceLabel(meta);

    const mode = app.forum.attribute('postedOnDisplayMode') || 'os_only';
    let inline;
    if (meta) {
      inline = mode === 'os_browser' && client ? `${os} · ${client}` : os || client;
    } else {
      // Legacy posts (rendered before posted_on_meta was introduced) only
      // have the flat string, so we trust that and skip the icon lookup.
      inline = legacy;
    }

    return (
      <Tooltip text={this.tooltip({ os, client, device, legacy })}>
        <span className="PostedOn">
          <Icon name={osIcon(meta)} />
          <span className="PostedOn-text">{inline}</span>
        </span>
      </Tooltip>
    );
  }

  tooltip({ os, client, device, legacy }) {
    if (!os && !client && !device) {
      return app.translator.trans('datlechin-posted-on.forum.post.posted_on_text', { posted_on: legacy });
    }
    const lines = [];
    if (os) lines.push(`${app.translator.trans('datlechin-posted-on.forum.post.tooltip.os')}: ${os}`);
    if (client) lines.push(`${app.translator.trans('datlechin-posted-on.forum.post.tooltip.client')}: ${client}`);
    if (device) lines.push(`${app.translator.trans('datlechin-posted-on.forum.post.tooltip.device')}: ${device}`);
    return lines.join('\n');
  }
}
