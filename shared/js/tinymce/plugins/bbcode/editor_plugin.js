/**
 * $Id: editor_plugin_src.js 201 2007-02-12 15:56:56Z spocke $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	tinymce.create('tinymce.plugins.BBCodePlugin', {
		init : function(ed, url) {
			var t = this, dialect = ed.getParam('bbcode_dialect', 'punbb').toLowerCase();

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t['_' + dialect + '_bbcode2html'](o.content);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.set)
					o.content = t['_' + dialect + '_bbcode2html'](o.content);

				if (o.get)
					o.content = t['_' + dialect + '_html2bbcode'](o.content);
			});
		},

		getInfo : function() {
			return {
				longname : 'BBCode Plugin',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/bbcode',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		// Private methods

		// HTML -> BBCode in PunBB dialect
		_punbb_html2bbcode : function(_text) {
			_text = tinymce.trim(_text);

//			alert(_text)

			function rep(re, str) {
				_text = _text.replace(re, str);
			};

			// example: <strong> to [b]
			rep(/<a.*?href=\"(.*?)\".*?>(.*?)<\/a>/gi,"[url=$1]$2[/url]");
			rep(/<span style=\"color: ?(.*?);\">(.*?)<\/span>/gi,"[color=$1]$2[/color]");
			rep(/<font.*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[color=$1]$2[/color]");
			rep(/<span style=\"font-size:(.*?);\">(.*?)<\/span>/gi,"[size=$1]$2[/size]");
			rep(/<font[^>]*>/gi,"");
			rep(/<\/font>/gi,"");
			rep(/<img[^>]+src=\"(.*?)\".*?\/>/gi,"[img]$1[/img]");
			rep(/<\/(strong|b)>/gi,"[/b]");
			rep(/<(strong|b)>/gi,"[b]");
			rep(/<\/(em|i)>/gi,"[/i]");
			rep(/<(em|i)>/gi,"[i]");
			rep(/<span style=\"text-decoration: ?underline;\">(.*?)<\/span>/gi,"[u]$1[/u]");
			rep(/<u>/gi,"[u]");
			rep(/<\/u>/gi,"[/u]");
			rep(/<br>/gi,"\n");
			rep(/<br [^>]*?>/gi,"\n");
			rep(/<p>/gi,"\n\n");
			rep(/<p [^>]*>/gi,"\n\n");
			rep(/<span[^>]*>/gi,"");
			rep(/<\/span>/gi,"");
			rep(/<\/p>/gi,"\n");
			rep(/&nbsp;/gi," ");
			rep(/&quot;/gi,"\"");
			rep(/&lt;/gi,"<");
			rep(/&gt;/gi,">");
			rep(/&amp;/gi,"&");

			rep(/<\/h2>/gi,"[/h2]");
			rep(/<h2>/gi,"[h2]");
//			rep(/<\/s>/gi,"[/s]");
//			rep(/<s>/gi,"[s]");

			rep(/<hr \/>/gi,"[hr]");

			rep(/<table[^>]*>/gi,"[table]");
			rep(/<\/table>/gi,"[/table]");

			rep(/<tr>/gi,"[tabtr]");
			rep(/<\/tr>/gi,"[/tabtr]");
			rep(/<td>/gi,"[td]");
			rep(/<td rowspan="(\d+)">/gi,"[td rowspan=\"$1\"]");
			rep(/<td colspan="(\d+)">/gi,"[td colspan=\"$1\"]");
			rep(/<\/td>/gi,"[/td]");
			rep(/<tbody>/gi,"");
			rep(/<\/tbody>/gi,"");

			rep(/<object ([^>]*?)>/gi,"[object $1]");
			rep(/<\/object>/gi,"[/object]");
			rep(/<param ([^>]*?)\s*\/>/gi,"[param $1][/param]");
			rep(/<param ([^>]*?)>/gi,"[param $1]");
			rep(/<\/param>/gi,"[/param]");

/*
			rep(/<font.*?color=\"(.*?)\".*?class=\"codeStyle\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");
			rep(/<font.*?color=\"(.*?)\".*?class=\"quoteStyle\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");
			rep(/<font.*?class=\"codeStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");
			rep(/<font.*?class=\"quoteStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");
			rep(/<blockquote[^>]*>/gi,"[quote]");
			rep(/<\/blockquote>/gi,"[/quote]");
			rep(/<span class=\"codeStyle\">(.*?)<\/span>/gi,"[code]$1[/code]");
			rep(/<span class=\"quoteStyle\">(.*?)<\/span>/gi,"[quote]$1[/quote]");
			rep(/<strong class=\"codeStyle\">(.*?)<\/strong>/gi,"[code][b]$1[/b][/code]");
			rep(/<strong class=\"quoteStyle\">(.*?)<\/strong>/gi,"[quote][b]$1[/b][/quote]");
			rep(/<em class=\"codeStyle\">(.*?)<\/em>/gi,"[code][i]$1[/i][/code]");
			rep(/<em class=\"quoteStyle\">(.*?)<\/em>/gi,"[quote][i]$1[/i][/quote]");
			rep(/<u class=\"codeStyle\">(.*?)<\/u>/gi,"[code][u]$1[/u][/code]");
			rep(/<u class=\"quoteStyle\">(.*?)<\/u>/gi,"[quote][u]$1[/u][/quote]");
			rep(/<code[^>]*>/gi,"[code]");
			rep(/<\/code>/gi,"[/code]");
*/
			return _text; 
		},


		// BBCode -> HTML from PunBB dialect
		_punbb_bbcode2html : function(_text) {
			_text = tinymce.trim(_text);

			function rep(re, str) {
				_text = _text.replace(re, str);
			};

			// example: [b] to <strong>
			rep(/\n/gi,"<br />");
			rep(/\[b\]/gi,"<strong>");
			rep(/\[\/b\]/gi,"</strong>");
			rep(/\[i\]/gi,"<em>");
			rep(/\[\/i\]/gi,"</em>");
			rep(/\[u\]/gi,"<u>");
			rep(/\[\/u\]/gi,"</u>");
			rep(/\[url=([^\]]+)\](.*?)\[\/url\]/gi,"<a href=\"$1\">$2</a>");
			rep(/\[url\](.*?)\[\/url\]/gi,"<a href=\"$1\">$1</a>");
			rep(/\[img\](.*?)\[\/img\]/gi,"<img src=\"$1\" />");
			rep(/\[color=(.*?)\](.*?)\[\/color\]/gi,"<font color=\"$1\">$2</font>");
