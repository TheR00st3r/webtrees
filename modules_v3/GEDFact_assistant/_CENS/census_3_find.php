<?php
// Facility for Census assistant that will allow a user to search for a person
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010  PGV Development Team.  All rights reserved.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id$

$controller=new WT_Controller_Simple();

global $MEDIA_DIRECTORY, $MEDIA_DIRECTORY_LEVELS, $ABBREVIATE_CHART_LABELS; 

$type           =safe_GET('type', WT_REGEX_ALPHA, 'indi');
$filter         =safe_GET('filter');
$action         =safe_GET('action');
$callback       =safe_GET('callback', WT_REGEX_NOSCRIPT, 'paste_id');
$create         =safe_GET('create');
$media          =safe_GET('media');
$external_links =safe_GET('external_links');
$directory      =safe_GET('directory', WT_REGEX_NOSCRIPT, $MEDIA_DIRECTORY);
$multiple       =safe_GET_bool('multiple');
$showthumb      =safe_GET_bool('showthumb');
$all            =safe_GET_bool('all');
$subclick       =safe_GET('subclick');
$choose         =safe_GET('choose', WT_REGEX_NOSCRIPT, '0all');
$level          =safe_GET('level', WT_REGEX_INTEGER, 0);
$qs             =safe_GET('tags');



// Retrives the currently selected tags in the opener window (reading curTags value of the query string)
// $preselDefault will be set to the array of DEFAULT preselected tags
// $preselCustom will be set to the array of CUSTOM preselected tags
function getPreselectedTags(&$preselDefault, &$preselCustom) {
	global $qs;
	$all = strlen($qs) ? explode(',', strtoupper($qs)) : array();
	$preselDefault = array();
	$preselCustom = array();
	foreach ($all as $one) {
		if (WT_Gedcom_Tag::isTag($one)) {
			$preselDefault[] = $one;
		} else {
			$preselCustom[] = $one;
		}
	}
}

if ($showthumb) {
	$thumbget='&amp;showthumb=true';
} else {
	$thumbget='';
}

if ($subclick=='all') {
	$all=true;
}

$embed = substr($choose, 0, 1)=="1";
$chooseType = substr($choose, 1);
if ($chooseType!="media" && $chooseType!="0file") {
	$chooseType = "all";
}

//-- force the thumbnail directory to have the same layout as the media directory
//-- Dots and slashes should be escaped for the preg_replace
$srch = "/".addcslashes($MEDIA_DIRECTORY, '/.')."/";
$repl = addcslashes($MEDIA_DIRECTORY."thumbs/", '/.');
$thumbdir = stripcslashes(preg_replace($srch, $repl, $directory));

//-- prevent script from accessing an area outside of the media directory
//-- and keep level consistency
if (($level < 0) || ($level > $MEDIA_DIRECTORY_LEVELS)) {
	$directory = $MEDIA_DIRECTORY;
	$level = 0;
} elseif (preg_match("'^$MEDIA_DIRECTORY'", $directory)==0) {
	$directory = $MEDIA_DIRECTORY;
	$level = 0;
}
// End variables for find media

require WT_ROOT.'includes/specialchars.php';
// End variables for Find Special Character

switch ($type) {
case "indi":
	$controller->setPageTitle(WT_I18N::translate('Find individual ID'));
	break;
case "fam":
	$controller->setPageTitle(WT_I18N::translate('Find Family List'));
	break;
case "media":
	$controller->setPageTitle(WT_I18N::translate('Find media'));
	$action="filter";
	break;
case "place":
	$controller->setPageTitle(WT_I18N::translate('Find Place'));
	$action="filter";
	break;
case "repo":
	$controller->setPageTitle(WT_I18N::translate('Repositories'));
	$action="filter";
	break;
case "note":
	$controller->setPageTitle(WT_I18N::translate('Find Shared Note'));
	$action="filter";
	break;
case "source":
	$controller->setPageTitle(WT_I18N::translate('Find Source'));
	$action="filter";
	break;
case "specialchar":
	$controller->setPageTitle(WT_I18N::translate('Find Special Characters'));
	$action="filter";
	break;
case "facts":
	$controller->setPageTitle(WT_I18N::translate('Find fact tags'));
	echo
		WT_JS_START,
		'jQuery(document).ready(function(){ initPickFact(); });',
		WT_JS_END;
	break;
}
$controller->pageHeader();

echo WT_JS_START;
?>

	function pasterow(id, nam, mnam, label, gend, cond, dom, dob, dod, occu, age, birthpl, fbirthpl, mbirthpl, chilBLD) {
		window.opener.insertRowToTable(id, nam, mnam, label, gend, cond, dom, dob, dod, occu, age, birthpl, fbirthpl, mbirthpl, chilBLD);
		<?php if (!$multiple) echo "window.close();"; ?>
	}

	function pasteid(id, name, thumb) {
		if (thumb) {
			window.opener.<?php echo $callback; ?>(id, name, thumb);
			<?php if (!$multiple) echo "window.close();"; ?>
		} else {
			// GEDFact_assistant ========================
			if (window.opener.document.getElementById('addlinkQueue')) {
				window.opener.insertRowToTable(id, name);
				// Check if Indi, Fam or source ===================
				/*
				if (id.match("I")=="I") {
					var win01 = window.opener.window.open('edit_interface.php?action=addmedia_links&noteid=newnote&pid='+id, 'win01', edit_window_specs);
					if (window.focus) {win01.focus();}
				} else if (id.match("F")=="F") {
					// TODO --- alert('Opening Navigator with family id entered will come later');
				}
				*/
			}
			window.opener.<?php echo $callback; ?>(id);
			if (window.opener.pastename) window.opener.pastename(name);
			<?php if (!$multiple) echo "window.close();"; ?>
		}
	}
	function checknames(frm) {
		if (document.forms[0].subclick) button = document.forms[0].subclick.value;
		else button = "";
		if (frm.filter.value.length<2&button!="all") {
			alert("<?php echo WT_I18N::translate('Please enter more than one character'); ?>");
			frm.filter.focus();
			return false;
		}
		if (button=="all") {
			frm.filter.value = "";
		}
		return true;
	}
