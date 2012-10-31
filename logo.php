<?php
//<img id="thImg" src="http://ts4.mm.bing.net/th?id=H.5041252958995407&amp;pid=15.1" class="hide">

$scraped =  file_get_contents('http://m.bing.com/images/more?&ii=0&dv=True&q=' . urlencode('starbucks logo'));

preg_match('/thImg" src="(.*)"\/><\/div><div class/',$scraped,$matches);

error_log(print_r($matches[1],true));
//exit;

if(isset($matches[1])) echo $matches[1];

?>