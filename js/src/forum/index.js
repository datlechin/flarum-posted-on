import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import Post from 'flarum/common/models/Post';
import Model from 'flarum/common/Model';
import CommentPost from 'flarum/forum/components/CommentPost';
import PostedOn from './components/PostedOn';

app.initializers.add('datlechin/flarum-posted-on', () => {
  Post.prototype.postedOn = Model.attribute('posted_on');

  extend(CommentPost.prototype, 'headerItems', function (items) {
    const post = this.attrs.post;
    items.add('postedOn', PostedOn.component({ post }));
  });
});
