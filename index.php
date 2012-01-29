<?php

$version = '0.2';
$build = 'afb998';

$versioning = 'Version: '.$version.' ('.$build.')';

require('mysql.php'); 

if(!$link) {
    die('Keine Verbindung: '.mysql_error());
};

// Auswählen der Datenbank
$db_selected = mysql_select_db('d0131787', $link);


if(!$db_selected) {
    die ('Kann Datenbank nicht nutzen: ' .mysql_error());
};
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Blutdruck</title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<meta name="apple-mobile-web-app-capable" content="yes" /> 
<meta name="viewport" content="width = device-width, user-scalable=no">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link rel="apple-touch-icon" href="http://blut.aaronbauer.org/apple-touch-icon.png"/>

<style type="text/css">
	body {
		font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
		font-weight: lighter;
	}
	
	#wrap {
		width:480px;
		margin-left: auto;
		margin-right: auto;
	}
	input {
		font-size: 35pt;
	}
	
	.button {
		font-size: 10pt;
	}
	
	table {		
		border: 1pt solid black;
		text-align: center;
		padding: 3px;
	}
	
	td {
		border: 1pt solid grey;
		padding: 10px;
	}
	
</style>
</head>
<div id="wrap">
<h1><a href="index.php">Blutdruck</a></h1>
<?php 
# Erster Schritt: Name
if ($_GET['name']=='' and $_POST['dia']=='' and $_POST['sys']=='' and $_GET['page']=='') {

	$read_query = 'SELECT * FROM blut ORDER BY id DESC';
    $exec_read = mysql_query($read_query) or die(mysql_error());
    $data = mysql_fetch_array($exec_read) or die(mysql_error());
    
    
    echo 'Hallo. Zuletzt gemessen hat hier <b>'.$data['name'].'</b> am '.$data['timestamp'].'.';
    
	echo '<h2>1. Wer bist du?</h2>';
	echo '<p>Sag mir wer du bist, damit ich alles richtig eintragen kann</p>';
	echo '<form action="index.php" method="get">
<select name="name">
  <option>Verena</option>
  <option>Franz</option>
  <option>Leander</option>
  <option>Aaron</option>
</select>
 
<input type="submit" value="Weiter" class="button" />
</form>';

    
    
}
# Zweiter Schritt: Blutdruck
if($_GET['name']!='' and $_POST['dia']=='' and $_POST['sys']=='' and $_GET['page']=='') {

echo '<h2>2. Deine Messung.</h2>';
echo 'Hallo '.$_GET['name'].'!';

	$read_query = 'SELECT AVG(dia), AVG(sys) FROM blut WHERE name="'.$_GET['name'].'"';
    $exec_read = mysql_query($read_query) or die(mysql_error());
    $data = mysql_fetch_array($exec_read) or die(mysql_error());
    
    echo '<p>Du hast zu letzt am <b>'.$data['timestamp'].'</b> gemessen! Das ist lange her.</p>';
    echo '<p>Dein durchschnittlicher Blutdruck liegt bei '.round($data['AVG(sys)']).'/'.round($data['AVG(dia)']).'.';
    
    $history_query = 'SELECT * FROM blut WHERE name="'.$_GET['name'].'" ORDER BY id DESC';
    $history_read = mysql_query($history_query) or die (mysql_error());
    
    echo '<table><tr><td>Dia</td><td>Sys</td><td>Zeit</td></tr>';
    while ($row = mysql_fetch_assoc($history_read)) {
    echo '<tr>';	
    echo '<td>'.$row["dia"].'</td>';
    echo '<td>'.$row["sys"].'</td>';
    echo '<td>'.date("H:i - d.m.Y",strtotime($row["timestamp"])).'</td>';
	echo '</tr>';
    }
    
    echo '</tr></table>';
 
    
    
	echo '<p>Wie ist dein Blutdruck heute?</p>';	
	echo '<form action="index.php" method="post">
<input type="text" size="3" maxlenght="3" name="sys" /> Sys in mm Hg <br />
<input type="text" size="3" maxlenght="3" name="dia" /> Dia in mm Hg <br />
<input type="hidden" name="name" value="'.$_GET['name'].'" />
	<p><b>Hast du auch wirklich alles richtig eingegeben? Wenn ja, dann drücke auf <i>"weiter"</i>.</b></p> <br />
<input type="submit" value="Weiter" class="button" />
</form>';
}
# Dritter Schritt: Fertig. Name und Blutdruck anzeigen.
if ($_POST['name']!='' and $_POST['dia']!='' and $_POST['sys']!='' and $_GET['page']=='') {
	echo '<h2>Danki!</h2>';
	echo 'Vielen Dank '.$_POST['name'].'! Dein Blutdruck ist '.$_POST['sys'].'/'.$_POST['dia'];
	echo ' <b>Du kannst die App jetzt beenden.</b>';
	
	$write_query = 'INSERT INTO blut (sys, dia, name) VALUES ('.$_POST['sys'].', "'.$_POST['dia'].'", "'.$_POST['name'].'");';
	$exec_write = mysql_query($write_query) or die(mysql_error());
    echo ' Alles paletti!';
    
    

}