<?php
echo WT_JS_END;

$options = array();
$options["option"][]= "findindi";
$options["option"][]= "findfam";
$options["option"][]= "findmedia";
$options["option"][]= "findplace";
$options["option"][]= "findrepo";
$options["option"][]= "findnote";
$options["option"][]= "findsource";
$options["option"][]= "findspecialchar";
$options["option"][]= "findfact";
$options["form"][]= "formindi";
$options["form"][]= "formfam";
$options["form"][]= "formmedia";
$options["form"][]= "formplace";
$options["form"][]= "formrepo";
$options["form"][]= "formnote";
$options["form"][]= "formsource";
$options["form"][]= "formspecialchar";

echo "<div align=\"center\">";
echo "<table class=\"list_table width90\" border=\"0\">";
echo "<tr><td style=\"padding: 10px;\" valign=\"top\" class=\"facts_label03 width90\">"; // start column for find text header

switch ($type) {
case "indi":
	echo WT_I18N::translate('Find individual ID');
	break;
case "fam":
	echo WT_I18N::translate('Find Family List');
	break;
case "media":
	echo WT_I18N::translate('Find media');
	break;
case "place":
	echo WT_I18N::translate('Find Place');
	break;
case "repo":
	echo WT_I18N::translate('Repositories');
	break;
case "note":
	echo WT_I18N::translate('Find Shared Note');
	break;
case "source":
	echo WT_I18N::translate('Find Source');
	break;
case "specialchar":
	echo WT_I18N::translate('Find Special Characters');
	break;
case "facts":
	echo WT_I18N::translate('Find fact tags');
	break;
}

echo "</td>"; // close column for find text header

// start column for find options
echo "</tr><tr><td class=\"list_value\" style=\"padding: 5px;\">";

// Show indi and hide the rest
if ($type == "indi") {
	echo "<div align=\"center\">";
	echo "<form name=\"filterindi\" method=\"get\" onsubmit=\"return checknames(this);\" action=\"find.php\">";
	echo "<input type=\"hidden\" name=\"callback\" value=\"$callback\">";
	echo "<input type=\"hidden\" name=\"action\" value=\"filter\">";
	echo "<input type=\"hidden\" name=\"type\" value=\"indi\">";
	echo "<input type=\"hidden\" name=\"multiple\" value=\"$multiple\">";
/*
	echo "<table class=\"list_table width100\" border=\"0\">";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo WT_I18N::translate('Name contains:'), " <input type=\"text\" name=\"filter\" value=\"";
	if ($filter) echo $filter;
	echo "\">";
	echo "</td></tr>";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo "<input type=\"submit\" value=\"", WT_I18N::translate('Filter'), "\"><br>";
	echo "</td></tr></table>";
*/
	echo "</form></div>";
}

// Show fam and hide the rest
if ($type == "fam") {
	echo "<div align=\"center\">";
	echo "<form name=\"filterfam\" method=\"get\" onsubmit=\"return checknames(this);\" action=\"find.php\">";
	echo "<input type=\"hidden\" name=\"action\" value=\"filter\">";
	echo "<input type=\"hidden\" name=\"type\" value=\"fam\">";
	echo "<input type=\"hidden\" name=\"callback\" value=\"$callback\">";
	echo "<input type=\"hidden\" name=\"multiple\" value=\"$multiple\">";
	echo "<table class=\"list_table width100\" border=\"0\">";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo WT_I18N::translate('Name contains:'), " <input type=\"text\" name=\"filter\" value=\"";
	if ($filter) echo $filter;
	echo "\">";
	echo "</td></tr>";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo "<input type=\"submit\" value=\"", WT_I18N::translate('Filter'), "\"><br>";
	echo "</td></tr></table>";
	echo "</form></div>";
}

// Show media and hide the rest
if ($type == 'media') {
	echo "<div align=\"center\">";
	echo "<form name=\"filtermedia\" method=\"get\" onsubmit=\"return checknames(this);\" action=\"find.php\">";
	echo "<input type=\"hidden\" name=\"choose\" value=\"", $choose, "\">";
	echo "<input type=\"hidden\" name=\"directory\" value=\"", $directory, "\">";
	echo "<input type=\"hidden\" name=\"thumbdir\" value=\"", $thumbdir, "\">";
	echo "<input type=\"hidden\" name=\"level\" value=\"", $level, "\">";
	echo "<input type=\"hidden\" name=\"action\" value=\"filter\">";
	echo "<input type=\"hidden\" name=\"type\" value=\"media\">";
	echo "<input type=\"hidden\" name=\"callback\" value=\"$callback\">";
	echo "<input type=\"hidden\" name=\"subclick\">"; // This is for passing the name of which submit button was clicked
	echo "<table class=\"list_table width100\" border=\"0\">";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo WT_I18N::translate('Media contains:'), " <input type=\"text\" name=\"filter\" value=\"";
	if ($filter) echo $filter;
	echo "\">";
	echo help_link('simple_filter');
	echo "</td></tr>";
	echo "<tr><td class=\"list_label width10\" wstyle=\"padding: 5px;\">";
	echo "<input type=\"checkbox\" name=\"showthumb\" value=\"true\"";
	if ($showthumb) echo "checked=\"checked\"";
	echo "onclick=\"this.form.submit();\">", WT_I18N::translate('Show thumbnails');
	echo "</td></tr>";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo "<input type=\"submit\" name=\"search\" value=\"", WT_I18N::translate('Filter'), "\" onclick=\"this.form.subclick.value=this.name\">&nbsp;";
	echo "<input type=\"submit\" name=\"all\" value=\"", WT_I18N::translate('Display all'), "\" onclick=\"this.form.subclick.value=this.name\">";
	echo "</td></tr></table>";
	echo "</form></div>";
}

