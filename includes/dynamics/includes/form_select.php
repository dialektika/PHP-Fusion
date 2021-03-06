<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_select.php
| Author: Frederick MC CHan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Note on Tags Support
 * $options['tags'] = default $input_value must not be multidimensional array but only as $value = array('1','2','3');
 * For tagging - set tags and multiple to 1
 *
 * @param       $title
 * @param       $input_name
 * @param       $input_id
 * @param array $option_array
 * @param bool  $input_value
 * @param array $options
 * @return string
 */
function form_select($title, $input_name, $input_id, array $option_array = array(), $input_value = FALSE, array $options = array()) {
	global $defender;
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js'></script>");
		add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />");
	}
	$input_value = ($input_value) ? $input_value : '0';
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

	$options = array(
		'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
		'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
		'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? '1' : '0',
		'allowclear' => !empty($options['allowclear']) && $options['allowclear'] == '1' ? 'allowClear: true' : '',
		'multiple' => !empty($options['multiple']) && $options['multiple'] == 1 ? '1' : '0',
		'width' => !empty($options['width']) ? $options['width'] : '',
		'keyflip' => !empty($options['keyflip']) && $options['keyflip'] == 1 ? '1' : '0',
		'tags' => !empty($options['tags']) && $options['tags'] == 1 ? 1 : 0,
		'jsonmode' => !empty($options['jsonmode']) && $options['jsonmode'] == 1 ? '1' : '0',
		'chainable' => !empty($options['chainable']) ? $options['chainable'] : '',
		'maxselect' => !empty($options['maxselect']) && isnum($options['maxselect']) ? $options['maxselect'] : 30,
		'error_text' => !empty($options['error_text']) ? $options['error_text'] : '',
		'class' => !empty($options['class']) ? $options['class'] : '',
		'inline' => !empty($options['inline']) ? $options['inline'] : '',
		'tip' => !empty($options['tip']) ? $options['tip'] : '',
	);

	if ($options['multiple']) {
		if ($input_value) {
			$input_value = construct_array($input_value);
		} else {
			$input_value = array();
		}
	}

	$html = "<div id='$input_id-field' class='form-group ".$options['class']."' ".($options['inline'] && $options['width'] && !$title ? "style='width: ".$options['width']." !important;'" : '').">\n";
	$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : 'col-xs-12 col-sm-12 col-md-12 col-lg-12 p-l-0')."' for='$input_id'>$title ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title=\"".$options['tip']."\"></i>" : '')."</label>\n" : '';
	$html .= ($options['inline']) ? "<div class='col-xs-12 ".($title ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12 col-md-12 col-lg-12 p-l-0")."'>\n" : "";
	if ($options['jsonmode'] || $options['tags']) {
		// json mode.
		$html .= "<div id='$input_id-spinner' style='display:none;'>\n<img src='".IMAGES."loader.gif'>\n</div>\n";
		$html .= "<input ".($options['required'] ? "class='req'" : '')." type='hidden' name='$input_name' id='$input_id' style='width: ".($options['width'] && $title ? $options['width'] : "250px")."'/>\n";
	} else {
		// normal mode
		$html .= "<select name='$input_name' id='$input_id' style='width: ".($options['width'] ? $options['width'] : "250px")."' ".($options['deactivate'] ? " disabled" : "").($options['multiple'] ? " multiple" : "").">";
		$html .= ($options['allowclear']) ? "<option value=''></option>" : '';
		if (is_array($option_array)) {
			foreach ($option_array as $arr => $v) { // outputs: key, value, class - in order
				$chain = ''; $select = '';
				if ($options['keyflip']) { // flip mode = store array values
					$chain = $options['chainable'] ? "class='$v'" : '';
					if ($input_value !== NULL) {
						$select = ($input_value == $v) ? "selected" : "";
					}
					$html .= "<option value='$v' ".$chain." ".$select.">".$v."</option>";
				} else { // normal mode = store array keys
					$chain = ($options['chainable']) ? "class='$arr'" : '';
					$select = '';
					//if ($input_value || $input_value === '0') {
					if ($input_value  !== NULL) {
						$input_value = stripinput($input_value); // not sure if can turn 0 to zero not null.
						$select = (isset($input_value) && $input_value == $arr) ? 'selected' : '';
					}
					$html .= "<option value='$arr' ".$chain." ".$select.">$v</option>";
				}
				unset($arr);
			} // end foreach
		}
		$html .= "</select>\n";
	}
	$html .= "<div id='$input_id-help'></div>";
	$html .= ($options['inline']) ? "</div>\n" : '';
	$html .= "</div>\n";
	if ($options['required']) {
		$html .= "<input class='req' id='dummy-$input_id' type='hidden'>\n"; // for jscheck
	}



	// Generate Defender Tag
	$input_name = ($options['multiple']) ? str_replace("[]", "", $input_name) : $input_name;
	$defender->add_field_session(array(
							 'input_name' 	=> 	$input_name,
							 'type'			=>	'dropdown',
							 'title'		=>	$title2,
							 'id' 			=>	$input_id,
							 'required'		=>	$options['required'],
							 'safemode' 	=> 	$options['safemode'],
							 'error_text'	=> 	$options['error_text']
						 ));
	// Initialize Select2
	// Select 2 Multiple requires hidden DOM.
	if ($options['jsonmode'] == 0) {
		// not json mode (normal)
		$max_js = '';
		if ($options['multiple'] && $options['maxselect']) {
			$max_js = "maximumSelectionSize : ".$options['maxselect'].",";
		}
		$tag_js = '';
		if ($options['tags']) {
			$tag_value = json_encode($option_array);
			$tag_js = ($tag_value) ? "tags: $tag_value" : "tags: []";
		}
		if ($options['required']) {
			add_to_jquery("
			var init_value = $('#".$input_id."').select2('val');
			if (init_value) { $('dummy-".$input_id."').val(init_value);	} else { $('dummy-".$input_id."').val('');	}
			$('#".$input_id."').select2({
				".($options['placeholder'] ? "placeholder: '".$options['placeholder']."'," : '')."
				".$max_js."
				".$options['allowclear']."
				".$tag_js."
			}).bind('change', function(e) {	$('#dummy-".$input_id."').val($(this).val()); });
			");
		} else {
			add_to_jquery("
			$('#".$input_id."').select2({
				".($options['placeholder'] ? "placeholder: '".$options['placeholder']."'," : '')."
				".$max_js."
				".$options['allowclear']."
				".$tag_js."
			});
			");
		}
	} else {
		// json mode
		add_to_jquery("
                var this_data = [{id:0, text: '".$options['placeholder']."'}];
                $('#".$input_id."').select2({
                placeholder: '".$options['placeholder']."',
                data: this_data
                });
            ");
	}
	// For Multiple Callback.
	if (is_array($input_value) && $options['multiple']) { // stores as value;
		$vals = '';
		foreach ($input_value as $arr => $val) {
			$vals .= ($arr == count($input_value)-1) ? "'$val'" : "'$val',";
		}
		add_to_jquery("$('#".$input_id."').select2('val', [$vals]);");
		// For Tags */
		/* foreach ($input_value as $id => $text) {
			$select_array[] = $keyflip ? array('id' => "$text", 'text' => "$text") : array('id' => "$id", 'text' => "$text");
		}
		if (!isset($select_array)) {
			$select_array = array();
		}
		$encoded = json_encode($select_array);
		add_to_jquery("$('#".$input_id."').select2('data', $encoded);"); */
	}
	// alert('Selected value is '+$('#".$input_id."').select2('val'));
	return $html;
}

function form_user_select($title, $input_name, $input_id, $input_value = FALSE, array $options = array()) {
	global $locale, $defender;
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />");
		add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js'></script>");
	}
	$title = (isset($title) && (!empty($title))) ? $title : "";
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	$html = "";

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
		'inline' => !empty($options['inline']) ? $options['inline'] : '',
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
	$path = $options['file'] ? $options['file'] : INCLUDES."search/users.json.php";

	if (!empty($input_value)) {
		// json mode.
		$encoded = $options['file'] ? $options['file'] : user_search($input_value);
	} else {
		$encoded = json_encode(array());
	}

	$defender->add_field_session(array(
									 'input_name' 	=> 	$input_name,
									 'type'			=>	'dropdown',
									 'title'		=>	$title2,
									 'id' 			=>	$input_id,
									 'required'		=>	$options['required'],
									 'safemode' 	=> 	$options['safemode'],
									 'error_text'	=> 	$options['error_text']
								 ));
	add_to_jquery("
                function avatar(item) {
                    if(!item.id) {return item.text;}
                    var avatar = item.avatar;
                    var level = item.level;
                    return '<table><tr><td style=\"\"><img style=\"height:30px;\" class=\"img-rounded\" src=\"".IMAGES."avatars/' + avatar + '\"/></td><td style=\"padding-left:10px\"><div><strong>' + item.text + '</strong></div>' + level + '</div></td></tr></table>';
                }

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
                      	console.log(page);
                        return {results: data};
                      }
                },
                formatSelection: avatar,
                escapeMarkup: function(m) { return m; },
                formatResult: avatar,
                ".$options['allowclear']."
                })".(!empty($encoded) ? ".select2('data', $encoded );" : '')."
            ");
	return $html;
}

