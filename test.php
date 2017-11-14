<?php

	function sendTops(){
		$tops = curl_init("http://45.76.11.241/connection/instagram/manage/rank.php");
		curl_setopt($tops, CURLOPT_POST, 1);
	    curl_setopt($tops, CURLOPT_POSTFIELDS, 'curl=mailTops');

	    $msg = curl_exec($tops);
	    curl_close($tops);

	    echo $msg;

	    // $ch = curl_init("https://api.sendgrid.com/v3/mail/send");
	    // curl_setopt($ch, CURLOPT_HEADER, 1);
	    // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    //     'Authorization: Bearer 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',
	    //     'Content-Type: application/json',
	    // ));
	    // curl_setopt($ch, CURLOPT_POST, 1);
	    // $email = 'pierre.leroy.mail@gmail.com';
	    // $data2 = '{"personalizations": [{"to": [{"email": "'.$email.'"} ], "subject": "Instagram Manager by YouPic"} ], "from": {"email": "insta_manager@youpic.com"}, "content": [{"type": "text/html", "value": "'.$msg.'"} ] }';
	    // curl_setopt($ch, CURLOPT_POSTFIELDS, $data2);

	    // curl_exec($ch);
	    // curl_close($ch);
	}

	sendTops();