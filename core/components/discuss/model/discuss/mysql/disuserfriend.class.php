<?php
/**
 * @package discuss
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/disuserfriend.class.php');
/**
 * @package discuss
 * @subpackage mysql
 */
class disUserFriend_mysql extends disUserFriend {}