// Show place and hide the rest
if ($type == "place") {
	echo "<div align=\"center\">";
	echo "<form name=\"filterplace\" method=\"get\"  onsubmit=\"return checknames(this);\" action=\"find.php\">";
	echo "<input type=\"hidden\" name=\"action\" value=\"filter\">";
	echo "<input type=\"hidden\" name=\"type\" value=\"place\">";
	echo "<input type=\"hidden\" name=\"callback\" value=\"$callback\">";
	echo "<input type=\"hidden\" name=\"subclick\">"; // This is for passing the name of which submit button was clicked
	echo "<table class=\"list_table width100\" border=\"0\">";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo WT_I18N::translate('Place contains:'), " <input type=\"text\" name=\"filter\" value=\"";
	if ($filter) echo $filter;
	echo "\">";
	echo "</td></tr>";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo "<input type=\"submit\" name=\"search\" value=\"", WT_I18N::translate('Filter'), "\" onclick=\"this.form.subclick.value=this.name\">&nbsp;";
	echo "<input type=\"submit\" name=\"all\" value=\"", WT_I18N::translate('Display all'), "\" onclick=\"this.form.subclick.value=this.name\">";
	echo "</td></tr></table>";
	echo "</form></div>";
}

// Show repo and hide the rest
if ($type == "repo") {
	echo "<div align=\"center\">";
	echo "<form name=\"filterrepo\" method=\"get\" onsubmit=\"return checknames(this);\" action=\"find.php\">";
	echo "<input type=\"hidden\" name=\"action\" value=\"filter\">";
	echo "<input type=\"hidden\" name=\"type\" value=\"repo\">";
	echo "<input type=\"hidden\" name=\"callback\" value=\"$callback\">";
	echo "<input type=\"hidden\" name=\"subclick\">"; // This is for passing the name of which submit button was clicked
	echo "<table class=\"list_table width100\" border=\"0\">";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo WT_I18N::translate('Repository contains:'), " <input type=\"text\" name=\"filter\" value=\"";
	if ($filter) echo $filter;
	echo "\">";
	echo "</td></tr>";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo "<input type=\"submit\" name=\"search\" value=\"", WT_I18N::translate('Filter'), "\" onclick=\"this.form.subclick.value=this.name\">&nbsp;";
	echo "<input type=\"submit\" name=\"all\" value=\"", WT_I18N::translate('Display all'), "\" onclick=\"this.form.subclick.value=this.name\">";
	echo "</td></tr></table>";
	echo "</form></div>";
}

// Show Shared Notes and hide the rest
if ($type == "note") {
	echo "<div align=\"center\">";
	echo "<form name=\"filternote\" method=\"get\" onsubmit=\"return checknames(this);\" action=\"find.php\">";
	echo "<input type=\"hidden\" name=\"action\" value=\"filter\">";
	echo "<input type=\"hidden\" name=\"type\" value=\"note\">";
	echo "<input type=\"hidden\" name=\"callback\" value=\"$callback\">";
	echo "<input type=\"hidden\" name=\"subclick\">"; // This is for passing the name of which submit button was clicked
	echo "<table class=\"list_table width100\" border=\"0\">";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo WT_I18N::translate('Shared Note contains:'), " <input type=\"text\" name=\"filter\" value=\"";
	if ($filter) echo $filter;
	echo "\">";
	echo "</td></tr>";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo "<input type=\"submit\" name=\"search\" value=\"", WT_I18N::translate('Filter'), "\" onclick=\"this.form.subclick.value=this.name\">&nbsp;";
	echo "<input type=\"submit\" name=\"all\" value=\"", WT_I18N::translate('Display all'), "\" onclick=\"this.form.subclick.value=this.name\">";
	echo "</td></tr></table>";
	echo "</form></div>";
}

// Show source and hide the rest
if ($type == "source") {
	echo "<div align=\"center\">";
	echo "<form name=\"filtersource\" method=\"get\" onsubmit=\"return checknames(this);\" action=\"find.php\">";
	echo "<input type=\"hidden\" name=\"action\" value=\"filter\">";
	echo "<input type=\"hidden\" name=\"type\" value=\"source\">";
	echo "<input type=\"hidden\" name=\"callback\" value=\"$callback\">";
	echo "<input type=\"hidden\" name=\"subclick\">"; // This is for passing the name of which submit button was clicked
	echo "<table class=\"list_table width100\" border=\"0\">";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo WT_I18N::translate('Source contains:'), " <input type=\"text\" name=\"filter\" value=\"";
	if ($filter) echo $filter;
	echo "\">";
	echo "</td></tr>";
	echo "<tr><td class=\"list_label width10\" style=\"padding: 5px;\">";
	echo "<input type=\"submit\" name=\"search\" value=\"", WT_I18N::translate('Filter'), "\" onclick=\"this.form.subclick.value=this.name\">&nbsp;";
	echo "<input type=\"submit\" name=\"all\" value=\"", WT_I18N::translate('Display all'), "\" onclick=\"this.form.subclick.value=this.name\">";
	echo "</td></tr></table>";
	echo "</form></div>";
}

