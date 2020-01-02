<?php 
class FetchImage{
	static $upgrade_message = 'Please upgrade to the current version of WordPress. Not only is it necessary for this plugin to work properly, but it will also help prevent hackers from getting into your blog through old security holes.';
    static $nonce_name = 'img-post-creator';
    static $post_title = "";
    static $description = "grabed description";
    static $images = array();
    static $kw = ""; 
	static public function remove_bad_words($term) {
        $badword = array('meqi', 'dada', 'entot', 'payudara', 'vagina', 'bokep', 'jembut', 'toket', 'memek', 'bugil', 'bogel', 'telanjang', 'ngentot', 'sex', 'lesbi', ' anal ', 'ngecrot', 'kontol', ' ass ');
        $str = str_ireplace($badword, 'cantik', $term);
        return $str;
    }
	static public function clean_title($title) {
        $badword = array('www', 'jpg', 'blogspot', 'com ', 'net ', 'org ', '-');
        $str = str_ireplace($badword, '', $title);
        return $str;
    }
	static public function getRandomWord($len = 10) {
        $word = array_merge(range('a', 'z'), range('A', 'Z'));
        shuffle($word);
        return substr(implode($word), 0, $len);
    }
	function getKeywordSuggestionsFromGoogle($keyword) {
        $keywords = array();
        $data = self::aip_curl_site('http://suggestqueries.google.com/complete/search?output=firefox&client=firefox&hl=en-US&q=' . urlencode($keyword));
        if (($data = json_decode($data, true)) !== null) {
            $keywords = $data[1];
        }
        $arr = array_slice($keywords, 0, 4);
        return implode(",", $arr);
    }
	static public function aip_curl_site($url) {
        $ch = curl_init();
        $proxy = "209.90.63.108";
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_PROXY, $proxy);
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7) Gecko/20040608');
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
	
	static public function get_bing_image($kw, $n_img_per_post, $target, $exclude, $size, $is_license) {
        include_once ("phpQuery.php");
        self::$kw = $kw;
        $n_img_per_post =  + 3;
        $target = urlencode($target);
        $query = str_replace(" ", "+", $kw);
        if ($target != 'none') {
            $query = $query . "+site:".$target;
        }
        if (!empty($exclude)) {
            $query = $query . "+-site:".$exclude;
        }
        $size = get_option("img_size");
        $first = 1;
        $result = "";
        try {
            $url = "http://www.bing.com/images/async?q=".urlencode(strtolower($query))."&async=content&first=".$first."&adlt=&count=".$n_img_per_post."&qft=";
            //$url = "pinterest.com/search/pins/?q=Kitchen%20Kraft%20Cabinets";
             if ($size != "all") {
                 $url.= "+filterui:imagesize-" . $size;
             }
             if ($is_license == "all") {
                $url.= "";
            } elseif ($is_license == "all_creative_commons") {
                 $url.= "+filterui:licenseType-Any";
             } elseif ($is_license == "public_domain") {
                $url.= "+filterui:license-L1";
             } elseif ($is_license == "free_to_share_and_use") {
                $url.= "+filterui:license-L2_L3_L4_L5_L6_L7";
             } elseif ($is_license == "free_to_share_and_use_com") {
                $url.= "+filterui:license-L2_L3_L4";
             } elseif ($is_license == "free_to_modify_share_and_use") {
                $url.= "+filterui:license-L2_L3_L5_L6";
             } else {
                $url.= "+filterui:license-L2_L3";
             }
            $result = @file_get_contents($url);
            if (!$result) {
               //echo "file_get_contents failed, try using curl";
                $result = self::aip_curl_site($url);
           }
           // echo "Result: ".$result;
            phpQuery::newDocument($result);
            $post_tag = $kw;
            $i = 0;
            $k = 0;
            self::$post_title = "";
            $j = 0;
            //preg_match_all('!<a class="thumb" target="_blank" href="(.*?)"!', $result, $url_matches);
            //foreach ($url_matches[1] as $a) {
            foreach (pq('.iusc') as $a) {
                //echo "<img src=".$a.">";
                $raw_image = pq($a)->attr('m');
                $urls = json_decode($raw_image, true);
                $image['link'] = $urls['purl'];
                $image['mediaurl'] = $urls['murl'];
                $image['thumbnail'] = $urls['turl'];
                $image['title'] = preg_replace("/[^a-zA-Z0-9\s]/", "", $urls['t'] );
                $parentNode = pq($a)->root->parentNode;
                $infoNode = $a->parentNode->nextSibling;
                $imageInfo = pq("li", $infoNode)->elements[1]->nodeValue;
                $t2arr = explode("·", $imageInfo);
                $image['size'] = "0";
                $image['dimension'] = trim($t2arr[0]);
                $images[] = $image;
            }
        }
        catch(Exception $e) {
            echo "<br>Keyword Skipped, " . $e->getMessage();
            $images = array();
        }
        return $images;
        //print_r($images);
    //     echo "<ol>";
    //     foreach($images as $image){
    //         //echo "<li>"
    //         echo "<img src='".$image['mediaurl']."'>";
    //         echo $image['mediaurl'];
    //         echo "<br/>";
    //         echo $image['title'];
    //         echo "<br/>";
    //       //  echo "</li>";
    //     }
    //    echo "</ol>";
    }
    
    static public function get_article($kw) {
        $kw = trim($kw);
        $cari = $kw;
        $kw = str_replace(" ", "+", $kw);
        $url = "https://www.bing.com/search?q=$kw&format=rss";
        $feed = @file_get_contents($url);
        if(!$feed){
            $feed = self::aip_curl_site($url);
        }
        $xpath = "//channel/item";
        
        $xml = @simplexml_load_string($feed)->xpath($xpath);
        if (!$xml) {
            $url = "https://www.bing.com/news/search?q=$kw&format=rss";
            $feed = @file_get_contents($url);
            if(!$feed){
                $feed = self::aip_curl_site($url);
            }
            $xml = @simplexml_load_string($feed)->xpath($xpath);
        }
        $i = 0;
        $str = "";
        foreach ($xml as $r) {
            $str.= str_replace("...", "", $r->description) . " ";
            if ($i == 5) $str.= ".<br><br>";
            $i++;
        }
        $str = self::remove_bad_words($str);
        $str = str_replace("http://", "", $str);
        $str = preg_replace("/$cari/i", "<strong>$cari</strong>", $str, 1);
        $str = preg_replace("/ $cari/i", " <em>$cari</em>", $str, 1);
        $str = preg_replace("/ $cari/i", " <u>$cari</u>", $str, 1);
        $str = "<p>" . $str . ".</p>";
        
        return $str;
        //var_dump($xml);
    }

    static public function pete_curl_get($url, $params) {
        $post_params = array();
        foreach ($params as $key => & $val) {
            if (is_array($val)) $val = implode(',', $val);
            $post_params[] = $key . '=' . urlencode($val);
        }
        $post_string = implode('&', $post_params);
        $fullurl = $url . "?" . $post_string;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_URL, $fullurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7) Gecko/20040608');
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    static public function get_google_image($kw, $nimg, $target_site, $imgSize = "large", $is_public, $api, $cx) {
        if ($api == "") {
            echo "Google API is empty";
            exit;
        }
        if ($cx == "") {
            echo "Google (CSE) CX is empty";
            exit;
        }
        $start = 0;
        $get_google_API = $api;
        $result = array();
        while ($nimg >= $start * 8) {
            $searchurl = 'https://www.googleapis.com/customsearch/v1?';
            $searchurl.= '&key=' . $get_google_API;
            $searchurl.= '&start=' . (($start * 8) + 1);
            if ($imgSize != 'all') {
                $searchurl.= '&imgsz=' . $imgSize;
            }
            if ($target_site != "none") {
                $searchurl.= '&siteSearch=' . $target_site . '&siteSearchFilter=i';
                $searchurl.= '&linkSite=' . $target_site;
            }
            $searchurl.= '&searchType=image';
            $searchurl.= '&cx=' . $cx;
            $searchurl.= '&alt=json&googlehost=www.google.com';
            //$searchurl.='&lr=lang_de';
            $searchurl.= '&fileType=jpg,jpeg,bmp,gif,png';
            $searchurl.= '&q=' . urlencode($kw);
            $response = self::pete_curl_get($searchurl, array());
            $responseobject = json_decode($response, true);
            if (count($responseobject['error']) > 0) {
                echo "<div class=\"error\"><strong>API ERROR:</strong> <br>";
                echo "Domain:" . $responseobject['error']['errors'][0]['domain'] . "<br>";
                echo "Reason:" . $responseobject['error']['errors'][0]['reason'] . "<br>";;
                echo $responseobject['error']['errors'][0]['message'] . "<br>";
                echo "</div>";
                break;
            }
            if (count(isset($responseobject)) == 0) break;
            $images = self::google_images_result($responseobject);
            $result = array_merge($result, $images);
            $start++;
        }
        return $result;
    }
    function google_images_result($allresponseresults) {
        $images = array();
        if (is_array($allresponseresults)) {
            foreach ($allresponseresults['items'] as $item) {
                $url = $item['link'];
                $startx = strpos($url, "/revision/latest");
                if ($startx > 0) {
                    $url = substr($url, 0, $startx);
                }
                $title = $item['title'];
                $title = str_replace("-", " ", $title);
                $title = strip_tags($title);
                $height = $item['image']['height'];
                $width = $item['image']['width'];
                $size = $item['image']['byteSize'];
                $image['link'] = $item['displayLink'];
                $image['mediaurl'] = $url;
                $image['title'] = $title;
                $image['dimension'] = $height . " X " . $width;
                $image['size'] = $size;
                $images[] = $image;
            }
        }
        return $images;
    }
    static public function get_google_image2($kw){
        include_once ("phpQuery.php");
        //$kw = "fliesen überstreichen ideen"; //change this
        $kw = urlencode( $kw );
        $googleRealURL = "https://www.google.com/search?hl=en&biw=1360&bih=652&tbs=isz%3Alt%2Cislt%3Asvga%2Citp%3Aphoto&tbm=isch&sa=1&q=".$kw."&oq=".$kw."&gs_l=psy-ab.12...0.0.0.10572.0.0.0.0.0.0.0.0..0.0....0...1..64.psy-ab..0.0.0.wFdNGGlUIRk";
        
        // Call Google with CURL + User-Agent
        $ch = curl_init($googleRealURL);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux i686; rv:20.0) Gecko/20121230 Firefox/20.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $google = curl_exec($ch);   
        $array_imghtml = explode("\"ou\":\"", $google); //the big url is inside JSON snippet "ou":"big url"
        foreach($array_imghtml as $key => $value){
          if ($key > 0) {
            $array_imghtml_2 = explode("\",\"", $value);
            $array_imgurl[] = $array_imghtml_2[0];
          }
        }
        //echo "Result: ".$google;
        phpQuery::newDocument($google);
        foreach (pq('.rg_meta') as $elements) {
            $raw_images = pq($elements)->html();
            $urls = json_decode($raw_images, true);
            $image['link'] = $urls['ru'];
            $image['mediaurl'] = $urls['ou'];
            $image['thumbnail'] = $urls['tu'];
            $image['title'] = $urls['pt'];
            $image['host'] = $urls['rh'];
            $images[] = $image;
        }
        return $images;
        //return $google;
    }
	// static public function aip_copy_image_into_local($src, $kw = "") {
    //     $fname = basename($src);
    //     if ($kw != "") $fname = $kw . ".jpg";
    //     $uploads = wp_upload_dir();
    //     $ref = explode("/", $src);
    //     $refhost = "http://" . $ref[2];
    //     $success = false;
    //     $uploads = wp_upload_dir();
    //     $ch = curl_init($src);
    //     curl_setopt($ch, CURLOPT_HEADER, 0);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_REFERER, $refhost);
    //     $rawdata = @curl_exec($ch);
    //     curl_close($ch);
    //     $fullpath = $uploads['path'] . "/" . $fname;
    //     if (!file_exists($fullpath)) {
    //         if (true) { //reset image metadata
    //             $im = imagecreatefromstring($rawdata);
    //             //imageflip($im, IMG_FLIP_HORIZONTAL);
    //             imagejpeg($im, $fullpath);
    //             imagedestroy($im);
    //         } else {
    //             $fp = @fopen($fullpath, 'x');
    //             @fwrite($fp, $rawdata);
    //             @fclose($fp);
    //         }
    //     }
    //     if (file_exists($fullpath)) $success = $uploads['url'] . "/" . basename($fullpath);
    //     return $success;
    // }
	
	// static public function get_image($kw) {
	// 	self::$kw = $kw;
	// 	//$n_img_per_post = 10;
    //     $n_img_per_post = get_option('num_image');
    //     // $api = get_option('g_api');
    //     // $cx = get_option('g_cx');
    //     // $is_public = (get_option('public_image') == 1);
    //     // $size = get_option('imgSize');
    //     // $target = get_option('target_site');
	// 	$images = self::get_bing_image($kw, $n_img_per_post + 8, "none", "all", true);
    //     $template = get_option('post_template');
    //     $tempcat = get_cat_name(get_option('cron_category'));
	// 	foreach ($images as $key => $value) {
    //             $title = $value['title'];
    //             $img_size = $value['dimension'];
    //             $url = $value['link'];
    //             $img_url = $value['mediaurl'];
    //             $content = '';
    //             $title = str_replace("...", "", $title);
    //             $title = self::clean_title($title);
    //             self::$post_title = $kw;
    //             $arrImgHost[] = $url;
    //             $arrImgSrc[] = $img_url;
    //             $arrImg[] = $img_url;
    //             $img_desc = str_replace("...", "", $title);
    //             $arrImgDesc[] = $img_desc;
    //             $arrImgResolution[] = $img_size;
    //         }
	// 		$j = 0;
    //         $img_path = array();
    //         //print_r($arrImg);
    //         // foreach($arrImg as $value){
    //         //     echo "<img src=".$value." style='width:50px;height:50px;'>";
    //         // }
	// 	foreach ($arrImg as $key => $value) {
    //                 $localimg_url = $value;
    //                     $filename = pathinfo($value, PATHINFO_FILENAME);
    //                     $filename = strtolower($filename);
    //                     if (false) {// save as kw
    //                         $newFilename = str_replace(" ", "-", $kw);
    //                         $newFilename = $newFilename.'-'.($j + 1);
    //                     } else {
    //                     $newFilename = str_replace("_", "-", $newFilename);
    //                     $newFilename = str_replace("--", "-", $newFilename);
    //                     $newFilename = str_replace("+", "-", $newFilename);
    //                     $newFilename = str_replace(" ", "-", $newFilename);
    //                     $newFilename = str_replace("%20", "-", $newFilename);
    //                     }
    //                     $localimg_url = self::aip_copy_image_into_local($value, $newFilename);
    //                     $path = parse_url($localimg_url, PHP_URL_PATH);
    //                     $img_path = $_SERVER['DOCUMENT_ROOT'] . $path;
                        
    //                    // echo "<img src=".$value." style='width:50px;height:50px;'>";
    //                    //echo "<div style='display:none;'>".$value."</div>";
    //                     $nsize = @filesize($img_path);
    //                     if ($size == "xxlarge") {
    //                         $brokenImageSize = 1024 * 100;
    //                     } else {
    //                         $brokenImageSize = 1024 * 10;
    //                     }
    //                     if ($nsize > $brokenImageSize) {
    //                         $validImg[] = $localimg_url;
    //                         $sz = 'BKMGTP';
    //                         $factor = floor((strlen($size) - 1) / 3);
    //                         $arrImgSize[] = sprintf("%.2f", $nsize / pow(1024, $factor)) . @$sz[$factor];
    //                         self::$images[] = $localimg_url;
    //                         if (count($validImg) >= $n_img_per_post) break;
    //                     } else {
    //                         unlink($img_path);
    //                     }
    //                     //echo "<img src=".$localimg_url." style='width:50px;height:50px;'>";
    //                 $j++;
					
    //             }
	// 			foreach ($validImg as $key=>$localurl){
    //                 $template = str_replace("{URL_IMG" . ($key + 1) . "}", $localurl, $template);
    //                 $template = str_replace("{TITLE_IMG" . ($key + 1) . "}", $arrImgDesc[$key], $template);
    //             }
    //             $template = str_replace('{GALLERY}', '[gallery orderby="rand"  size="medium" columns="4" link="post"]', $template);
    //             $template = str_replace('{KEYWORD}', $kw, $template);
    //             $template = str_replace('{CATEGORY}', $tempcat, $template);
    //             $template = str_replace('{POST_TITLE}', '[post_title]', $template);
    //             return $template;
		
	// }

}
