<?php
/********************************************************************
config.php handles setting of Global variables, GET, and POST requests
It also houses the functions for POST requests.

All other config functions are in ./include/config-functions.php

********************************************************************/

require('./include/global-vars.php');
require('./include/global-functions.php');
require('./include/menu.php');
require('./include/config-functions.php');

load_config();
ensure_active_session();
action_topmenu();


/************************************************
*Constants                                      *
************************************************/


/************************************************
*Global Variables                               *
************************************************/
$page = 1;
$searchbox = '';
$showblradio = false;
$blradio = 'all';
$db = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);

/************************************************
*Arrays                                         *
************************************************/
$list = array();                                 //Global array for all the Block Lists

/************************************************
*POST REQUESTS                                  *
************************************************/
//Deal with POST actions first, that way we can reload the page and remove POST requests from browser history.
if (isset($_POST['action'])) {
  switch($_POST['action']) {
    case 'advanced':
      if (update_advanced()) {                   //Are users settings valid?
        save_config();                           //If ok, then save the Config file        
        sleep(1);                                //Short pause to prevent race condition
      }      
      header('Location: ?v=advanced');           //Reload page
      break;
    case 'blocklists':
      update_blocklist_config();
      save_config();
      exec(NTRK_EXEC.'--run-notrack');
      $mem->delete('SiteList');                  //Delete Site Blocked from Memcache
      sleep(1);                                  //Short pause to prevent race condition
      header('Location: ?v=blocks');             //Reload page
      break;
    case 'webserver':      
      update_webserver_config();
      save_config();      
      header('Location: ?');
      break;    
    case 'stats':
      if (update_stats_config()) {
        save_config();        
        sleep(1);                                //Short pause to prevent race condition
        header('Location: ?');
      }
      break;
    case 'tld':
      load_csv($CSVTld, 'CSVTld');               //Load tld.csv
      update_domain_list();      
      sleep(1);                                  //Prevent race condition
      header('Location: ?v=tld');                //Reload page
      break;
    default:      
      die('Unknown POST action');
  }
}
//-------------------------------------------------------------------
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <link href="./css/master.css" rel="stylesheet" type="text/css" />
  <link rel="icon" type="image/png" href="./favicon.png" />
  <script src="./include/config.js"></script>
  <script src="./include/menu.js"></script>
  <title>NoTrack - Config</title>  
</head>

<body>
<?php
action_topmenu();
draw_topmenu();
draw_sidemenu();
echo '<div id="main">';


/********************************************************************
 *  Update Advanced Config
 *    1. Make sure Suppress list is valid
 *    1a. Replace new line and space with commas
 *    1b. If string too short, set to '' then leave
 *    1c. Copy Valid URL's to a ValidList array
 *    1d. Write valid URL's to Config Suppress string seperated by commas 
 *  Params:
 *    None
 *  Return:
 *    None
 */
function update_advanced() {
  
  global $Config;
  
  $suppress = '';
  $suppresslist = array();
  $validlist = array();
  if (isset($_POST['suppress'])) {
    $suppress = preg_replace('#\s+#',',',trim($_POST['suppress'])); //Split array
    if (strlen($suppress) <= 2) {             //Is string too short?
      $Config['Suppress'] = '';
      return true;
    }
    
    $suppresslist = explode(',', $suppress);  //Split string into array
    foreach ($suppresslist as $site) {           //Check if each item is a valid URL
      if (Filter_URL_Str($site)) {
        $validlist[] = $site;
      }
    }
    if (sizeof($validlist) == 0) $Config['Suppress'] = '';
    else $Config['Suppress'] = implode(',', $validlist);
  }
  
  return true;
}


/********************************************************************
 *  Update Block List Config
 *    Read and Filter values parsed from HTTP POST into the Config array  
 *    After this function save_config is run
 *  Params:
 *    None
 *  Return:
 *    None
 */
