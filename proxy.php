<?php

// ip route add default via 192.168.141.2

require_once("cb.php");

$fakey = array('search','match','proven','mechanical','engineer','ball','micro','conductor','media','marketing','computer','tech','systems','switch','process','programmer','creative','chemical','science','javascript','department','application','resources');
$rand = rand(0,count($fakey)-1);

//$_GET = 'organization'=>'Proctor';
$values = $_GET;
$organization = $values['organization'];
$orgid = $values['orgid'];

error_log($organization);

//$results =  CBAPI::getJobResults('javascript','Chicago', 'US', 1);  
//dump($results,1);
//exit;

$fake = false;
$results = request($organization);
//dump($results,1);
//exit;

$fakeit = false;
if(!$results && $fakeit)
{
  $fake = true;
  $results = request($rand,true);
  error_log('faking');
}

/**
$company = $results[0]->companyName;
//echo $company;exit;
if(strtolower($company) != strtolower($organization))
{
  $fake = true;
  $results = request($rand,true);
}
**/

//dump($results,1);

$response = parse($results,$organization,$fake);
if(isset($_GET['dev'])) dump($response,1);
respond($response,$orgid);

/**
 * request CB data
 *
 */
function request($organization,$keyword=false)
{
  if($keyword) return CBAPI::getJobResults($organization,'', 'US', 1); 
  return CBAPI::getJobResultsCompany($organization);  
}


/**
 * parse response
 *
 */
function parse($results,$organization,$fake)
{
  $jobs = array();
  $logo = NULL;
  foreach($results AS $result)
  {
    $data = array();
    $data['did'] = $result->did;
    $data['company'] = $organization;
    $data['title'] = $result->title;
    $data['city'] = $result->city;
    $pay = $result->pay;
    $data['pay'] = (strtolower($pay) == 'n/a') ? NULL : $pay; 
    $data['state'] = $result->state;
    $data['location'] = $result->location;
    $data['url'] = $result->url;
    $data['logo'] = $result->logo;
    
    if(!$logo && $data['logo']) $logo = $data['logo'];
    
    $desc = $result->description;
    
    if($fake)
    {
      $company = $result->companyName;
      $desc = str_replace($company,$organization,$result->description); 
      $desc = swapper($desc,$company,$organization,' ');
      $desc = swapper($desc,$company,$organization,',');
    }
    
    //echo $desc . ' :::' . $company . ' :::: ' . $organization;exit;
    
    $data['description'] = $desc;
    $data['posted'] = $result->posted;
    $jobs[] = $data; 
  }  
 
  if(!$logo) $logo = scrape_logo($organization);
  
  $response = array(
                    'logo'=>$logo,
                    'jobs'=>$jobs,
                  );
  
  return $response; 
}

function swapper($desc,$name,$organization,$delim)
{
  $parts = explode($delim,$name);
  $swap = $desc;
  foreach($parts AS $p)
  {
    $swap = str_replace($p,$organization,$swap);  
  }
  return $swap;
}



function respond($response,$orgid)
{
  $found = ($response['jobs']) ? true : false;
  if($found) error_log('found');
  $json = 'circles(' . json_encode(array('found'=>$found,'logo'=>$response['logo'],'jobs'=>$response['jobs'],'orgid'=>$orgid)) . ')';  
  echo $json;
}

function scrape_logo($name)
{
  $scraped =  file_get_contents('http://m.bing.com/images/more?&ii=0&dv=True&q=' . urlencode($name . ' logo'));

  preg_match('/thImg" src="(.*)"\/><\/div><div class/',$scraped,$matches);

  //error_log(print_r($matches[1],true));
  //exit;

  return (isset($matches[1])) ? $matches[1] : NULL;
}



/**
 * quick dump
 *
 */
function dump($val,$abort=false)
{  
  ini_set('highlight.html','#007700');
  $output = highlight_string(print_r($val,true),true);
  echo $output;
  if($abort) 
  {
    echo 'Dump Abort';
    exit;
  }  
}