if($_GET['page']=='info') {
	$read_query = 'SELECT AVG(dia), AVG(sys) FROM blut';
    $exec_read = mysql_query($read_query) or die(mysql_error());
    $data = mysql_fetch_array($exec_read) or die(mysql_error());
    echo '<p>Durchschnitt (Dia in mm Hg): '.$data['AVG(dia)'].'</p>';
    echo '<p>Durchschnitt (Sys in mm Hg): '.$data['AVG(sys)'].'</p>';
	include ('info.php');
}

if($_GET['page']=='statistics') {
	$read_query = 'SELECT STDDEV(dia), AVG(dia), STDDEV(sys), AVG(sys), COUNT(*) FROM blut';
    $exec_read = mysql_query($read_query) or die(mysql_error());
    $data = mysql_fetch_array($exec_read) or die(mysql_error());
    
    $toplist_dia_query = 'SELECT * FROM blut ORDER BY dia DESC LIMIT 5';
    $exec_toplist_dia = mysql_query($toplist_dia_query) or die (mysql_error());
    
    $toplist_sys_query = 'SELECT * FROM blut ORDER BY sys DESC LIMIT 5';
    $exec_toplist_sys = mysql_query($toplist_sys_query) or die (mysql_error());
        
    $se_dia = $data['STDDEV(dia)']/sqrt($data['COUNT(*)']);
    $ci_dia_high = $data['AVG(dia)']+1.96*$se_dia;
    $ci_dia_low = $data['AVG(dia)']-1.96*$se_dia;
    
    $ci_dia_mean = ($ci_dia_high+$ci_dia_low)/2;
    
    $se_sys = $data['STDDEV(sys)']/sqrt($data['COUNT(*)']);
    $ci_sys_high = $data['AVG(sys)']+1.96*$se_sys;
    $ci_sys_low = $data['AVG(sys)']-1.96*$se_sys;
    
    $ci_sys_mean = ($ci_sys_high+$ci_sys_low)/2;
    
    echo '<p>Anzahl der Messwerte: '.$data['COUNT(*)'].'</p>';
       
    echo '<h2>Diatolisch</h2>'; 
    
    echo '<p>Die fünf höchsten Messwerte:</p>';
    echo '<table><tr><td>Dia</td><td>Name</td><td>Zeit</td></tr>';
    while ($row_dia = mysql_fetch_assoc($exec_toplist_dia)) {
    echo '<tr>';	
    echo '<td>'.$row_dia["dia"].'</td>';
    echo '<td>'.$row_dia["name"].'</td>';
    echo '<td>'.date("H:i - d.m.Y",strtotime($row_dia["timestamp"])).'</td>';
	echo '</tr>';
    }    
    echo '</tr></table>';
    
    echo '<p>Durchschnitt (Dia in mm Hg): '.$data['AVG(dia)'].'</p>'; 
    echo '<p>Standardabweichung (Dia in mm Hg): '.$data['STDDEV(dia)'].'</p>'; 
    echo '<p>Standardfehler: '.$se_dia.' (Dia in mm Hg)<br /></p>';
    echo '<p>95% Konfidenzintervall: ['.$ci_dia_low.'; '.$ci_dia_high.'] (Dia in mm Hg)</p>';
    
    echo '<h2>Systolisch</h2>';
 
    echo '<p>Die fünf höchsten Messwerte:</p>';
    echo '<table><tr><td>Sys</td><td>Name</td><td>Zeit</td></tr>';
    while ($row_sys = mysql_fetch_assoc($exec_toplist_sys)) {
    echo '<tr>';	
    echo '<td>'.$row_sys["sys"].'</td>';
    echo '<td>'.$row_sys["name"].'</td>';
    echo '<td>'.date("H:i - d.m.Y",strtotime($row_sys["timestamp"])).'</td>';
	echo '</tr>';
    }    
    echo '</tr></table>';
    
    echo '<p>Durchschnitt (Sys in mm Hg): '.$data['AVG(sys)'].'</p>';
    echo '<p>Standardabweichung (Sys in mm Hg): '.$data['STDDEV(sys)'].'</p>';
    echo '<p>Standardfehler: '.$se_sys.' (Sys in mm Hg)<br /></p>';
    echo '<p>95% Konfidenzintervall: ['.$ci_sys_low.'; '.$ci_sys_high.'] (Sys in mm Hg)</p>';
   
}

?>
<p><a href="?page=info">Hier</a> erfährst du mehr über deinen Blutdruck.</p>
<p><a href="?page=statistics">Statistik</a></p>
<p><?php echo $versioning; ?></p>
</div>
</html>

<?php mysql_close($link); ?>
