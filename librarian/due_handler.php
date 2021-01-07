<?php
	require "../db_connect.php";
	require "verify_librarian.php";
	require "header_librarian.php";
	require "../details.php";
	require '/usr/share/php/libphp-phpmailer/src/PHPMailer.php';
	require '/usr/share/php/libphp-phpmailer/src/SMTP.php';
	require '/usr/share/php/libphp-phpmailer/src/Exception.php';
?>

<html>
	<head>
		<title>Reminders for today</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css" />
	</head>
	<body>
	
	<?php
		$query = "CALL generate_due_list();";
		$result = mysqli_query($con, $query);
		$rows = mysqli_num_rows($result);
		
		if($rows > 0)
		{
			$successfulEmails = 0;
			$idArray;
			$query = "";
		
			for($i=0; $i<$rows; $i++)
			{
				$row = mysqli_fetch_array($result);
				//$to = $row[1];
				$mail = new PHPMailer\PHPMailer\PHPMailer;
				$mail->setFrom('library@example.com');
				$mail->addAddress($row[1]);
				$mail->Subject = 'Reminder!';
				$message = "This is a reminder to return the book '".$row[3]."' with ISBN ".$row[2]." to the library.";
				$mail->Body = $message;
				$mail->IsSMTP();
				$mail->SMTPSecure = 'ssl';
				$mail->Host = 'ssl://smtp.gmail.com';
				$mail->SMTPAuth = true;
				$mail->Port = 465;
						
				$mail->Username = $USERNAME;
				$mail->Password = $PASSWORD;
				
				if($mail->send())
				{
					$idArray[$i] = $row[0];
					$successfulEmails++;
				}
			}
			
			mysqli_next_result($con);
			
			for($i=0; $i<$rows; $i++)
			{
				$query = $con->prepare("UPDATE book_issue_log SET last_reminded = CURRENT_DATE WHERE issue_id = ?;");
				$query->bind_param("d", $idArray[$i]);
				$query->execute();
				$query->get_result();
			}
			
			if($successfulEmails > 0)
				echo "<h2 align='center'>Successfully notified ".$successfulEmails." members</h2>";
			else
				echo "ERROR: Couldn't notify any member.";
		}
		else
			echo "<h2 align='center'>No reminders pending</h2>";
	?>
	</body>
</html>