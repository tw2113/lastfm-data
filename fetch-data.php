<?php
set_time_limit(0);

$start = strtotime('December 31st 11:59pm 2007');
$end = strtotime('January 1st 12:00am 2015');
$api_key = '';

$status = json_decode(
	file_get_contents(
		"http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=tw2113&limit=200&extended=1&format=json&from={$start}&to={$end}&api_key={$api_key}"
	)
);

$totalpages = $status->recenttracks->{'@attr'}->totalPages;

echo '<h1>' . $totalpages . '</h1>';

for( $i=1;$i<=$totalpages;$i++) {
	$fname = "./data/page{$i}.json";

	$file = fopen($fname,'w+');
	$thepage = file_get_contents("http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=tw2113&limit=200&extended=1&format=json&from={$start}&to={$end}&page={$i}&api_key={$api_key}");
	echo fwrite($file,$thepage);
	fclose($file);
}

echo '<p>finished<p>';