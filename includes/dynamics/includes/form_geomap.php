<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System Version 8
| Copyright (C) 2002 - 2013 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Form API - Address Input Based
| Filename: form_geomap.php
| Author: Frederick MC Chan (Hien)
| Sub-Author: Joakim Falke
| Communities of PHP-Fusion at PHP-Fusion.co.uk
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

function form_address($title = FALSE, $input_name, $input_id, $input_value = FALSE, array $options = array()) {
	global $locale, $defender;
	$title = (isset($title) && (!empty($title))) ? $title : "";
	$title2 = (isset($title) && (!empty($title))) ? $title : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js'></script>");
		add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />");
	}
	$countries = array();
	require INCLUDES."geomap/geomap.inc.php";
	// NOTE (remember to parse readback value as of '|' seperator)
	if (isset($input_value) && (!empty($input_value))) {
		if (!is_array($input_value)) {
			$input_value = construct_array($input_value, "", "|");
		}
	} else {
		$input_value['0'] = "";
		$input_value['1'] = "";
		$input_value['2'] = "";
		$input_value['3'] = "";
		$input_value['4'] = "";
		$input_value['5'] = "";
	}

	$options += array(
		'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
		'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
		'width' => !empty($options['width']) ?  $options['width']  : '100%',
		'class' => !empty($options['class']) ?  $options['class']  : '',
		'inline' => !empty($options['inline']) ?  $options['inline']  : '',
		'error_text' => !empty($options['error_text']) ?  $options['error_text']  : '',
		'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? '1'  : '0',
		'flag' => !empty($options['flag']) ?  $options['flag']  : '',
	);

	$html = "";
	$html .= "<div id='$input_id-field' class='form-group clearfix m-b-10 ".$options['class']."' >\n";
	$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$title ".($options['required'] ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : '';
	$html .= "<div class='row'>\n";
	$html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 m-b-10'>\n";
	$html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."-street' value='".$input_value['0']."' placeholder='".$locale['street1']." ".($options['required'] ? '*':'')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
	$html .= "<div id='$input_id-street-help'></div>";
	$html .= "</div>\n";

	$html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 m-b-10'>\n";
	$html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."-street2' value='".$input_value['1']."' placeholder='".$locale['street2']."' ".($options['deactivate'] == "1" ? "readonly" : '')." />";
	$html .= "</div>\n";

	$html .= "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5 m-b-10'>\n";
	$html .= "<select name='".$input_name."[]' id='$input_id-country' style='width:100%;'/>\n";
	$html .= "<option value=''></option>";
	foreach ($countries as $arv => $countryname) { // outputs: key, value, class - in order
		$country_key = str_replace(" ", "-", $countryname);
		$select = ($input_value[2] == $country_key) ? "selected" : '';
		$html .= "<option value='$country_key' ".$select.">$countryname</option>";
	}
	$html .= "</select>\n";
	$html .= "<div id='$input_id-country-help'></div>";
	$html .= "</div>\n";
	$html .= "<div class='col-xs-12 col-sm-7 col-md-7 col-lg-7 m-b-10'>\n";
	$html .= "<div id='state-spinner' style='display:none;'>\n<img src='".IMAGES."loader.gif'>\n</div>\n";
	$html .= "<input type='hidden' name='".$input_name."[]' id='$input_id-state' value='".$input_value['3']."' style='width:100%;' />\n";
	$html .= "<div id='$input_id-state-help'></div>";
	$html .= "</div>\n";
	$html .= "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5 m-b-10'>\n";
	$html .= "<input type='text' name='".$input_name."[]' id='".$input_id."-city' class='form-control' value='".$input_value['4']."' placeholder='".$locale['city']."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
	$html .= "<div id='$input_id-city-help'></div>";
	$html .= "</div>\n";
	$html .= "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4 m-b-10'>\n";
	$html .= "<input type='text' name='".$input_name."[]'  id='".$input_id."-postcode' class='form-control' value='".$input_value['5']."' placeholder='".$locale['postcode']."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
	$html .= "<div id='$input_id-postcode-help'></div>";
	$html .= "</div>\n";
	$html .= "</div>\n"; // close inner row
	$html .= ($options['inline']) ? "</div>\n" : "";
	$html .= "</div>\n";
	$defender->add_field_session(array(
		 'input_name' 	=> 	$input_name,
		 'type'			=>	'address',
		 'title'		=>	$title2,
		 'id' 			=>	$input_id,
		 'required'		=>	$options['required'],
		 'safemode'		=> 	$options['safemode'],
		 'error_text'	=> 	$options['error_text']
	 ));

	$flag_function = ''; $flag_plugin = '';
	if ($options['flag']) {
		$flag_function = "
		function show_flag(item) {
		if(!item.id) {return item.text;}
		var icon = '".IMAGES."small_flag/flag_'+ item.id.replace(/-/gi,'_').toLowerCase() +'.png';
		return '<img style=\"float:left; margin-right:5px; margin-top:3px;\" src=\"' + icon + '\"/></i>' + item.text;
		}";
		$flag_plugin = "
         formatResult: show_flag,
		 formatSelection: show_flag,
		 escapeMarkup: function(m) { return m; },
		";
	}

	add_to_jquery("
	".$flag_function."
    $('#$input_id-country').select2({
	$flag_plugin
	placeholder: 'Country ".($options['required'] == 1 ? '*':'')."'
    });
    $('#".$input_id."-country').bind('change', function(){
    	var ce_id = $(this).val();
        $.ajax({
        url: '".INCLUDES."geomap/form_geomap.json.php',
        type: 'GET',
        data: { id : ce_id },
        dataType: 'json',
        beforeSend: function(e) {
        //$('#state-spinner').show();
        $('#".$input_id."-state').hide();
        },
        success: function(data) {
        //$('#state-spinner').hide();
        $('#".$input_id."-state').select2({
        placeholder: 'Select State ".($options['required'] == 1 ? '*':'')."',
        allowClear: true,
        data : data
        });
        },
        error : function() {
		$.pnotify({title: 'Error! Something went wrong.',
		text: 'We cannot read the database, please recheck source codes.',
		icon: 'pngicon-l-badge-multiply',
		width: 'auto'
		});
        }
        })
	}).trigger('change');
	");
	return $html;
}

