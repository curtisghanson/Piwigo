<?php
require_once __DIR__ . '/../vendor/autoload.php';

// by default we start with guest
$user['id'] = $conf['guest_id'];
var_dump(session_name());exit;
if (isset($_COOKIE[session_name()]))
{
  if (isset($_GET['act']) and $_GET['act'] == 'logout')
  { // logout
    logout_user();
    redirect(get_gallery_home_url());
  }
  elseif (!empty($_SESSION['pwg_uid']))
  {
    $user['id'] = $_SESSION['pwg_uid'];
  }
}

// Now check the auto-login
if ( $user['id'] == $conf['guest_id'] )
{
  auto_login();
}

// using Apache authentication override the above user search
if ($conf['apache_authentication'])
{
  $remote_user = null;
  foreach (array('REMOTE_USER', 'REDIRECT_REMOTE_USER') as $server_key)
  {
    if (isset($_SERVER[$server_key]))
    {
      $remote_user = $_SERVER[$server_key];
      break;
    }
  }

  if (isset($remote_user))
  {
    if (!($user['id'] = get_userid($remote_user)))
    {
      $user['id'] = register_user($remote_user, '', '', false);
    }
  }
}

$user = build_user($user['id'], (defined('IN_ADMIN') and IN_ADMIN) ? false : true); // use cache ?

if ($conf['browser_language'] and (is_a_guest() or is_generic()) )
{
  get_browser_language($user['language']);
}
trigger_notify('user_init', $user);
