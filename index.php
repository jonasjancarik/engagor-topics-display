<?php

// =========================================================================================================
//
// WHAT DOES THIS SCRIPT DO?
//
// This code performs a simple query on the Engagor API (the /me/accounts endpoint),
// which returns a list of accounts (and associated projects, topics and monitored profiles)
// the authenticated user (hardcoded) has access to.
//
// It then processes the response and either produces a pseudo-XLS file (if the ?format=xls parameter
// is present) or an HTML page consisting of a single large table.
//
// Author contacts:
//
// Daniel Fritz (daniel.fritz@ext.ec.europa.eu)
// Jonas Jancarik (jonas.jancarik@ec.europa.eu)
//
// =========================================================================================================


// require Engagor API class
// See http://developers.engagor.com/ for documentation.
require 'lib/Engagor.php';

// = SETTINGS =
require 'config.php';

if (!isset($accountId) OR !isset($accessToken)) {
	die('Please add your account ID and access token in the config.php file');
}

// Create a new instance of the Engagor class
$engagor = new Engagor($accessToken);

// Form API request
$request = $engagor->api(
	'/me/accounts',
	['limit' => 99999] // an arbitrarily high number to ensure all accounts will be returned
	);

// Perform request and store to $response
$response = $engagor->processResponse($request);

// Check whether the response looks ok (i.e. is not empty, http response code is not 200)
if (empty($response['meta']) OR $response['meta']['code'] != 200) {
    die('<p>Something went wrong. Here is the API response:</p>' . print_r($response, true));
}

// = OUTPUT PREPARATION =

$output = '
<table>
	<thead>
		<tr>
			<th>Project Name</th>
			<th>Project ID</th>
			<th>Topic Name</th>
			<th>Topic ID</th>
			<th>Monitored Profile Type</th>
			<th>Monitored Profile User Name</th>
			<th>Monitored Profile Display Name</th>
			<th>Search Keywords</th>
		</tr>
	</thead>
	<tbody>
';


foreach ($response['response']['data']['0']['projects'] as $key => $project) {

	foreach ($project['topics'] as $key => $topic) {

		foreach ($topic['monitoredprofiles'] as $key => $monitored_profile) {
			$output .= '<tr>';
			$output .= '<td>' . $project['name'] . '</td>';
			$output .= '<td>' . $project['id'] . '</td>';
			$output .= '<td>' . $topic['name'] . '</td>';
			$output .= '<td>' . $topic['id'] . '</td>';
			$output .= '<td>' . $monitored_profile['type'] . '</td>';
			$output .= (isset($monitored_profile['username'])) ? '<td>' . $monitored_profile['username'] . '</td>' : '<td></td>';
			$output .= '<td>' . $monitored_profile['displayname'] . '</td>';
			$output .= ($monitored_profile['type'] == 'keywordsearch') ? '<td>' . $topic['searchkeywords'] . '</td>' : '<td></td>';
			$output .= '</tr>';
		}

	}

}

$output .= '</tbody></table>';

// = OUTPUT =

// == XLS OUTPUT ==

if (isset($_GET['format']) AND 'xls' == $_GET['format']) {

	header('Content-Type: text/html; charset=utf-8');
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=Engagor_topics.xls");
	header("Pragma: no-cache");
	header("Expires: 0");

	echo "\xFF\xFE" . mb_convert_encoding($output, 'UTF-16LE', 'UTF-8');

}

// == HTML OUTPUT ==

else {
	header('Content-Type: text/html; charset=utf-8');
	header("Pragma: no-cache");
	header("Expires: 0");

	echo "
	<!doctype html>

	<html lang='en'>
	<head>
		<meta charset='utf-8'>

		<title>EC Engagor Topics</title>
		<meta name='description' content='EC Engagor Topics'>
		<meta name='author' content='DG COMM A5'>

		<link rel='stylesheet' href='css/styles.css?v=1.0'>

		<!--[if lt IE 9]>
		<script src='http://html5shiv.googlecode.com/svn/trunk/html5.js'></script>
		<![endif]-->
	</head>
	<body>
	";

	echo $output;

	echo '
	</body>
	</html>
	';

}
