import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import Post from 'flarum/common/models/Post';
import User from 'flarum/common/models/User';
import Model from 'flarum/common/Model';
import Switch from 'flarum/common/components/Switch';
import PostedOn from './components/PostedOn';

app.initializers.add('datlechin/flarum-posted-on', () => {
  Post.prototype.postedOn = Model.attribute('postedOn');
  User.prototype.disclosePostedOn = Model.attribute('disclosePostedOn');

  extend('flarum/forum/components/CommentPost', 'headerItems', function (items) {
    const post = this.attrs.post;
    const user = post.user();

    if (!post.postedOn()) return;
    if (user && user.disclosePostedOn() === false) return;

    items.add('postedOn', PostedOn.component({ post }));
  });

  extend('flarum/forum/components/SettingsPage', 'privacyItems', function (items) {
    items.add(
      'disclosePostedOn',
      <Switch
        state={this.user.disclosePostedOn()}
        onchange={(value) => {
          this.disclosePostedOnLoading = true;

          this.user.save({ disclosePostedOn: value }).then(() => {
            this.disclosePostedOnLoading = false;
            m.redraw();
          });
        }}
        loading={this.disclosePostedOnLoading}
      >
        {app.translator.trans('datlechin-posted-on.forum.settings.privacy_disclose_posted_on_label')}
      </Switch>
    );
  });
});
