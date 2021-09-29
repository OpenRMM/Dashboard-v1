<?php
/*
 * login_with_teamviewer.php
 *
 * @(#) $Id: login_with_teamviewer.php,v 1.1 2014/11/07 08:26:17 mlemos Exp $
 *
 */

	/*
	 *  Get the http.php file from http://www.phpclasses.org/httpclient
	 */
	require('api/oauth/http.php');
	require('api/oauth/oauth_client.php');

	$client = new oauth_client_class;
	$client->server = 'TeamViewer';
	$client->debug = false;
	$client->debug_http = true;
	$client->redirect_uri = 'http://rmm.smgunlimited.com/rmm';

    $client->client_id = '318473-nHYZmURDn7Sex9Yh6hV7'; $application_line = __LINE__;
    $client->client_secret = 'oDoV5Dh22P3wM1XuUfQO';

	if(strlen($client->client_id) == 0
	|| strlen($client->client_secret) == 0)
		die('Please go to TeamViewer applications page '.
			'https://login.teamviewer.com/nav/api create an application '.
			'and in the line '.$application_line.' set the client_id to Client ID '.
			'and client_secret with Client secret.');

	/* API permissions
	 */
	$client->scope = '';
	if(($success = $client->Initialize()))
	{
		if(($success = $client->Process()))
		{
			if(strlen($client->authorization_error))
			{
				$client->error = $client->authorization_error;
				$success = false;
			}
			elseif(strlen($client->access_token))
			{
				$success = $client->CallAPI(
					'https://webapi.teamviewer.com/api/v1/account',
					'GET', array(), array('FailOnAccessError'=>true), $user);
			}
		}
		$success = $client->Finalize($success);
	}
	if($client->exit)
		exit;
	if($success)
	{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>TeamViewer OAuth client results</title>
</head>
<body>
<?php
		echo '<h1>', HtmlSpecialChars($user->name),
			' you have logged in successfully with TeamViewer!</h1>';
		echo '<pre>', HtmlSpecialChars(print_r($user)), '</pre>';
?>
</body>
</html>
<?php
	}
	else
	{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>OAuth client error</title>
</head>
<body>
<h1>OAuth client error</h1>
<pre>Error: <?php echo HtmlSpecialChars($client->error); ?></pre>
</body>
</html>
<?php
	}

?>