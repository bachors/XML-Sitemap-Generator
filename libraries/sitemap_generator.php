<?php

/****************************************
* #### Sitemap Generator v1.0 ####
* Coded by Ican Bachors 2016.
* http://bachors.com/
*****************************************/

class Sitemap_generator
{
    function __construct($set)
    {
		$url = $set['url'];
		$www = $set['www'];
		
        $this->url        = $url;
        $this->scheme     = parse_url($url, PHP_URL_SCHEME);
        $this->host       = parse_url($url, PHP_URL_HOST);
		$this->user_agent = (empty($set['ua']) ? 'Googlebot/2.1 (http://www.googlebot.com/bot.html)' : $set['ua']);
        
		// array list of directories
        $dirs     = explode('/', parse_url($url, PHP_URL_PATH));
        $not_path = '/\./';
        foreach ($dirs as $key => $val) {
            $res = preg_match($not_path, $val);
            if ($res || empty($val)) {
                unset($dirs[$key]);
            }
        }
        $dirs      = array_values($dirs);
        $this->dir = $dirs;
        
		// get domain name only without www or subdomain
        if ($www == true) {
            $domain = 'ibacor.' . parse_url($url, PHP_URL_HOST);
            $dot    = explode('.', $domain);
            if (count($dot) == 5) {
                $domain = $dot[2] . '.' . $dot[3] . '.' . $dot[4];
            } else if (count($dot) == 4) {
                $domain = $dot[2] . '.' . $dot[3];
            } else {
				// fake host to return error
                $this->host = 'dfsdfsd343ffsxdfdf.kom';
            }
            $this->domain = $domain;
        } else {
            $this->domain = str_replace('www.', '', $this->host);
        }
        
		// include simple_html_dom
        include_once ('simple_html_dom.php');
    }	
    
    private function ayocurl()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_REFERER, $this->url);
        curl_setopt($ch, CURLOPT_URL, $this->url);
		
		if (!curl_error($ch)) {
			$html = curl_exec($ch);
			curl_close($ch);
		}else{
			$html = 'error';
		}
		
        return $html;
    }
    
	// fix url without domain name to full url
    private function pull_url($url)
    {
        if (preg_match("/^http/", $url)) {
            $fixurl = $url;
        } else if (preg_match("/^\/\//", $url)) {
			$fixurl = $this->scheme . ':' . $url;
		} else {
            $no = substr_count($url, '..');
            if ($no == 0) {
                if (substr($url, 0, 2) == '//') {
                    $fixurl = $this->scheme . $url;
                } else if (substr($url, 0, 1) == '/') {
                    $fixurl = $this->scheme . '://' . $this->host . $url;
                } else {
                    $dir    = implode('/', $this->dir);
                    $path   = (!empty($dir) ? '/' . $dir : '');
                    $fixurl = $this->scheme . '://' . $this->host . $path . '/' . $url;
                }
            } else {
                $path = '';
                for ($x = 0; $x < count($this->dir) - $no; $x++) {
                    $path .= '/' . $this->dir[$x];
                }
                $fixurl = $this->scheme . '://' . $this->host . $path . '/' . str_replace('../', '', $url);
            }
        }
        return $fixurl;
    }
    
    public function urls()
    {
        $data      = array();
		$dom 	   = $this->ayocurl();
		if($dom != 'error' && $dom != false){
			$cekHost   = @gethostbynamel($this->host);
			$cekDomain = @gethostbynamel($this->domain);
			
			// cek host found
			if (!empty($cekHost) && !empty($cekDomain)) {
				$html = str_get_html($dom);
				
				if (!empty($html->find('a'))) {
					$links = $html->find('a');
					foreach ($links as $link) {
						$href = preg_replace("/#[^>]+/i", "", $link->href);
						$href = preg_replace("/#/", "", $href);
						if (!preg_match('/\(/', $href)) {
									
							// cek url with domain name
							if (preg_match("/^http/", $href)) {
								if (preg_match("/^$this->scheme:\/\/$this->domain/", $href) || preg_match("/^$this->scheme:\/\/([^>]*)\.$this->domain/", $href)) {
									array_push($data, $href);
								}
							}
									
							// url without domain name
							else {
								$no = substr_count($href, '..');
								
								if ($no == 0) {
									if (preg_match("/^\/\//", $href)) {
										$fixhref = $this->scheme . ':' . $href;
									} else if (substr($href, 0, 1) == '/') {
										$fixhref = $this->scheme . '://' . $this->host . $href;
									} else {
										$dir     = implode('/', $this->dir);
										$path    = (!empty($dir) ? '/' . $dir : '');
										$fixhref = $this->scheme . '://' . $this->host . $path . '/' . $href;
									}
								} else {
									$path = '';
									for ($x = 0; $x < count($this->dir) - $no; $x++) {
										$path .= '/' . $this->dir[$x];
									}
									$fixhref = $this->scheme . '://' . $this->host . $path . '/' . str_replace('../', '', $href);
								}
										
								array_push($data, $fixhref);
							}
									
						}
					}
				}
					
			}
		}
		
		// remove duplicat url
        return array_unique($data);
    }
    
}

?>