function update_blocklist_config() {  
  global $Config;
  $customstr = '';
  $customlist = array();
  $validlist = array();
    
  $Config['bl_notrack'] = filter_config('bl_notrack');
  $Config['bl_tld'] = filter_config('bl_tld');
  $Config['bl_qmalware'] = filter_config('bl_qmalware');
  $Config['bl_hexxium'] = filter_config('bl_hexxium');
  $Config['bl_cedia'] = filter_config('bl_cedia');
  $Config['bl_cedia_immortal'] = filter_config('bl_cedia_immortal');
  $Config['bl_disconnectmalvertising'] = filter_config('bl_disconnectmalvertising');
  $Config['bl_easylist'] = filter_config('bl_easylist');
  $Config['bl_easyprivacy'] = filter_config('bl_easyprivacy');
  $Config['bl_fbannoyance'] = filter_config('bl_fbannoyance');
  $Config['bl_fbenhanced'] = filter_config('bl_fbenhanced');
  $Config['bl_fbsocial'] = filter_config('bl_fbsocial');
  $Config['bl_hphosts'] = filter_config('bl_hphosts');
  $Config['bl_malwaredomainlist'] = filter_config('bl_malwaredomainlist');
  $Config['bl_malwaredomains'] = filter_config('bl_malwaredomains');  
  $Config['bl_pglyoyo'] = filter_config('bl_pglyoyo');
  $Config['bl_someonewhocares'] = filter_config('bl_someonewhocares');  
  $Config['bl_spam404'] = filter_config('bl_spam404');
  $Config['bl_swissransom'] = filter_config('bl_swissransom');
  $Config['bl_swisszeus'] = filter_config('bl_swisszeus');
  $Config['bl_winhelp2002'] = filter_config('bl_winhelp2002');
  $Config['bl_areasy'] = filter_config('bl_areasy');
  $Config['bl_chneasy'] = filter_config('bl_chneasy');
  $Config['bl_deueasy'] = filter_config('bl_deueasy');
  $Config['bl_dnkeasy'] = filter_config('bl_dnkeasy');
  $Config['bl_ruseasy'] = filter_config('bl_ruseasy');
  $Config['bl_fblatin'] = filter_config('bl_fblatin');
  
  if (isset($_POST['bl_custom'])) {    
    $customstr = preg_replace('#\s+#',',',trim($_POST['bl_custom'])); //Split array
    $customlist = explode(',', $customstr);      //Split string into array
    foreach ($customlist as $site) {             //Check if each item is a valid URL
      if (Filter_URL_Str($site)) {
        $validlist[] = $site;
      }
    }
    if (sizeof($validlist) == 0) $Config['bl_custom'] = '';
    else $Config['bl_custom'] = implode(',', $validlist);
  }
  else {
    $Config['bl_custom'] = "";
  }
    
  return null;
}


/********************************************************************
 *  Update Custom List
 *    Works for either BlackList or WhiteList
 *    1. Appropriate list should have already have been loaded into $list Array
 *    2. Find out what value has been requested on GET &do=
 *    2a. Add Site using site name, and comment to end of $list array
 *    2b. Change whether Site is enabled or disabled using Row number of $list array
 *    2c. Delete Site from $list array by using Row number
 *    2d. Change whether Site is enabled or disabled using name
 *    2e. Error and leave function if any other value is given
 *    3. Open File for writing in /tmp/"listname".txt
 *    4. Write $list array to File
 *    5. Delete $list array from Memcache
 *    6. Write $list array with changes from #2 to Memcache
 *    7. Run ntrk-exec as Forked process
 *    8. Onward process is to show_custom_list function
 *
 *  Params:
 *    Actual Name, List name
 *  Return:
 *    True when action carried out
 */
