<?php
/**
 *
 * @package discuss
 */
require_once $modx->getOption('discuss.core_path').'model/discuss/discuss.class.php';
$discuss = new Discuss($modx,$scriptProperties);
$discuss->initialize($modx->context->get('key'));

if (empty($_REQUEST['post'])) { $modx->sendErrorPage(); }
$post = $modx->getObject('disPost',$_REQUEST['post']);
if ($post == null) { $modx->sendErrorPage(); }

/* setup defaults */
$properties = $post->toArray();

/* get thread root */
$thread = $post->getThreadRoot();
if ($thread == null) $modx->sendErrorPage();
$properties['thread'] = $thread->get('id');

/* get board breadcrumb trail */
$c = $modx->newQuery('disBoard');
$c->innerJoin('disBoardClosure','Ancestors');
$c->where(array(
    'Ancestors.descendant' => $post->get('board'),
));
$c->sortby('Ancestors.depth','ASC');
$ancestors = $modx->getCollection('disBoard',$c);

$trail = '<a href="'.$modx->makeUrl($modx->getOption('discuss.board_list_resource')).'">Home</a> / ';
foreach ($ancestors as $ancestor) {
    $url = $modx->makeUrl($modx->getOption('discuss.board_resource'),'','?board='.$ancestor->get('id'));
    $trail .= '<a href="'.$url.'">'.$ancestor->get('name').'</a>';
    $trail .= ' / ';
}
$trail .= 'Reply to Post: <a href="[[~[[++discuss.thread_resource]]]]?thread='.$thread->get('id').'">'.$post->get('title').'</a>';
$properties['trail'] = $trail;


/* if POST, process new thread request */
if (!empty($_POST)) {
    $modx->toPlaceholders($_POST,'post');
    $result = include $discuss->config['processorsPath'].'web/post/reply.php';
    $discuss->processResult($result);
} else {
    $modx->setPlaceholder('post.title','Re: '.$post->get('title'));
}

/* get thread */
$properties['thread_posts'] = $modx->hooks->load('post/getthread',array(
    'post' => &$post,
    'thread' => &$thread,
));



/* output form to browser */
$modx->regClientStartupScript($discuss->config['jsUrl'].'web/dis.post.reply.js');
$modx->setPlaceholder('discuss.error_panel',$discuss->getChunk('disError'));
$modx->setPlaceholder('discuss.post',$post->get('title'));

return $discuss->output('thread/reply',$properties);