function form_location($title, $input_name, $input_id, $input_value = FALSE, array $options = array()) {
	global $userdata, $locale, $defender;
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />");
		add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js'></script>");
	}
	if (!defined('PLOCATION')) {
		define('PLOCATION', true);
		add_to_jquery("
		function plocation(item) {
			if(!item.id) {return item.text;}
			var flag = item.flag;
			var region = item.region;
			return '<table><tr><td style=\"\"><img style=\"height:16px;\" src=\"".IMAGES."/' + flag + '\"/></td><td style=\"padding-left:10px\"><div>' + item.text + '</div></div></td></tr></table>';
		}
		");
	}


	$title = (isset($title) && (!empty($title))) ? $title : "";
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";

	$options = array(
		'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
		'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : $locale['choose-user'],
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
		'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? '1' : '0',
		'allowclear' => !empty($options['allowclear']) && $options['allowclear'] == '1' ? 'allowClear: true' : '',
		'multiple' => !empty($options['multiple']) && $options['multiple'] == 1 ? '0' : '1',
		'width' => !empty($options['width']) ? $options['width'] : '',
		'keyflip' => !empty($options['keyflip']) && $options['keyflip'] == 1 ? '1' : '0',
		'tags' => !empty($options['tags']) && $options['tags'] == 1 ? '1' : '0',
		'jsonmode' => !empty($options['jsonmode']) && $options['jsonmode'] == 1 ? '1' : '0',
		'chainable' => !empty($options['chainable']) ? $options['chainable'] : '',
		'maxselect' => !empty($options['maxselect']) && isnum($options['maxselect']) ? $options['maxselect'] : 1,
		'error_text' => !empty($options['error_text']) ? $options['error_text'] : '',
		'class' => !empty($options['class']) ? $options['class'] : '',
		'inline' => !empty($options['inline']) && $options['inline'] == 1 ? 1 : 0,
		'file' => !empty($options['file']) ? $options['file'] : '',
	);
	$length = "minimumInputLength: 1,";

	$html = "";
	$html .= "<div id='$input_id-field' class='form-group ".$options['class']."'>\n";
	$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3  p-l-0" : '')."' for='$input_id'>$title ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($options['inline']) ? "<div class='col-xs-12 ".($title ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12 col-md-12 col-lg-12")." p-l-0'>\n" : "";
	$html .= "<input ".($options['required'] ? "class='req'" : '')." type='hidden' name='$input_name' id='$input_id' data-placeholder='".$options['placeholder']."' style='width:100%;' ".($options['deactivate'] ? 'disabled' : '')." />";
	if ($options['deactivate']) {
		$html .= form_hidden("", $input_name, $input_id, $input_value);
	}
	$html .= "<div id='$input_id-help'></div>";
	$html .= $options['inline'] ? "</div>\n" : '';
	$html .= "</div>\n";
	$defender->add_field_session(array(
			 'input_name' 	=> 	$input_name,
			 'type'			=>	'textbox',
			 'title'		=>	$title2,
			 'id' 			=>	$input_id,
			 'required'		=>	$options['required'],
			 'safemode'		=> 	$options['safemode'],
			 'error_text'	=> 	$options['error_text']
		 ));

	$path = $options['file'] ? $options['file'] : INCLUDES."search/location.json.php";

	if (!empty($input_value)) {
		// json mode.
		$encoded = $options['file'] ? $options['file'] : location_search($input_value);
	} else {
		$encoded = json_encode(array());
	}



	add_to_jquery("
                $('#".$input_id."').select2({
                $length
                multiple: true,
                maximumSelectionSize: ".$options['maxselect'].",
                placeholder: '".$options['placeholder']."',
                ajax: {
                url: '$path',
                dataType: 'json',
                data: function (term, page) {
                        return {q: term};
                      },
                      results: function (data, page) {
                        return {results: data};
                      }
                },
                formatSelection: plocation,
                escapeMarkup: function(m) { return m; },
                formatResult: plocation,
                ".$options['allowclear']."
                })".(!empty($encoded) ? ".select2('data', $encoded );" : '')."
            ");
	return $html;
}

