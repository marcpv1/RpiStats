<?php
namespace lib;

class Storage {
  
  public static function hdd() {

    $result = array();

    exec('df -T | grep -vE "tmpfs|rootfs|Filesystem"', $drivesarray);
    
    for ($i=0; $i<count($drivesarray); $i++) {
      $drivesarray[$i] = preg_replace('!\s+!', ' ', $drivesarray[$i]);
      preg_match_all('/\S+/', $drivesarray[$i], $drivedetails);
      list($fs, $type, $size, $used, $available, $percentage, $mounted) = $drivedetails[0];
        
      $result[$i]['name'] = $mounted;
      $result[$i]['total'] = self::kConv($size);
      $result[$i]['free'] = self::kConv($available);
      $result[$i]['used'] = self::kConv($size - $available);
      $result[$i]['format'] = $type;
      
      $result[$i]['percentage'] = rtrim($percentage, '%');

      if($result[$i]['percentage'] > '80')
        $result[$i]['alert'] = 'warning';
      else
        $result[$i]['alert'] = 'success';
    }

    return $result;
  }
  
  public static function kConv($kSize){
    $unit = array('K', 'M', 'G', 'T');
    $i = 0;
    $size = $kSize;
    while($i < 3 && $size > 1024){
      $i++;
      $size = $size / 1024;
    }
    return round($size, 2).$unit[$i];
  }

}


function icon_alert($alert) {
  echo '<i class="icon-';
  switch($alert) {
    case 'success':
      echo 'ok';
      break;
    case 'warning':
      echo 'warning-sign';
      break;
    default:
      echo 'exclamation-sign';
  }
  echo '"></i>';
}

function shell_to_html_table_result($shellExecOutput) {
	$shellExecOutput = preg_split('/[\r\n]+/', $shellExecOutput);

	// remove double (or more) spaces for all items
	foreach ($shellExecOutput as &$item) {
		$item = preg_replace('/[[:blank:]]+/', ' ', $item);
		$item = trim($item);
	}

	// remove empty lines
	$shellExecOutput = array_filter($shellExecOutput);

	// the first line contains titles
	$columnCount = preg_match_all('/\s+/', $shellExecOutput[0]);
	$shellExecOutput[0] = '<tr><th>' . preg_replace('/\s+/', '</th><th>', $shellExecOutput[0], $columnCount) . '</th></tr>';
	$tableHead = $shellExecOutput[0];
	unset($shellExecOutput[0]);

	// others lines contains table lines
	foreach ($shellExecOutput as &$item) {
		$item = '<tr><td>' . preg_replace('/\s+/', '</td><td>', $item, $columnCount) . '</td></tr>';
	}

	// return the build table
	return '<table class=\'table table-striped\'>'
				. '<thead>' . $tableHead . '</thead>'
				. '<tbody>' . implode($shellExecOutput) . '</tbody>'
			. '</table>';
}

$hdd = Storage::hdd();
?>
<link href="bootstrap.min.css" rel="stylesheet" media="screen" />
<link href="bootstrap-responsive.min.css" rel="stylesheet" />
<link href="raspcontrol.css" rel="stylesheet" media="screen" />

<html>
<script type="text/javascript" 
src="ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script type="text/javascript">
function apagartorrent() {
    $.get("stopt.php");
    return false;
}
</script>
<head>
<link rel="shortcut icon" href="img/favicon.ico" />
<title>Raspberry Pi 3 Stats</title>
</head>
<body>
<div id="content">
<h1>Raspberry Pi 3</h1>
<p></p>
<br/>
<h2>HDD space</h2>

 

<table>
<tr class="storage" id="check-storage">
           
            <?php
              for ($i=1; $i<sizeof($hdd); $i++) { 
			  echo '
            <td class="infos">
              <i class="icon-folder-open"></i> ', $hdd[$i]['name'] , '
              <div class="progress">
                <div class="bar bar-', $hdd[$i]['alert'], '" style="width: ', $hdd[$i]['percentage'], '%;">', $hdd[$i]['percentage'], '%</div>
              </div>
              free: <span class="text-success">', $hdd[$i]['free'], 'b</span> &middot; used: <span class="text-warning">', $hdd[$i]['used'], 'b</span> &middot; total: ', $hdd[$i]['total'], 'b &middot; format: ', $hdd[$i]['format'], '
              <br><br><br>
			  
			</td>
          </tr>
          ', ($i == sizeof($hdd)-1) ? null : '<tr class="storage">';
              }
           ?>
		   </table>

<?php

echo "<h2><p>Uptime</p></h2>";
exec("uptime -p",$output2);

foreach($output2 as $out2){
echo $out2;
}

echo "<br/><br/><h2><p>Internal temperature</p></h2>";

exec("cat /sys/class/thermal/thermal_zone0/temp",$output2);
foreach($output2 as $out2){
$res=$out2/1000;

if ($res!="0") echo $res . " C";
}

echo "<br/><br/><h2><p>Date</p></h2>";

exec("date",$output3);
foreach($output3 as $out3){
echo $out3;
}


//$pReboot=$_POST[botoReboot];
//if ($pReboot=="REINICIAR RASPBERRY")
//{
//	$ret=system('sudo reboot',$ret_val);
//}

?>

</p>
</div>
</body>
</html>