// Show specialchar and hide the rest
if ($type == "specialchar") {
	echo "<div align=\"center\">";
	echo "<form name=\"filterspecialchar\" method=\"get\" action=\"find.php\">";
	echo "<input type=\"hidden\" name=\"action\" value=\"filter\">";
	echo "<input type=\"hidden\" name=\"type\" value=\"specialchar\">";
	echo "<input type=\"hidden\" name=\"callback\" value=\"$callback\">";
	echo "<table class=\"list_table width100\" border=\"0\">";
	echo "<tr><td class=\"list_label\" style=\"padding: 5px;\">";
	echo "<select id=\"language_filter\" name=\"language_filter\" onchange=\"submit();\">";
	echo "<option value=\"\">", WT_I18N::translate('Change language'), "</option>";
	$language_options = "";
	foreach ($specialchar_languages as $key=>$value) {
		$language_options.= "<option value=\"$key\">$value</option>";
	}
	$language_options = str_replace("\"$language_filter\"", "\"$language_filter\" selected", $language_options);
	echo $language_options;
	echo "</select><br><a href=\"#\" onclick=\"setMagnify()\">", WT_I18N::translate('Magnify'), "</a>";
	echo "</td></tr></table>";
	echo "</form></div>";
}

// Show facts
if ($type == "facts") {
	echo "<div align=\"center\">";
	echo "<form name=\"filterfacts\" method=\"get\" action=\"find.php\" >";
	echo "<input type=\"hidden\" name=\"type\" value=\"facts\">";
	echo "<input type=\"hidden\" name=\"tags\" value=\"$qs\">";
	echo "<input type=\"hidden\" name=\"callback\" value=\"$callback\">";
	echo "<table class=\"list_table width100\" border=\"0\">";
	echo "<tr><td class=\"list_label\" style=\"padding: 5px; font-weight: normal; white-space: normal;\">";
	getPreselectedTags($preselDefault, $preselCustom);
	?>
	<?php echo WT_JS_START; ?>
	// A class representing a default tag
	function DefaultTag(id, name, selected) {
		this.Id=id;
		this.Name=name;
		this.LowerName=name.toLowerCase();
		this._counter=DefaultTag.prototype._newCounter++;
		this.selected=!!selected;
	}
	DefaultTag.prototype= {
		_newCounter:0
		,view:function() {
			var row=document.createElement("tr"),cell,o;
			row.appendChild(cell=document.createElement("td"));
			o=null;
			if (document.all) {
				//Old IEs handle the creation of a checkbox already checked, as far as I know, only in this way
				try {
					o=document.createElement("<input type='checkbox' id='tag"+this._counter+"' "+(this.selected?"checked='checked'":"")+">");
				} catch(e) {
					o=null;
				}
			}
			if (!o) {
				o=document.createElement("input");
				o.setAttribute("id","tag"+this._counter);
				o.setAttribute("type","checkbox");
				if (this.selected) o.setAttribute("checked", "checked");
			}
			o.DefaultTag=this;
			o.ParentRow=row;
			o.onclick=function() {
				this.DefaultTag.selected=!!this.checked;
				this.ParentRow.className=this.DefaultTag.selected?"sel":"unsel";
				Lister.recount();
			};
			cell.appendChild(o);
			row.appendChild(cell=document.createElement("th"));
			cell.appendChild(o=document.createElement("label"));
			o.htmlFor="tag"+this._counter;
			o.appendChild(document.createTextNode(this.Id));
			row.appendChild(cell=document.createElement("td"));
			cell.appendChild(document.createTextNode(this.Name));
			TheList.appendChild(row);
			row.className=this.selected?"sel":"unsel";
		}
	};
	// Some global variable
	var DefaultTags=null /*The list of the default tag*/, TheList=null /* The body of the table that will show the default tabs */;

	// A single-instance class that manage the populating of the table
	var Lister= {
		_curFilter:null
		,_timer:null
		,clear:function() {
			var n=TheList.childNodes.length;
			while (n) TheList.removeChild(TheList.childNodes[--n]);
		}
		,_clearTimer:function() {
			if (this._timer!=null) {
				clearTimeout(this._timer);
				this._timer=null;
			}
		}
		,askRefresh:function() {
			this._clearTimer();
			this._timer=setTimeout("Lister.refreshNow()",200);
		}
		,refreshNow:function(force) {
			this._clearTimer();
			var s=document.getElementById("tbxFilter").value.toLowerCase().replace(/\s+/g," ").replace(/^ | $/g,""),k;
			if (force||(typeof(this._curFilter)!="string")||(this._curFilter!=s)) {
				this._curFilter=s;
				this.clear();
				for (k=0;k<DefaultTags.length;k++) {
					if (DefaultTags[k].LowerName.indexOf(this._curFilter)>=0) DefaultTags[k].view();
				}
			}
		}
		,recount:function() {
			var k,n=0;
			for (k=0;k<DefaultTags.length;k++)
				if (DefaultTags[k].selected)
					n++;
			document.getElementById("layCurSelectedCount").innerHTML=n.toString();
		}
		,showSelected:function() {
			this._clearTimer();
			this.clear();
			for (var k=0;k<DefaultTags.length;k++) {
				if (DefaultTags[k].selected)
					DefaultTags[k].view();
			}
		}
	};

	function initPickFact() {
		var n,i,j,tmp,preselectedDefaultTags="\x01<?php foreach ($preselDefault as $p) echo addslashes($p), '\\x01'; ?>";

		DefaultTags=[<?php
		$firstFact=TRUE;
		foreach (WT_Gedcom_Tag::getPicklistFacts() as $factId => $factName) {
			if (preg_match('/^_?[A-Z0-9]+$/', $factId, $matches)) {
				if ($firstFact) $firstFact=FALSE;
				else echo ',';
				echo 'new DefaultTag("'.addslashes($factId).'","'.addslashes($factName).'",preselectedDefaultTags.indexOf("\\x01'.addslashes($factId).'\\x01")>=0)';
			}
		}
		?>];
		TheList=document.getElementById("tbDefinedTags");
		i=document.getElementById("tbxFilter");
		i.onkeypress=i.onchange=i.onkeyup=function() {
			Lister.askRefresh();
		};
		Lister.recount();
		Lister.refreshNow();
		document.getElementById("btnOk").disabled=false;
	}
	function DoOK() {
		var result=[],k,linearResult,custom;
		for (k=0;k<DefaultTags.length;k++) {
			if (DefaultTags[k].selected) result.push(DefaultTags[k].Id);
		}
		linearResult="\x01"+result.join("\x01")+"\x01";
		custom=document.getElementById("tbxCustom").value.toUpperCase().replace(/\s/g,"").split(",");
		for (k=0;k<custom.length;k++) {
			if (linearResult.indexOf("\x01"+custom[k]+"\x01")<0) {
				linearResult+=custom[k]+"\x01";
				result.push(custom[k]);
			}
		}
		result = result.join(",")
		if (result.substring(result.length-1, result.length)==',') {
			result = result.substring(0, result.length-1);
		}
		pasteid(result);
		window.close();
		return false;
	}
	<?php echo WT_JS_END; ?>
	<div id="layDefinedTags"><table id="tabDefinedTags">
		<thead><tr>
			<th>&nbsp;</th>
			<th><?php echo WT_I18N::translate('Tag'); ?></th>
			<th><?php echo WT_I18N::translate('Description'); ?></th>
		</tr></thead>
		<tbody id="tbDefinedTags">
		</tbody>
	</table></div>

	<table id="tabDefinedTagsShow"><tbody><tr>
		<td><a href="#" onclick="Lister.showSelected();return false"><?php echo WT_I18N::translate('Show only selected tags'); ?> (<span id="layCurSelectedCount"></span>)</a></td>
		<td><a href="#" onclick="Lister.refreshNow(true);return false"><?php echo WT_I18N::translate('Show all tags'); ?></a></td>
	</tr></tbody></table>

	<table id="tabFilterAndCustom"><tbody>
		<tr><td><?php echo WT_I18N::translate('Filter'); ?>:</td><td><input type="text" id="tbxFilter"></td></tr>
		<tr><td><?php echo WT_I18N::translate('Custom tags'); ?>:</td><td><input type="text" id="tbxCustom" value="<?php echo addslashes(implode(',', $preselCustom)); ?>"></td></tr>
	<td><td></tbody></table>

	<table id="tabAction"><tbody><tr>
		<td><button id="btnOk" disabled="disabled" onclick="if (!this.disabled)DoOK();"><?php echo WT_I18N::translate('Accept'); ?></button></td>
		<td><button onclick="window.close();return false"><?php echo WT_I18N::translate('Cancel'); ?></button></td>
	<tr></tbody></table>
	<?php
	echo "</td></tr></table>";
	echo "</form></div>";
}
// end column for find options
echo "</td></tr>";
echo "</table>"; // Close table with find options

