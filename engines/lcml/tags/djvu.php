<?
    function lp_djvu($txt,$params)
    { 
//        $txt = $_[0]=~s!\[(.+?\.djvu)\s+(\d+)x(\d+)\s+(.+?)\]!<\!\-\-
//        return "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"http://www.djvu.com/plugins/TriggerUpdate.js\"></SCRIPT>\-\-><OBJECT classid="clsid:0e8d0700-75df-11d3-8b4a-0008c7450c4a" WIDTH=$2 HEIGHT=$3 BORDER=0 HSPACE=0 VSPACE=0 STYLE="border:0" CODEBASE="http://www.djvu.com/plugins/DjVuControl.cab#version=2,0,6,1"><param name="imageURL" value="$1"><param name="flags"  value="$4"><EMBED TYPE="image/x.djvu" SRC="/plugins/welcome.djvu" HEIGHT=$2 WIDTH=$3 PLUGINSPAGE="http://www.djvu.com/plugins/SmartUpdate.html?$1" $4></EMBED></OBJECT>!gis;

        return $txt;
    }
?>