function map_country($states, $country) {
	$states_list = array();
	$flag = "small_flag/flag_".str_replace('-', '_', strtolower($country)).".png";
	foreach($states[$country] as $states_name) {
		$states_list[] = array('id' => "$states_name", 'text' => "$states_name, $country", 'flag' => "$flag", "region" => "$country");
	}
	return $states_list;
}

function map_region($states) {
	$states_list = array();
	foreach($states as $country_name => $country_states) {
		$flag = "small_flag/flag_".str_replace('-', '_', strtolower($country_name)).".png";
		foreach($country_states as $states_name) { // add [] to prevent duplicate since Sabah exist in Yemen and Malaysia.
			$states_list[$states_name][] = array('id' => "$states_name", 'text' => "$states_name, $country_name", 'flag' => "$flag", "region" => "$country_name");
		}
	}
	return $states_list;
}

/* Returns Json Encoded Object used in form_select_user */
function location_search($q) {
	include INCLUDES."geomap/geomap.inc.php";
	// since search is on user_name.
	$found = 0;
	foreach(array_keys($states) as $k) { // type the country then output full states
		if (preg_match('/^'.$q.'/', $k, $matches)) {
			$states_list = map_country($states, $k);
			//print_p($states_list);
			return json_encode($states_list);
			$found = 1;
		}
	}
	if (!$found) { // a longer version
		$region_list = map_region($states);
		if (array_key_exists($q, $region_list)) {
			//print_p($region_list[$q]);
			return json_encode($region_list[$q]);
		}
	}
}


?>