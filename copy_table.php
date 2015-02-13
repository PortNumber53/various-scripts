#!/usr/local/bin/php
<?php
//Copy table data

require "config/config.php";

/***************************************************************************/

$link = mysql_connect($host, $username, $password);
if (!$link) {
    die('Not connected : ' . mysql_error());
}

// make foo the current db
$db_selected = mysql_select_db($database, $link);
if (!$db_selected) {
    die ('Can\'t use ' . $database . ' : ' . mysql_error());
}

$slave_conn = mysql_connect($slave['host'], $slave['username'], $slave['password']);
if (!$slave) {
    die('Not connect to slave :' . mysql_error($slave_conn));
}
$slave_db_selected = mysql_select_db($slave['database'], $slave_conn);
if (!$slave_db_selected) {
    die ('Can\'t use ' . $slave['database'] . ' : ' . mysql_error($slave_conn));
}





$params = $_SERVER['argv'];


$action = $params[1];
$table_name = $params[2];
$primary_key = empty($params[3]) ? '' : $params[3];

function define_trigger($table_name, $fields)
{
	$insert_trigger_name = "sync_new_".$table_name."_insert";
	$update_trigger_name = "sync_new_".$table_name."_update";
	$delete_trigger_name = "sync_new_".$table_name."_delete";
	$output = "";
	$output .= "\n";

	$field_lines = '';
	foreach ($fields as $name)
	{
		$field_lines .= "    $name = NEW.$name,\n";
	}
	$field_lines = substr($field_lines, 0, -2) . "\n";

	$output .= "DROP TRIGGER IF EXISTS $insert_trigger_name;\n";
	$output .= "DROP TRIGGER IF EXISTS $update_trigger_name;\n";
	$output .= "DROP TRIGGER IF EXISTS $delete_trigger_name;\n";

	$output .= "delimiter |\n";
	$output .= "CREATE trigger $insert_trigger_name AFTER INSERT ON $table_name\n";
	$output .= "  FOR EACH ROW BEGIN\n";
	$output .= "    INSERT INTO new_".$table_name." SET\n";
	$output .= $field_lines . ";\n";
	$output .= "  END;\n";
	$output .= "|\n";
	$output .= "delimiter ;\n";
	$output .= "\n";

	$output .= "delimiter |\n";
	$output .= "CREATE trigger $update_trigger_name AFTER UPDATE ON $table_name\n";
	$output .= "  FOR EACH ROW BEGIN\n";
	$output .= "    INSERT INTO new_".$table_name." SET\n";
	$output .= $field_lines;
	$output .= "  ON DUPLICATE KEY UPDATE\n";
	//$output .= "    UPDATE new_".$table_name." SET\n";
	$output .= $field_lines. "\n";
	//$output .= "    WHERE\n";
	//$output .= "    ".$fields[0]."=NEW.".$fields[0]."\n";
	$output .= ";\n";
	$output .= "  END;\n";
	$output .= "|\n";
	$output .= "delimiter ;\n";
	$output .= "\n";

	$output .= "delimiter |\n";
	$output .= "CREATE TRIGGER $delete_trigger_name BEFORE DELETE ON $table_name\n";
	$output .= "  FOR EACH ROW BEGIN\n";
	$output .= "    DELETE FROM new_".$table_name." WHERE\n";
	$output .= "    ".$fields[0]."=OLD.".$fields[0].";\n";
	$output .= "  END;\n";
	$output .= "|\n";
	$output .= "delimiter ;\n";
	$output .= "\n";
	$output .= "\n";

	return $output;
}

function create_table($table_name)
{
	$output = "";
	$output .= "CREATE TABLE new_$table_name LIKE $table_name;\n";
	$output .= "ALTER TABLE new_$table_name CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
	return $output;
}

function switch_tables($table_name, $primary_key)
{
	$sql = "SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE information_schema.KEY_COLUMN_USAGE.REFERENCED_COLUMN_NAME='$primary_key';\n";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0)
	{
		die("Error: $primary_key is referenced in FK CONSTRAINTS\n\n");
	}
	$output = "RENAME TABLE $table_name to tmp_$table_name,";
	$output .= "new_$table_name TO $table_name,\n";
	$output .= "tmp_$table_name TO old_$table_name;\n";
	return $output;
}


$sql = "SHOW COLUMNS FROM $table_name";
$result = mysql_query($sql);
if (!$result)
{
	echo 'Could not run query: ' . $sql . "\n" . mysql_error() . "\n\n";
	exit;
}

if (mysql_num_rows($result) > 0)
{
	$fields = array();
	while ($row = mysql_fetch_assoc($result))
	{
		//print_r($row);
		$fields[] = $row['Field'];
	}
}


switch ($action)
{
	case 'trigger':
		echo define_trigger($table_name, $fields);
	break;

	case 'create':
		echo create_table($table_name);
	break;

	case 'switch':
                if (empty($primary_key))
                {
                        die("Error: Need the primary key\n\n");
                }
		echo switch_tables($table_name, $primary_key);
	break;

	case 'copy':
		if (empty($primary_key))
		{
			die("Error: Need the primary key\n\n");
		}

		$stop = false;
		$offset = 0;
		$batch_size = 1000;
		$total_time = 0;
		while (! $stop)
		{
			$result = mysql_query("SELECT $primary_key FROM $table_name ORDER BY $primary_key LIMIT $offset, $batch_size", $link);
			if (mysql_num_rows($result) == 0)
			{
				$stop = true;
			}
			if (! $stop)
			{
				$start_time = microtime(TRUE);
				while ($row = mysql_fetch_assoc($result))
				{
					$key = $row[$primary_key];
					$sql = "UPDATE $table_name SET $primary_key=$primary_key WHERE $primary_key=$key";
					$copy_query = mysql_query($sql, $link);
				}
				$offset += mysql_num_rows($result);
				$step_time = microtime(TRUE)-$start_time;
				$total_time += $step_time;
				echo "$offset / $batch_size / $step_time / $total_time\n";flush();
			}
			//check replication

			$sql_slave = 'show slave status';
			$wait = TRUE;
			while ($wait)
			{
				$slave_result = mysql_query($sql_slave, $slave_conn);
				$row_slave = mysql_fetch_array($slave_result);
				if ($row_slave['Seconds_Behind_Master'] == 0)
				{
					$wait = FALSE;
				}
				echo "Seconds_Behind_Master: " . $row_slave['Seconds_Behind_Master'] . " [sleeping that much]\n";
				sleep( (int) $row_slave['Seconds_Behind_Master']);
				$result = mysql_query("SELECT $primary_key FROM $table_name ORDER BY $primary_key LIMIT $offset, $batch_size", $link);
			}
		}
	break;
}




echo "\n";
