<?php

if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST"){

	$data = array();
			
	if(!empty($_POST['url'])){
		
		include_once('libraries/sitemap_generator.php');

		// data
		$set = array(
			'url' => $_POST['url'],
			'www' => (empty($_POST['www']) ? false : true),
			'ua' => 'Googlebot/2.1 (http://www.googlebot.com/bot.html)'
		);			
		$sitemap = new Sitemap_generator($set);
					
		$data['status'] = 'success';
		$data['data'] = $sitemap->urls();
		
	}else{
		$data['status'] = 'error';
		$data['message'] = 'Data not valid!';
	}
			
	echo json_encode($data);

}else{
	header('Location: /');
}