/* Returns Json Encoded Object used in form_select_user */
function user_search($user_id) {
	$encoded = json_encode(array());
	$user_id = stripinput($user_id);
	$result = dbquery("SELECT user_id, user_name, user_avatar, user_level FROM " . DB_USERS . " WHERE user_status='0' AND user_id='$user_id'");
	if (dbrows($result) > 0) {
		while ($udata = dbarray($result)) {
			$user_id = $udata['user_id'];
			$user_avatar = ($udata['user_avatar']) ? $udata['user_avatar'] : "noavatar50.png";
			$user_name = $udata['user_name'];
			$user_level = getuserlevel($udata['user_level']);
			$user_opts[] = array('id' => "$user_id", 'text' => "$user_name", 'avatar' => "$user_avatar", "level" => "$user_level");
		}
		if (!isset($user_opts)) {
			$user_opts = array();
		}
		$encoded = json_encode($user_opts);
	}
	return $encoded;
}

// Returns a full hierarchy nested dropdown.
function form_select_tree($title, $input_name, $input_id, $input_value = FALSE, array $options = array(), $db, $name_col, $id_col, $cat_col, $self_id = FALSE, $id = FALSE, $level = FALSE, $index = FALSE, $data = FALSE) {
	global $defender, $locale;
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js' /></script>\n");
		add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />\n");
	}
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_value = isset($input_value) ? stripinput($input_value) : '';
	$name = isset($name_col) ? stripinput($name_col) : '';
	$id_col = (isset($id_col) && ($id_col != "")) ? stripinput($id_col) : '';
	$cat_col = (isset($cat_col) && ($cat_col != "")) ? stripinput($cat_col) : '';

	/* Documentation Included */
	$options += array(
		'required' => !empty($options['required']) && $options['required'] == 1 ? 1 : 0, // to set required field
		'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? 1 : 0, // to init safemode filter
		'allowclear' => !empty($options['allowclear']) ? 1 : 0, // to have an "X" to reset selection
		'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : $locale['choose'], // to add a placeholder
		'deactivate' => !empty($options['deactivate']) ? 1 : 0, // to disable the entire field
		'multiple' => !empty($options['multiple']) ? 1 : 0, // to make this field a multiple input
		'width' => !empty($options['width']) ? $options['width'] : '250px', // to set a preset width of the selector
		'parent_value' => !empty($options['parent_value']) ? $options['parent_value'] : $locale['root'], // to change the name of the root item. need add_parent_opts
		'add_parent_opts' => !empty($options['add_parent_opts']) && $options['add_parent_opts'] == 1 ? 1 : 0, // add a 'parent' or not.
		'no_root' => !empty($options['no_root']) && $options['no_root'] == 1 ? 1 : 0, // remove 'root'
		'show_current' => !empty($options['show_current']) && $options['show_current'] == 1 ? 1 : 0, // place a (Current Item) marker
		'error_text' => !empty($options['error_text']) ? $options['error_text'] : '', // set error text on fail validation
		'class' => !empty($options['class']) ? $options['class'] : '', // append a css class to the selector
		'inline' => !empty($options['inline']) ? $options['inline'] : '', // make the label and field on the same row
		'disable_opts' => !empty($options['disable_opts']) ? $options['disable_opts'] : '', // disable selection , accept either exploded array or imploded text
		'hide_disabled' => !empty($options['hide_disabled']) && $options['hide_disabled'] == 1 ? 1 : 0,  // to hide any disabled opts. required $options['disabled_opts']
		'tip' => !empty($options['tip']) ? $options['tip'] : '',
	);

	$allowclear = ($options['placeholder'] && $options['multiple'] || $options['allowclear']) ? "allowClear:true" : '';
	$multiple = $options['multiple'] ? 'multiple' : '';
	$disable_opts = '';
	if ($options['disable_opts']) {
		$disable_opts = is_array($options['disable_opts']) ? $options['disable_opts'] : explode(',', $options['disable_opts']);
	}

	/* Child patern */
	$opt_pattern = str_repeat("&#8212;", $level);
	if (!$level) {
		$level = 0;
		$html = "<div id='$input_id-field' class='form-group ".$options['class']."' ".($options['inline'] && $options['width'] && !$title ? "style='width: ".$options['width']." !important;'" : '').">\n";
		$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : 'col-xs-12 col-sm-12 col-md-12 col-lg-12 p-l-0')."' for='$input_id'>$title ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title=\"".$options['tip']."\"></i>" : '')."</label>\n" : '';
		$html .= ($options['inline']) ? "<div class='col-xs-12 ".($title ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12 col-md-12 col-lg-12")." p-l-0'>\n" : "";
	}
	if ($level == 0) {
		$html = &$html;
		add_to_jquery("
		$('#".$input_id."').select2({
		placeholder: '".$options['placeholder']."',
		$allowclear
		});
		");
		$html .= "<select name='$input_name' style='".($options['width'] ? "width: ".$options['width']." " : 'min-width:250px;')."' id='$input_id' class='".$options['class']."' ".($options['deactivate'] == 1 ? "readonly" : '')." $multiple>";
		$html .= $options['allowclear'] ? "<option value=''></option>" : '';
		if ($options['no_root'] !== 1) { // api options to remove root from selector. used in items creation.
			$this_select = '';
			if ($input_value !== NULL) {
				if ($input_value == '0') {
					$this_select = 'selected';
				}
			}
			$html .= ($options['add_parent_opts'] == 1) ? "<option value='0' ".$this_select.">$opt_pattern ".$locale['parent']."</option>\n" : "<option value='0' ".$this_select." >$opt_pattern ".$options['parent_value']."</option>\n";
		}
		$index = dbquery_tree($db, $id_col, $cat_col);
		$data = dbquery_tree_data($db, $id_col, $cat_col);
	}
	if (!$id) {
		$id = 0;
	}

	if (isset($index[$id])) {
		foreach ($index[$id] as $key => $value) {
			//$hide = $disable_branch && $value == $self_id ? 1 : 0;
			$html = &$html;
			$name = $data[$value][$name_col];
			$name = PHPFusion\QuantumFields::parse_label($name);
			$select = ($input_value !== "" && ($input_value == $value)) ? 'selected' : '';
			$disabled = $disable_opts && in_array($value, $disable_opts) ? 1 : 0;
			$hide = $disabled && $options['hide_disabled'] ? 1 : 0;
			// do a disable for filter_opts item.
			$html .= (!$hide) ? "<option value='$value' ".$select." ".($disable_opts && in_array($value, $disable_opts) ? 'disabled' : '')." >$opt_pattern $name ".($options['show_current'] && $self_id == $value ? '(Current Item)' : '')."</option>\n" : '';
			if (isset($index[$value]) && (!$hide)) {
				$html .= form_select_tree($title, $input_name, $input_id, $input_value, $options, $db, $name_col, $id_col, $cat_col, $self_id, $value, $level+1, $index, $data);
			}
		}
	}
	if (!$level) {
		$html = &$html;
		$html .= "</select>";
		$html .= "<div id='$input_id-help'></div>";
		$html .= ($options['inline']) ? "</div>\n" : '';
		$html .= "</div>\n";
		if ($options['required']) {
			$html .= "<input class='req' id='dummy-$input_id' type='hidden'>\n"; // for jscheck
		}
//		$html .= ($options['inline'] && $title) ? "</div>\n" : '';
		//$html .= "</div>\n";
		//$html .= "</div>\n";
		$defender->add_field_session(array(
			 'input_name' 	=> 	$input_name,
			 'type'			=>	'dropdown',
			 'title'		=>	$title2,
			 'id' 			=>	$input_id,
			 'required'		=>	$options['required'],
			 'safemode' 	=> 	$options['safemode'],
			 'error_text'	=> 	$options['error_text']
		 ));
	}
	return $html;
}
?>