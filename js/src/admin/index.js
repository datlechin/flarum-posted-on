import app from 'flarum/admin/app';

app.initializers.add('datlechin/flarum-posted-on', () => {
  app.registry
    .for('datlechin-posted-on')
    .registerSetting({
      setting: 'datlechin-posted-on.display_mode',
      label: app.translator.trans('datlechin-posted-on.admin.settings.display_mode_label'),
      help: app.translator.trans('datlechin-posted-on.admin.settings.display_mode_help'),
      type: 'select',
      default: 'os_only',
      options: {
        os_only: app.translator.trans('datlechin-posted-on.admin.settings.display_mode_os_only'),
        os_browser: app.translator.trans('datlechin-posted-on.admin.settings.display_mode_os_browser'),
      },
    })
    .registerSetting({
      setting: 'datlechin-posted-on.skip_guests',
      label: app.translator.trans('datlechin-posted-on.admin.settings.skip_guests_label'),
      help: app.translator.trans('datlechin-posted-on.admin.settings.skip_guests_help'),
      type: 'boolean',
    });
});