/*			rep(/\[code\](.*?)\[\/code\]/gi,"<code>$1</code>&nbsp;");
			rep(/\[quote.*?\](.*?)\[\/quote\]/gi,"<blockqoute>$1</blockquote>&nbsp;"); */

			rep(/\[h2\]/gi,"<h2>");
			rep(/\[\/h2\]/gi,"</h2>");
//			rep(/\[s\]/gi,"<s>");
//			rep(/\[\/s\]/gi,"</s>");

			rep(/\[hr\]/gi,"<hr />");

			rep(/\[table\]/gi,"<table class=\"btab\">");
			rep(/\[\/table\]/gi,"</table>");

			rep(/\[tabtr\]/gi,"<tr>");
			rep(/\[\/tabtr\]/gi,"</tr>");
			rep(/\[td\]/gi,"<td>");
			rep(/\[td rowspan=\"(\d+)\"\]/gi,"<td rowspan=\"$1\">");
			rep(/\[td colspan=\"(\d+)\"\]/gi,"<td colspan=\"$1\">");
			rep(/\[\/td\]/gi,"</td>");

			rep(/\[object (.+?)\]/gi,"<object $1>");
			rep(/\[\/object\]/gi,"</object>");
			rep(/\[param (.+?)\]/gi,"<param $1>");
			rep(/\[\/param\]/gi,"</param>");

			return _text; 
		}
	});

	// Register plugin
	tinymce.PluginManager.add('bbcode', tinymce.plugins.BBCodePlugin);
})();