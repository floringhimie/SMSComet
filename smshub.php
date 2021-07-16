<?	 
$hostname='localhost';
$user='user'; 
$pass='pass';
$dbase='database';

$connection = @mysql_connect("$hostname" , "$user" , "$pass")
or die ("Can't connect to MySQL");
$db = mysql_select_db($dbase , $connection) or die ("Can't select database.");


	function send_task()
	{
		global $connection;
		$reply = array();
		
		$query = mysql_query("SELECT * FROM sms_gw_send_task where id_cabinet='{$_POST['deviceId']}' and status = 'PENDING' limit 0,5",$connection);
		while($fetch = mysql_fetch_assoc($query))
		{
			$reply[] = [
				"number" => $fetch["telefon"],
				"message" => $fetch["mesaj"],
				"messageId" => strval($fetch["id_mesaj"])
			];

		}
		if (!empty($reply)) {
			$response = json_encode(array_values($reply));
		} else {
			$response = '';
		}

		send_response($response);
	}
	function sent_responce()
	{
		global $connection;
		
		$sql=mysql_query("UPDATE sms_gw_send_task SET status = '{$_POST['status']}', data_trimitere = now() where id_cabinet='{$_POST['deviceId']}' and id_mesaj='{$_POST['messageId']}'", $connection);
		
		$arr = '{"status":"OK"}';
		send_response($arr);
	}
	
	
	function received() {
		global $connection;
		global $id;
		$_POST['number'] = str_replace('+4','',$_POST['number']);
		$insert = mysql_query("INSERT INTO sms_gw_send_task(id_cabinet,telefon,mesaj,status,data_adaugare) VALUES('{$id}','{$_POST['number']}','{$_POST['message']}','RECEIVED',now())",$connection);
		$arr = '{"status":"OK"}';
		send_response($arr);
	}
	
	function send_response($response)
    {
        // Avoid caching
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		    header("Content-type: application/json; charset=utf-8");
        echo $response;
    }
	
	
	if($_SERVER['REQUEST_METHOD'] === 'POST')
    {
		/*if(!get_magic_quotes_gpc()) {
			$_POST['deviceId'] = addslashes($_POST['deviceId']);
			$_POST['deviceSecret'] = addslashes($_POST['deviceSecret']);
			$_POST['action'] = addslashes($_POST['action']);
			$_POST['status'] = addslashes($_POST['status']);
			$_POST['number'] = addslashes($_POST['number']);
			$_POST['message'] = addslashes($_POST['message']);
			$_POST['messageId'] = addslashes($_POST['messageId']);
		}*/
		if($_POST['deviceId'] == '') {
			exit;
		} else {
			$id = $_POST['deviceId'];
			$secret = '1234';
		}
		
		if($secret == '') {
			exit;
		} else if($secret != $_POST['deviceSecret']) {
			exit;
		}
		
		if(isset($_POST['action']) AND $_POST['action'] === 'SEND'){
            send_task();
        }
		if(isset($_POST['action']) AND $_POST['action'] === 'STATUS_UPDATE'){
            sent_responce();
        }
		if(isset($_POST['action']) AND $_POST['action'] === 'RECEIVED'){
            received();
        }
	}
	if($_SERVER['REQUEST_METHOD'] === 'GET')
    {
		echo "...";
	}

?>
