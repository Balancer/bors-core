<?
    require_once('funcs/images/fill.php');

    function lsp_gallery($txt, $title) 
    { 
        $out  = "<br><br>\n<script charset=\"UTF-8\" src=\"http://airbase.ru/inc/js/gal.js\"></script>\n";
        $out .= "<script charset=\"UTF-8\">begGallery('".addslashes($title)."')</script>\n";
        $out .= "<noscript><h2>$title</h2><ul></noscript>\n";

        $hts = new DataBaseHTS;

        $page = $hts->normalize_uri($GLOBALS['main_uri']);

        foreach(split("\n", $txt) as $s)
        {
//			echo $s;
		
			@list($iimg, $description, $copyright, $author, $uri) = @split("\|", $s);
			
//			echo "($iimg, $description, $copyright, $author, uri=$uri)";
			
			$img = fill_image_data($iimg, $page);
            
            if(!$img)
            {
                $GLOBALS['cms_images'][] = $iimg;
                $img = 'http://airbase.ru/img/design/system/not-loaded.png';
                $img = fill_image_data($img, $page);
            }
            else
            {
                $hts->nav_link($page, $img);
            }

            foreach(split(' ','description copyright author') as $p)
                if(!$hts->get_data($img, $p))
                    $hts->get_data($img, $p, $$p);

            $admin_url = "http://airbase.ru/admin/img.phtml?img=$img";
            $img_admin = ""; // "<a href=\"$admin_url\" title=\"Управление картинкой\" onClick=\"window.open('$admin_url','_blank','scrollbars=yes,status=yes,toolbar=no,directories=no,width=600,height=400,resizable=yes'); return false;\"><img src=\"http://airbase.ru/admin/img/tools.gif\" width=\"16\" height=\"13\" border=\"0\" align=\"absmiddle\"></a>";

			$w = $hts->get_data($img, 'width');
            $h = $hts->get_data($img, 'height');
            $tt= array(1 => 'GIF', 2 => 'JPG', 3 => 'PNG', 4 => 'SWF', 5 => 'PSD', 6 => 'BMP', 7 => 'TIFF(intel byte order)', 8 => 'TIFF(motorola byte order)', 9 => 'JPC', 10 => 'JP2', 11 => 'JPX', 12 => 'JB2', 13 => 'SWC', 14 => 'IFF', 15 => 'WBMP', 16 => 'XBM');
            $t = $hts->get_data($img, 'type');
            if(!empty($tt[$t]))
                $t = $tt[$t];

			$ico = preg_replace("!^(http://[^/]+)(.*?)(/[^/]+)$!", "$1/cache$2/200x150$3", $img);

            list($iw, $ih, $it) = getimagesize($ico);

            $ico640_url  = preg_replace("!/200x150/!","/640x480/",$ico);
            $ico800_url  = preg_replace("!/200x150/!","/800x600/",$ico);
            $ico1024_url = preg_replace("!/200x150/!","/1024x768/",$ico);

            $ico640_url  = ($w>640  || $h>480) ? "<a href=\"$ico640_url\">640x480</a>":"";
            $ico800_url  = ($w>800  || $h>600) ? "<a href=\"$ico800_url\">800x600</a>":"";
            $ico1024_url = ($w>1024 || $h>768) ? "<a href=\"$ico1024_url\">1024x768</a>":"";

            $icons= $ico640_url ? "Другие размеры: $ico640_url $ico800_url $ico1024_url":"";

			$idesc = "";
			if(!$uri)
			{
				$uri = preg_replace("!\.(gif|jpe?g|png)$!i", ".htm", $img);
				$idesc = "$img_admin $t {$w}x{$h} ".intval($hts->get_data($img, 'size')/1024+0.5)."K";
			}
			
            $out .= "<script charset=\"UTF-8\">galItem(".
                "'".addslashes($uri)."',".
                "'".addslashes($description)."',".
                "'".addslashes($idesc)."<br />$icons',".
                "'$ico',$iw,$ih,'".addslashes($copyright)."')</script>".
                "<noscript><li><a href=\"$uri\">[$t {$w}x{$h}]</a> $description<small>// $copyright</small></noscript>\n";
        }
        $out .= "<script charset=\"UTF-8\">endGallery()</script><noscript></ul></noscript>\n\n";
       
        return $out;
    }
?>