echo "<br>";
echo "<a href=\"#\" onclick=\"if (window.opener.showchanges) window.opener.showchanges(); window.close();\">", WT_I18N::translate('Close Window'), "</a><br>";
echo "<br>";

if ($action=="filter") {
	$filter = trim($filter);
	$filter_array=explode(' ', preg_replace('/ {2,}/', ' ', $filter));

	// Output Individual fot GEDFact Assistant ======================
	if ($type == "indi") {
		echo "<table class=\"tabs_table width90\"><tr>";
		$myindilist=search_indis_names($filter_array, array(WT_GED_ID), 'AND');
		if ($myindilist) {
			echo "<td class=\"list_value_wrap\"><ul>";
			usort($myindilist, array('WT_GedcomRecord', 'Compare'));
			foreach ($myindilist as $indi ) {
			//	echo $indi->format_list('li', true);
				
				$nam = $indi->getAllNames();
				$wholename = rtrim($nam[0]['givn'],'*')."&nbsp;".$nam[0]['surname'];
				$fulln = rtrim($nam[0]['givn'],'*')."&nbsp;".$nam[0]['surname'];
				$fulln = str_replace('"', '\'', $fulln); // Replace double quotes
				$fulln = str_replace("@N.N.", "(".WT_I18N::translate('unknown').")", $fulln);
				$fulln = str_replace("@P.N.", "(".WT_I18N::translate('unknown').")", $fulln);
				$givn  = rtrim($nam[0]['givn'],'*');
				$surn  = $nam[0]['surname'];
				if (isset($nam[1])) {
					$fulmn = rtrim($nam[1]['givn'],'*')."&nbsp;".$nam[1]['surname'];
					$fulmn = str_replace('"', '\'', $fulmn); // Replace double quotes
					$fulmn = str_replace("@N.N.", "(".WT_I18N::translate('unknown').")", $fulmn);
					$fulmn = str_replace("@P.N.", "(".WT_I18N::translate('unknown').")", $fulmn);
					$marn  = $nam[1]['surname'];
				} else {
					$fulmn = $fulln;
				}

				//-- Build Indi Parents Family to get FBP and MBP  -----------
				foreach ($indi->getChildFamilies() as $family) {
					$father = $family->getHusband();
					$mother = $family->getWife();
					if (!is_null($father)) {
						$FBP = $father->getBirthPlace();
					}
					if (!is_null($mother)) {
						$MBP = $mother->getBirthPlace();
					}
				}
				if (!isset($FBP)) { $FBP = "UNK, UNK, UNK, UNK"; }
				if (!isset($MBP)) { $MBP = "UNK, UNK, UNK, UNK"; }

				//-- Build Indi Spouse Family to get marriage Date ----------
				foreach ($indi->getSpouseFamilies() as $family) {
					$marrdate = $family->getMarriageDate();
					$marrdate = ($marrdate->minJD()+$marrdate->maxJD())/2;  // Julian
					$children = $family->getChildren();
				}
				if (!isset($marrdate)) { $marrdate = ""; }

				//-- Get Children's Name, DOB, DOD --------------------------
				if (isset($children)) {
					$chBLDarray = Array();
					foreach ($children as $key=>$child) {
						$chnam   = $child->getAllNames();
						$chfulln = rtrim($chnam[0]['givn'],'*')." ".$chnam[0]['surname'];
						$chfulln = str_replace('"', "", $chfulln); // Must remove quotes completely here
						$chfulln = str_replace("@N.N.", "(".WT_I18N::translate('unknown').")", $chfulln);
						$chfulln = str_replace("@P.N.", "(".WT_I18N::translate('unknown').")", $chfulln); // Child's Full Name
						$chdob   = ($child->getBirthDate()->minJD()+$child->getBirthDate()->maxJD())/2; // Child's Date of Birth (Julian)
						if (!isset($chdob)) { $chdob = ""; }
						$chdod   = ($child->getDeathDate()->minJD()+$child->getDeathDate()->maxJD())/2; // Child's Date of Death (Julian)
						if (!isset($chdod)) { $chdod = ""; }
						$chBLD   = ($chfulln.", ".$chdob.", ".$chdod);
						array_push($chBLDarray, $chBLD);
					}
				}
				if (isset($chBLDarray) && $indi->getSex()=="F") {
					$chBLDarray = implode("::", $chBLDarray);
				} else {
					$chBLDarray = '';
				}

				echo "<li>";
					// ==============================================================================================================================
					// NOTES = is equivalent to= function pasterow(id, nam, mnam, label, gend, cond, dom, dob, age, dod, occu, birthpl, fbirthpl, mbirthpl, chilBLD) {
					// ==============================================================================================================================
					echo "<a href=\"#\" onclick=\"window.opener.insertRowToTable(";
						echo "'".$indi->getXref()."', "; // id        - Indi Id
						echo "'".addslashes(strip_tags($fulln))."', "; // nam       - Name
						echo "'".addslashes(strip_tags($fulmn))."', "; // mnam      - Married Name
						echo "'-', "; // label     - Relation to Head of Household
						echo "'".$indi->getSex()."', "; // gend      - Sex
						echo "'S', "; // cond      - Marital Condition
						echo "'".$marrdate."', "; // dom       - Date of Marriage
						echo "'".(($indi->getBirthDate()->minJD() + $indi->getBirthDate()->maxJD())/2)."' ,"; // dob       - Date of Birth
						echo "'".(1901-$indi->getbirthyear())."' ,"; // ~age~     - Census Date minus YOB (Preliminary)
						echo "'".(($indi->getDeathDate()->minJD() + $indi->getDeathDate()->maxJD())/2)."' ,"; // dod       - Date of Death
						echo "'', "; // occu      - Occupation
						echo "'".addslashes($indi->getbirthplace())."', "; // birthpl   - Birthplace
						echo "'".$FBP."', "; // fbirthpl  - Father's Birthplace
						echo "'".$MBP."', "; // mbirthpl  - Mother's Birthplace
						echo "'".$chBLDarray."'"; // chilBLD   - Array of Children (name, birthdate, deathdate)
						echo ");";
						echo "return false;\">";
						echo "<b>".$indi->getFullName()."</b>&nbsp;&nbsp;&nbsp;"; // Name Link

						if ($ABBREVIATE_CHART_LABELS) {
							$born=WT_Gedcom_Tag::getAbbreviation('BIRT');
						} else {
							$born=WT_Gedcom_Tag::getLabel('BIRT');
						}

						echo "</span><br><span class=\"list_item\">", $born, " ", $indi->getbirthyear(), "&nbsp;&nbsp;&nbsp;", $indi->getbirthplace(), "</span>";
					echo "</a>";
				echo "</li>";

			echo "<hr>";
			
			}
			echo '</ul></td></tr><tr><td class="list_label">', WT_I18N::translate('Total individuals: %s', count($myindilist)), '</tr></td>';
		} else {
			echo "<td class=\"list_value_wrap\">";
			echo WT_I18N::translate('No results found.');
			echo "</td></tr>";
		}
		echo "</table>";
	}

	// Output Family
	if ($type == "fam") {
		echo "<table class=\"tabs_table width90\"><tr>";
		// Get the famrecs with hits on names from the family table
		// Get the famrecs with hits in the gedcom record from the family table
		$myfamlist = array_unique(array_merge(
			search_fams_names($filter_array, array(WT_GED_ID), 'AND'),
			search_fams($filter_array, array(WT_GED_ID), 'AND', true)
		));
		if ($myfamlist) {
			$curged = $GEDCOM;
			echo "<td class=\"list_value_wrap\"><ul>";
			usort($myfamlist, array('WT_GedcomRecord', 'Compare'));
			foreach ($myfamlist as $family) {
				echo $family->format_list('li', true);
			}
			echo '</ul></td></tr><tr><td class="list_label">', WT_I18N::translate('Total families: %s', count($myfamlist)), '</tr></td>';
		} else {
			echo "<td class=\"list_value_wrap\">";
			echo WT_I18N::translate('No results found.');
			echo "</td></tr>";
		}
		echo "</table>";
	}

	// Output Media
	if ($type == "media") {
		global $dirs;

		$medialist = get_medialist(true, $directory);

		echo "<table class=\"tabs_table width90\">";
		// Show link to previous folder
		if ($level>0) {
			$levels = explode("/", $directory);
			$pdir = "";
			for ($i=0; $i<count($levels)-2; $i++) $pdir.=$levels[$i]."/";
			$levels = explode("/", $thumbdir);
			$pthumb = "";
			for ($i=0; $i<count($levels)-2; $i++) $pthumb.=$levels[$i]."/";
			$uplink = "<a href=\"find.php?directory={$pdir}&amp;thumbdir={$pthumb}&amp;level=".($level-1)."{$thumbget}&amp;type=media&amp;choose={$choose}\">&nbsp;&nbsp;&nbsp;&lt;-- <span dir=\"ltr\">".$pdir."</span>&nbsp;&nbsp;&nbsp;</a><br>";
		}

		// Start of media directory table
		echo "<table class=\"list_table width90\">";

		// Tell the user where he is
		echo "<tr>";
			echo "<td class=\"topbottombar\" colspan=\"2\">";
				echo WT_I18N::translate('Current directory');
				echo "<br>";
				echo substr($directory, 0, -1);
			echo "</td>";
		echo "</tr>";

		// display the directory list
		if (count($dirs) || $level) {
			sort($dirs);
			if ($level) {
				echo "<tr><td class=\"list_value\" colspan=\"2\">";
				echo $uplink, "</td></tr>";
			}
			echo "<tr><td class=\"descriptionbox\" colspan=\"2\">";
			echo "<a href=\"find.php?directory={$directory}&amp;thumbdir=".str_replace($MEDIA_DIRECTORY, $MEDIA_DIRECTORY."thumbs/", $directory)."&amp;level={$level}{$thumbget}&amp;external_links=http&amp;type=media&amp;choose={$choose}\">", WT_I18N::translate('External objects'), "</a>";
			echo "</td></tr>";
			foreach ($dirs as $indexval => $dir) {
				echo "<tr><td class=\"list_value\" colspan=\"2\">";
				echo "<a href=\"find.php?directory={$directory}{$dir}/&amp;thumbdir={$directory}{$dir}/&amp;level=".($level+1)."{$thumbget}&amp;type=media&amp;choose={$choose}\"><span dir=\"ltr\">", $dir, "</span></a>";
				echo "</td></tr>";
			}
		}
		echo "<tr><td class=\"descriptionbox\" colspan=\"2\"></td></tr>";

		/**
		 * This action generates a thumbnail for the file
		 *
		 * @name $create->thumbnail
		 */
		if ($create=="thumbnail") {
			$filename = $_REQUEST["file"];
			generate_thumbnail($directory.$filename, $thumbdir.$filename);
		}

		echo "<br>";

		// display the images TODO x across if lots of files??
		if (count($medialist) > 0) {
			foreach ($medialist as $indexval => $media) {

				// Check if the media belongs to the current folder
				preg_match_all("/\//", $media["FILE"], $hits);
				$ct = count($hits[0]);

				if (($ct <= $level+1 && $external_links != "http" && !isFileExternal($media["FILE"])) || (isFileExternal($media["FILE"]) && $external_links == "http")) {
					// simple filter to reduce the number of items to view
					$isvalid = filterMedia($media, $filter, 'http');
					if ($isvalid && $chooseType!="all") {
						if ($chooseType=="0file" && !empty($media["XREF"])) $isvalid = false; // skip linked media files
						if ($chooseType=="media" && empty($media["XREF"])) $isvalid = false; // skip unlinked media files
					}
					if ($isvalid) {
						if ($media["EXISTS"] && media_filesize($media["FILE"]) != 0) {
							$imgsize = findImageSize($media["FILE"]);
							$imgwidth = $imgsize[0]+40;
							$imgheight = $imgsize[1]+150;
						}
						else {
							$imgwidth = 0;
							$imgheight = 0;
						}

						echo "<tr>";

						//-- thumbnail field
						if ($showthumb) {
							echo "<td class=\"list_value width10\">";
							if (isset($media["THUMB"])) echo "<a href=\"#\" onclick=\"return openImage('", rawurlencode($media["FILE"]), "', $imgwidth, $imgheight);\"><img src=\"", filename_decode($media["THUMB"]), "\" width=\"50\" alt=\"\"></a>";
							else echo "&nbsp;";
						}

						//-- name and size field
						echo "<td class=\"list_value\">";
						if ($media["TITL"] != "") {
							echo "<b>", htmlspecialchars($media["TITL"]), "</b><br>";
						}
						if (!$embed) {
							echo "<a href=\"#\" onclick=\"pasteid('", addslashes($media["FILE"]), "');\"><span dir=\"ltr\">", $media["FILE"], "</span></a> -- ";
						}
						else echo "<a href=\"#\" onclick=\"pasteid('", $media["XREF"], "', '", addslashes($media["TITL"]), "', '", addslashes($media["THUMB"]), "');\"><span dir=\"ltr\">", $media["FILE"], "</span></a> -- ";
						echo "<a href=\"#\" onclick=\"return openImage('", rawurlencode($media["FILE"]), "', $imgwidth, $imgheight);\">", WT_I18N::translate('View'), "</a><br>";
						if (!$media["EXISTS"] && !isFileExternal($media["FILE"])) echo $media["FILE"], "<br><span class=\"error\">", WT_I18N::translate('The filename entered does not exist.'), "</span><br>";
						else if (!isFileExternal($media["FILE"]) && !empty($imgsize[0])) {
							echo WT_Gedcom_Tag::getLabelValue('__IMAGE_SIZE__', $imgsize[0].' × '.$imgsize[1]);
						}
						if ($media["LINKED"]) {
							echo WT_I18N::translate('This media object is linked to the following:'), "<br>";
							foreach ($media["LINKS"] as $indi => $type_record) {
								if ($type_record!='INDI' && $type_record!='FAM' && $type_record!='SOUR' && $type_record!='OBJE') continue;
								$record=WT_GedcomRecord::getInstance($indi);
								echo '<br><a href="', $record->getHtmlUrl(), '">';
								switch($type_record) {
								case 'INDI':
									echo WT_I18N::translate('View Person'), ' - ';
									break;
								case 'FAM':
									echo WT_I18N::translate('View Family'), ' - ';
									break;
								case 'SOUR':
									echo WT_I18N::translate('View Source'), ' - ';
									break;
								case 'OBJE':
									echo WT_I18N::translate('View Object'), ' - ';
									break;
								}
								echo $record->getFullName(), '</a>';
							}
						} else {
							echo WT_I18N::translate('This media object is not linked to any GEDCOM record.');
						}
						echo "</td>";
					}
				}
			}
		}
		else {
			echo "<tr><td class=\"list_value_wrap\">";
			echo WT_I18N::translate('No results found.');
			echo "</td></tr>";
		}
		echo "</table>";
	}

	// Output Places
	if ($type == "place") {
		echo "<table class=\"tabs_table width90\"><tr>";
		$placelist = array();
		if ($all || $filter) {
			$placelist=find_place_list($filter);
			$ctplace = count($placelist);
			if ($ctplace>0) {
				$revplacelist = array();
				foreach ($placelist as $indexval => $place) {
					$levels = explode(',', $place); // -- split the place into comma seperated values
					$levels = array_reverse($levels); // -- reverse the array so that we get the top level first
					$placetext = "";
					$j=0;
					foreach ($levels as $indexval => $level) {
						if ($j>0) $placetext .= ", ";
						$placetext .= trim($level);
						$j++;
					}
					$revplacelist[] = $placetext;
				}
				uasort($revplacelist, "utf8_strcasecmp");
				echo "<td class=\"list_value_wrap\"><ul>";
				foreach ($revplacelist as $place) {
					echo "<li><a href=\"#\" onclick=\"pasteid('", str_replace(array("'", '"'), array("\'", '&quot;'), $place), "');\">", htmlspecialchars($place), "</a></li>";
				}
				echo "</ul></td></tr>";
				echo "<tr><td class=\"list_label\">", WT_I18N::translate('Places found'), " ", $ctplace;
				echo "</td></tr>";
			}
			else {
				echo "<tr><td class=\"list_value_wrap\"><ul>";
				echo WT_I18N::translate('No results found.');
				echo "</td></tr>";
			}
		}
		echo "</table>";
	}

	// Output Repositories
	if ($type == "repo") {
		echo "<table class=\"tabs_table width90\"><tr>";
		$repo_list = get_repo_list(WT_GED_ID);
		if ($repo_list) {
			echo "<td class=\"list_value_wrap\"><ul>";
			foreach ($repo_list as $repo) {
				echo '<li><a href="', $repo->getHtmlUrl(), '" onclick="pasteid(\'', $repo->getXref(), '\');"><span class="list_item">', $repo->getFullName(),'</span></a></li>';
			}
			echo "</ul></td></tr>";
			echo "<tr><td class=\"list_label\">", WT_I18N::translate('Repositories found'), " ", count($repo_list);
			echo "</td></tr>";
		}
		else {
			echo "<tr><td class=\"list_value_wrap\">";
			echo WT_I18N::translate('No results found.');
			echo "</td></tr>";
		}
		echo "</table>";
	}

	// Output Shared Notes
	if ($type=="note") {
		echo '<table class="tabs_table width90">';
		if ($filter) {
			$mynotelist = search_notes($filter_array, array(WT_GED_ID), 'AND', true);
		} else {
			$mynotelist = get_note_list(WT_GED_ID);
		}
		if ($mynotelist) {
			usort($mynotelist, array('WT_GedcomRecord', 'Compare'));
			echo '<tr><td class="list_value_wrap"><ul>';
			foreach ($mynotelist as $note) {
				echo '<li><a href="', $note->getHtmlUrl(), '" onclick="pasteid(\'', $note->getXref(), '\');"><span class="list_item">', $note->getFullName(),'</span></a></li>';
			}
			echo '</ul></td></tr><tr><td class="list_label">', WT_I18N::translate('Shared Notes found'), ' ', count($mynotelist), '</td></tr>';
		}
		else {
			echo '<tr><td class="list_value_wrap">', WT_I18N::translate('No results found.'), '</td></tr>';
		}
		echo '</table>';
	}

	// Output Sources
	if ($type=="source") {
		echo '<table class="tabs_table width90">';
		if ($filter) {
			$mysourcelist = search_sources($filter_array, array(WT_GED_ID), 'AND', true);
		} else {
			$mysourcelist = get_source_list(WT_GED_ID);
		}
		if ($mysourcelist) {
			usort($mysourcelist, array('WT_GedcomRecord', 'Compare'));
			echo '<tr><td class="list_value_wrap"><ul>';
			foreach ($mysourcelist as $source) {
				echo '<li><a href="', $source->getHtmlUrl(), '" onclick="pasteid(\'', $source->getXref(), '\');"><span class="list_item">', $source->getFullName(),'</span></a></li>';
			}
			echo '</ul></td></tr><tr><td class="list_label">', WT_I18N::translate('Total sources: %s', count($mysourcelist)), '</td></tr>';
		}
		else {
			echo '<tr><td class="list_value_wrap">', WT_I18N::translate('No results found.'), '</td></tr>';
		}
		echo '</table>';
	}

	// Output Special Characters
	if ($type == "specialchar") {
		echo "<table class=\"tabs_table width90\"><tr><td class=\"list_value center wrap\"><br>";
		// lower case special characters
		foreach ($lcspecialchars as $key=>$value) {
			echo '<a class="largechars" href="#" onclick="return window.opener.paste_char(\'', $value, '\');">', $key, '</a> ';
		}
		echo '<br><br>';
		//upper case special characters
		foreach ($ucspecialchars as $key=>$value) {
			echo '<a class="largechars" href="#" onclick="return window.opener.paste_char(\'', $value, '\');">', $key, '</a> ';
		}
		echo '<br><br>';
		// other special characters (not letters)
		foreach ($otherspecialchars as $key=>$value) {
			echo '<a class="largechars" href="#" onclick="return window.opener.paste_char(\'', $value, '\');">', $key, '</a> ';
		}
		echo '<br><br></td></tr></table>';
	}
}
echo "</div>"; // Close div that centers table

// Set focus to the input field
if ($type!='facts') echo WT_JS_START, 'document.filter', $type, '.filter.focus();', WT_JS_END;