function update_custom_list($actualname, $listname) {
  global $list, $mem;
  $row = 0;
  
  if (isset($_GET['do'])) {
    switch ($_GET['do']) {
      case 'add':                                //Add Site
        if ((isset($_GET['site'])) && (isset($_GET['comment']))) {
          if (filter_url($_GET['site'])) {
            $list[] = array($_GET['site'], strip_tags($_GET['comment']), true);
          }
        }
        break;
      case 'cng':
        if ((isset($_GET['row'])) && (isset($_GET['status']))) {
          $row = filter_integer($_GET['row'], 1, count($list)+1);
          if ($row > 0) {                        //Is integer valid?
            $row--;                              //Compensate table value to array position
            $list[$row][2] = filter_bool($_GET['status']);
          }
        }      
        break;
      case 'del':
        if (isset($_GET['row'])) {
          $row = filter_integer($_GET['row'], 1, count($list)+1);
          if ($row > 0) {                        //Is integer valid?
            array_splice($list, $row-1, 1);      //Remove one line from List array
          }
        }             
       break;
      case 'update':
        echo '<pre>Updating Custom blocklists in background</pre>'.PHP_EOL;
        exec(NTRK_EXEC.'--run-notrack');        
        return true;
        break;    
      default:
        echo 'Invalid request in update_custom_list()'.PHP_EOL;
        return false;
    }
  }
  else {
    echo 'No request specified in update_custom_list()'.PHP_EOL;
    return false;
  }
  
  //Open file /tmp/listname.txt for writing
  $fh = fopen(DIR_TMP.strtolower($actualname).'.txt', 'w') or die('Unable to open '.DIR_TMP.$actualname.'.txt for writing');
  
  //Write Usage Instructions to top of File
  fwrite($fh, "#Use this file to create your own custom ".$actualname.PHP_EOL);
  fwrite($fh, '#Run notrack script (sudo notrack) after you make any changes to this file'.PHP_EOL);
  
  foreach ($list as $line) {                     //Write list array to temp
    if ($line[2] == true) {                      //Is site enabled?
      fwrite($fh, $line[0].' #'.$line[1].PHP_EOL);
    }
    else {                                       //Site disabled, comment it out by preceding Line with #
      fwrite($fh, '# '.$line[0].' #'.$line[1].PHP_EOL);
    }    
  }
  fclose($fh);                                   //Close file
  
  $mem->delete($listname);
  $mem->set($listname, $list, 0, 60);
  
  exec(NTRK_EXEC.'--copy '.$listname);
  
  return true;
}


/********************************************************************
 *  Update Stats Config
 *
 *  Params:
 *    None
 *  Return:
 *    True if change has been made or False if nothing changed
 */
function update_stats_config() {
  global $Config, $SEARCHENGINELIST, $WHOISLIST;
  
  $updated = false;
  print_r($_POST);
  if (isset($_POST['search'])) {
    if (array_key_exists($_POST['search'], $SEARCHENGINELIST)) {      
      $Config['Search'] = $_POST['search'];
      $Config['SearchUrl'] = $SEARCHENGINELIST[$Config['Search']];
      $updated = true;
    }
  }
  
  if (isset($_POST['whois'])) {    
    if (array_key_exists($_POST['whois'], $WHOISLIST)) {
      $Config['WhoIs'] = $_POST['whois'];
      $Config['WhoIsUrl'] = $WHOISLIST[$Config['WhoIs']];
      $updated = true;
    }
  }
  
  return $updated;
}


