import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import Tooltip from 'flarum/common/components/Tooltip';

export default class PostedOn extends Component {
  oninit(vnode) {
    super.oninit(vnode);
  }

  view() {
    const post = this.attrs.post;

    return (
      <Tooltip text={this.getPostedSource()}>
        <span className="PostedOn">{post.postedOn()}</span>
      </Tooltip>
    );
  }

  getPostedSource() {
    const post = this.attrs.post;

    return app.translator.trans('datlechin-posted-on.forum.post.posted_on_text', { posted_on: post.postedOn() });
  }
}
