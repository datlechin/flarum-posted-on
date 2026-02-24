import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import Tooltip from 'flarum/common/components/Tooltip';
import Icon from 'flarum/common/components/Icon';

export default class PostedOn extends Component {
  view() {
    const post = this.attrs.post;

    if (!post.postedOn()) {
      return;
    }

    return (
      <Tooltip text={this.getPostedOn(post)}>
        <span className="PostedOn">
          <Icon name={this.getIcon(post)} /> {post.postedOn()}
        </span>
      </Tooltip>
    );
  }

  getPostedOn(post) {
    return app.translator.trans('datlechin-posted-on.forum.post.posted_on_text', {
      posted_on: post.postedOn(),
    });
  }

  getIcon(post) {
    const os = post.postedOn();

    if (os.startsWith('Windows')) return 'fab fa-windows';
    if (os.startsWith('macOS')) return 'fab fa-apple';
    if (os.startsWith('iOS') || os.startsWith('iPadOS')) return 'fab fa-apple';
    if (os.startsWith('Android')) return 'fab fa-android';
    if (os.startsWith('Ubuntu')) return 'fab fa-ubuntu';
    if (os.startsWith('Manjaro') || os.startsWith('Linux')) return 'fab fa-linux';
    if (os.startsWith('BlackBerry')) return 'fab fa-blackberry';

    return 'fas fa-globe';
  }
}