/********************************************************************
 *  Update Domian List
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function update_domain_list() {
  global $list, $mem;
  
  //Start with White List
  $fh = fopen(DIR_TMP.'domain-whitelist.txt', 'w') or die('Unable to open '.DIR_TMP.'domain-whitelist.txt for writing');
  
  fwrite($fh, '#Domain White list generated by config.php'.PHP_EOL);
  
  foreach ($list as $site) {                     //Generate White list based on unticked Risk 1 items
    if ($site[2] == 1) {
      if (! isset($_POST[substr($site[0], 1)])) { //Check POST for domain minus preceding .
        fwrite($fh, $site[0].PHP_EOL);           //Add domain to White list
      }
    }
  }
  fclose($fh);                                   //Close White List
  
  //Write Black List
  $fh = fopen(DIR_TMP.'domain-blacklist.txt', 'w') or die('Unable to open '.DIR_TMP.'domain-blacklist.txt for writing');
    
  fwrite($fh, '#Domain Block list generated by config.php'.PHP_EOL);
  fwrite($fh, '#Do not make any changes to this file'.PHP_EOL);
  
  foreach ($_POST as $Key => $Value) {           //Generate Black list based on ticked items in $_POST
    if ($Value == 'on') fwrite($fh, '.'.$Key.PHP_EOL); //Add each item of POST of value is "on"
  }
  fclose($fh);                                   //Close Black List
  
  exec(NTRK_EXEC.'--copy tld');
      
  $mem->delete('TLDBlackList');                  //Delete Black List from Memcache
  $mem->delete('TLDWhiteList');                  //Delete White List from Memcache
  
  return null;
}


/********************************************************************
 *  Update Webserver Config
 *    Run ntrk-exec with appropriate change to Webserver setting
 *    Onward process is save_config function
 *  Params:
 *    None
 *  Return:
 *    None
 */
function update_webserver_config() {
  global $Config;  
  
  if (isset($_POST['block'])) {
    switch ($_POST['block']) {
      case 'message':
        $Config['BlockMessage'] = 'message';
        exec(NTRK_EXEC.'--bm-msg');
        break;
      case 'pixel':
        $Config['BlockMessage'] = 'pixel';
        exec(NTRK_EXEC.'--bm-pxl');
        break;      
    }
  }
}

//Main---------------------------------------------------------------

/************************************************
*GET REQUESTS                                   *
************************************************/
if (isset($_GET['s'])) {                         //Search box
  //Allow only characters a-z A-Z 0-9 ( ) . _ - and \whitespace
  $searchbox = preg_replace('/[^a-zA-Z0-9\(\)\.\s\_\-]/', '', $_GET['s']);
  $searchbox = strtolower($searchbox);  
}

if (isset($_GET['page'])) {
  $page = filter_integer($_GET['page'], 1, PHP_INT_MAX, 1);
}

if (isset($_POST['showblradio'])) {
  if ($_POST['showblradio'] == 1) {
    $showblradio = true;
  }
}

if (isset($_GET['blrad'])) {
  if ($_GET['blrad'] == 'all') {
    $blradio = 'all';
    $showblradio = true;
  }
  elseif (array_key_exists($_GET['blrad'], $BLOCKLISTNAMES)) {
    $blradio = $_GET['blrad'];
    $showblradio = true;
  }
}

if (isset($_GET['action'])) {
  switch($_GET['action']) {
    case 'delete-history':
      exec(NTRK_EXEC.'--delete-history');
      show_general();
      break;
    case 'black':
      load_customlist('black', $FileBlackList);
      update_custom_list('BlackList', 'black');      
      break;
    case 'white':
      load_customlist('white', $FileWhiteList);
      update_custom_list('WhiteList', 'white');
      break;    
  }
}

if (isset($_GET['v'])) {                         //What view to show?
  switch($_GET['v']) {
    case 'config':
      show_general();
      break;
    case 'blocks':
      show_blocklists();
      break;
    case 'black':
      load_customlist('black', $FileBlackList);
      show_custom_list('black');
      break;
    case 'white':
      load_customlist('white', $FileWhiteList);
      show_custom_list('white');
      break;
    case 'tld':
      load_csv($CSVTld, 'csv_tld');
      show_domain_list();     
      break;
    case 'full':      
      show_full_blocklist();
      break;
    case 'advanced':
      show_advanced();
      break;
    case 'status':
      show_status();
      break;
    default:
      show_general();
      break;
  }
}
else {                                           //No View set
  show_menu();
}

$db->close();
?> 
</div>
</body>
</html>
