<?php
/**
*	 
*	Yahoo Hack U GIF Maker Version 1.0 by Chengyin Liu (chengyin.liu [at] gmail.com)
*	Demo: http://imwillow.com/hackugif/
*
*	The script requires "GIFEncoder.class.php" for encoding the GIF.
*	
*
*	This simple script grabs photos of Hack U and make a GIF.
*	The URLs of photos come from this web page:
*	http://slowgeek.com/hacku
*
*	The start and end photo can be specified with file name. 
*	By default, they take the first one and the last one.
*
*	If the number of totalFrames exceeds max totalFrame setting (70 for default),
*	the script will take the maximum number of totalFrames with the same interval between them.
*
*	GIF will be stored as "new.gif", replacing the old one.
*
*
*
*	Future features:
*	+ Search monkey logo randomly appears, inspired by Paul's iPhone.
*
*
*	The script used GIFEncoder by László Zsidi.
*	More info: http://www.phpclasses.org/browse/package/3163.html
*
*/
	
	
	include "GIFEncoder.class.php";


	/********************************
	* Settings
	********************************/
	$SOURCE_URL = "http://slowgeek.com/hacku/";	// URL of the webpage with photo filenames. 
							// Default: "http://slowgeek.com/hacku/"
	$PHOTO_LOCATION = "http://slowgeek.com/hacku/";	// The location of photos.
							// Default: "http://slowgeek.com/hacku/"
	$IMAGE_RE = '/\<br\>\<a href\=\"(.*)\"\>/';	// The regular expression to find photos' filenames.
							// Default: '/\<br\>\<a href\=\"(.*)\"\>/'
	$MAX_FRAME = 70;				// The maxinum of the total number of frames.
							// Default: 70
	$DEF_DELAY = 50;				// The time between each photos.
							// Default: 50
	
	
	
	
	/********************************
	* Get settings from POST
	********************************/
	// Get settings from POST
	$startFilename = $_POST['start'];
	$endFilename = $_POST['end'];
	$delay = $_POST['interval'];
	
	
	
	
	/********************************
	* Grab photo filenames using curl lib.
	********************************/
	$grabber = curl_init();

	curl_setopt($grabber, CURLOPT_URL, $SOURCE_URL);
	curl_setopt($grabber, CURLOPT_RETURNTRANSFER,1);
	$sourcePage = curl_exec ($grabber);
	curl_close ($grabber);
	
	preg_match_all($IMAGE_RE, $sourcePage, $photoFilenames, PREG_SET_ORDER);
	
	
	
	
	/********************************
	/ Find the number of start and end photo
	********************************/
	$start = 0;
	$end = count($photoFilenames) - 1;
	
	if ($startFilename && $endFilename) {			// If the start and end file is specified
		for ($i = (count($photoFilenames) - 1); $i > 0; $i--) {
			if ($startFilename === $photoFilenames[$i][1])
				$start = $i;
			if ($endFilename === $photoFilenames[$i][1])
				$end = $i;
		}	
	}
	
	
	
	/********************************
	* Check the sequence of $start and $end
	* Why: The photos on the source page are sorted from newest to oldest. This may confuse users.
	********************************/
	if ($start > $end) {
		$t = $start;
		$start = $end;
		$end = $t;
	}
	
	
	
	
	/********************************
	* Count the step between each frame
	********************************/
	$totalFrame = $end - $start;
	if ($totalFrame > $MAX_FRAME) {
		$totalFrame = $MAX_FRAME;
		$step = round(($end - $start) / $totalFrame);
	} else
		$step = 1;
	


	/********************************
	* Check interval
	********************************/
	if (!$interval)
		$interval = $DEF_INTERVAL;
				
		
	
	
	/********************************
	* Make GIF
	********************************/
	for($i = $end; $i > $start ; $i -= $step){
			$imgname = $PHOTO_LOCATION.$photoFilenames[$i][1];
			$im = @imagecreatefromjpeg($imgname);		// Convert the original JPG file to GIF
			imagegif($im, "images/".$i.".gif");
			$totalFrames[] = "images/".$i.".gif";		// Add GIF to frames array
			$time[] = $interval; 			
		}
		
		
		
	/********************************	
	* Add the last frame
	********************************/
	$imgname = $PHOTO_LOCATION.$photoFilenames[$start][1];
	$im = @imagecreatefromjpeg($imgname);
	imagegif($im, "images/".$i.".gif");
	$totalFrames[] = "images/".$i.".gif";
	$time[] = $interval;
	
	
	
	/********************************
	/ Call encoder
	********************************/
	$gif = new GIFEncoder	(
		$totalFrames, 	// totalFrames array
		$time, 		// elapsed time array
		0, 		// loops (0 = infinite)
		2, 		// disposal
		0, 0, 0, 	// rgb of transparency
		"url" 		// source type
	);
	
	
	
	
	/********************************
	/ Save and display the image
	********************************/
	$file = "new.gif";
	$handle = fopen($file, 'w');
	fwrite($handle, $gif->GetAnimation());
	echo "<html><body><img src=\"new.gif\" /></body></html>";




	/********************************
	/ Clean
	********************************/
	for($i = $end; $i > $start ; $i -= $step){
		@unlink("images/".$i.".gif");
	}

?>