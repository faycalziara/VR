<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
ob_start();
require_once("../../db/connection.php");
require_once("../functions.php");
session_write_close();
$settings = get_settings();
$tmp_languages = get_languages_viewer();
$array_languages = $tmp_languages[0];
$default_language = $tmp_languages[1];
$array_input_lang = array();
$query_lang = "SELECT * FROM svt_categories_lang;";
$result_lang = $mysqli->query($query_lang);
if($result_lang) {
    if ($result_lang->num_rows > 0) {
        while($row_lang=$result_lang->fetch_array(MYSQLI_ASSOC)) {
            $language=$row_lang['language'];
            unset($row_lang['language']);
            if(!array_key_exists($row_lang['id_category'], $array_input_lang)) {
                $array_input_lang[$row_lang['id_category']]=array();
            }
            $array_input_lang[$row_lang['id_category']][$language]=$row_lang;
        }
    }
}
$html = "";
foreach (get_categories() as $category) {
    $html .= "<tr class='cat_tr' data-id='".$category['id']."' id='cat_tr_".$category['id']."'>";
    $html .= "<td style='width:90px;'><input type='number' class='form-control position' value='".$category['position']."'></td>";
    $html .= "<td>
                <div style=\"margin-bottom: 5px;\" class=\"form-group\">
                    <button class=\"btn btn-sm btn-primary icon_picker_cat\" type=\"button\" id=\"GetIconPicker_cat_".$category['id']."\" data-iconpicker-input=\"input#cat_".$category['id']."_icon\" data-iconpicker-preview=\"i#cat_".$category['id']."_icon_preview\">"._("Select Icon")."</button>
                    <input readonly type=\"hidden\" id=\"cat_".$category['id']."_icon\" name=\"Icon\" value=\"".$category['icon']."\" required=\"\" placeholder=\"\" autocomplete=\"off\" spellcheck=\"false\">
                    <div style=\"vertical-align: middle;width:40px;\" class=\"icon-preview d-inline-block ml-1\" data-toggle=\"tooltip\" title=\"\">
                        <i style=\"font-size: 24px;\" id=\"cat_".$category['id']."_icon_preview\" class=\"".$category['icon']."\"></i>
                    </div>
                    <button onclick='remove_cat_icon(".$category['id'].");' class='btn btn-sm btn-danger ".((empty($category['icon'])) ? 'disabled' : '')." btn_delete_cat_icon'><i class='fas fa-remove'></i></button>
                </div>
                </td>";
    $html .= "<td>";
    $html .= "<input id='cat_name_{$category['id']}' type='text' class='form-control cat_name name' value='".$category['name']."'>";
    foreach ($array_languages as $lang) {
        if($lang!=$default_language) {
            $html .= "<input style=\"display:none;\" type=\"text\" class=\"form-control input_lang cat_name_lang\" data-target-id=\"cat_name_{$category['id']}\" data-lang=\"$lang\" value=\"".htmlspecialchars($array_input_lang[$category['id']][$lang]['name'])."\" />";
        }
    }
    $html .= "</td>";
    $html .= "<td><input type='text' class='form-control background' value='".$category['background']."'></td>";
    $html .= "<td><input type='text' class='form-control color' value='".$category['color']."'></td>";
    $html .= "<td><button onclick='modal_delete_category(".$category['id'].");' class='btn btn-sm btn-danger'><i class='far fa-trash-alt'></i> "._("delete")."</button></td>";
    $html .= "</tr>";
}
ob_end_clean();
echo json_encode(array("status"=>"ok","html"=>$html));