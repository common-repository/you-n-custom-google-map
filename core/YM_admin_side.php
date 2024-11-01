<?php

/**
 * @author giacomo@you-n.com
 * 
 * 22/07/13
 * 
 */
class YM_admin_side {

    /**
     * Contructor class 
     */
    function __construct($settings, $userSettings) {
        $this->YM_Settings = $settings;
        $this->YM_userConfiguration = $userSettings;
        //menu render
        add_action('admin_menu', array($this, 'addYMMenu'), 11);
        //save options
        add_action('admin_post_YMUpdateSettings', array(&$this, 'YMUpdateSettings'));
        add_action('admin_post_YMUpdateUserSettings', array(&$this, 'YMUpdateUserSettings'));

        //AJAX
        add_action("wp_ajax_uploadYMMarker", array(&$this, "uploadYMMarker"));
        add_action("wp_ajax_getInfoMarker", array(&$this, "YM_getInfoMarker"));
        add_action("wp_ajax_updateInfoMarker", array(&$this, "YM_updateInfoMarker"));
        add_action("wp_ajax_deleteMyMarker", array(&$this, "confirmDeleteMyMarker"));
        add_action("wp_ajax_getThumbConfiguration", array(&$this, "YM_getNewThumbConfiguration"));
        add_action("wp_ajax_createMarkerCopy", array(&$this, "YM_createMarkerCopy"));
        add_action("wp_ajax_updateMarkerPosition", array(&$this, "YM_updateMarkerPosition"));
        add_action("wp_ajax_getLatLongMarker", array(&$this, "YM_getLatLongMarker"));
        add_action("wp_ajax_updateInDBSceltaBordo", array(&$this, "YM_updateInDBSceltaBordo"));
        if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'ym_map') {
            //Script/Style inclusion function
            add_action('admin_enqueue_scripts', array(&$this, 'YMScriptsEnqueue'));
        }
        add_action('admin_menu', array(&$this, 'YM_AddBoxAdmin'));
        add_action('save_post', array(&$this, 'YM_SaveDataAdm'));
    }

    /**
     * Add a custom box to include the plugin in a page
     */
    function YM_AddBoxAdmin() {
        global $YM_meta_box;
        $YM_meta_box = array(
            'id' => 'youMap',
            'title' => 'Mappa You-n',
            'page' => 'page',
            'context' => 'side',
            'priority' => 'low',
            'fields' => array(
                array(
                    'name' => 'Mostra Mappa nella pagina',
                    'id' => 'YMMap',
                    'type' => 'checkbox'
                )
            )
        );
        add_meta_box($YM_meta_box['id'], $YM_meta_box['title'], array(&$this, 'YM_GestBoxAdmin'), $YM_meta_box['page'], $YM_meta_box['context'], $YM_meta_box['priority']);
    }

    function YM_GestBoxAdmin() {
        global $post, $YM_meta_box;
        echo '<input type="hidden" name="sight_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';

        echo '<table class="form-table">';

        foreach ($YM_meta_box['fields'] as $field) {
            // get current post meta data
            $meta = get_post_meta($post->ID, $field['id'], true);
            $checked = '';
            if ($meta != '')
                $checked = 'checked="checked"';
            echo '<tr>',
            '<th style="width:50%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
            '<td>';
            echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '" ' . $checked . ' />';
            echo '<td>',
            '</tr>';
        }

        echo '</table>';
    }

    function YM_SaveDataAdm() {
        global $YM_meta_box, $post;
        if (isset($post) && $post->ID) {
            if (!wp_verify_nonce($_POST['sight_meta_box_nonce'], basename(__FILE__))) {
                return $post->ID;
            }
            // check autosave
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post->ID;
            }

            // check permissions
            if ('page' == $_POST['post_type']) {
                if (!current_user_can('edit_page', $post_id)) {
                    return $post->ID;
                }
            } elseif (!current_user_can('edit_post', $post_id)) {
                return $post->ID;
            }
            foreach ($YM_meta_box['fields'] as $field) {
                $old = get_post_meta($post->ID, $field['id'], true);
                $new = $_POST[$field['id']];

                if ($new && $new != $old) {
                    update_post_meta($post->ID, $field['id'], $new);
                } elseif ('' == $new && $old) {
                    delete_post_meta($post->ID, $field['id'], $old);
                }
            }
            return;
        }
        return;
    }

    /**
     * END 
     */

    /**
     * Add top level menu in beckend page
     */
    function addYMMenu() {
        if (function_exists('add_menu_page')) {
            $messaggio = '';
            add_menu_page('You Map', 'You Map', 'administrator', 'ym_map', array(&$this, 'YMrenderAdminPage'), YM_PLUGIN_URI . '/images/you-map-menu-thumb.png', '27.1'); //PASSAGGIO PER RIFERIMENTO YM_PLUGIN_URI . '/images/you-slider-menu-thumb.png'
            if (isset($_GET['YMUpdateSettings']) || isset($_GET['YMUpdateSettings'])) {
                $messaggio = 'Modifiche Apportate con successo!';
            }
            if (isset($_GET['YMUpdateFailed'])) {
                $messaggio = 'WARNING: Si è verificato un errore!!!';
            }

            $this->showMyMessage($messaggio);
        }
    }

    /**
     * chiama il php che genera la pagina Home del plugin
     * @subpackage home.php
     * 
     * 
     * @param null
     * 
     * @return null 
     */
    function YMRenderAdminPage() {
        include YM_PLUGIN_URL . '/includes/YMFormConfiguration.php';
        include YM_PLUGIN_URL . '/includes/YMFormUserConfiguration.php';
    }

    /**
     * Style and Script inclusion for beckend side
     */
    function YMScriptsEnqueue() {
        $url = admin_url('admin-ajax.php');
        $attivoDisattivo = $this->YM_Settings['active'];
        if ($attivoDisattivo) {
            $attivoDisattivo = 'attivo';
        } else {
            $attivoDisattivo = 'disattivo';
        }
        $urloModal = YM_PLUGIN_URI;
        $zoom = $this->YM_Settings['zoom'];
        $visualizzaMarker = FALSE;
        if ($this->YM_userConfiguration[0]['lat'] != 0) {
            $visualizzaMarker = true;
        }
        ?>
        <script type="text/javascript">
            var urlo = '<?php echo $url; ?>';
            var urloModal = '<?php echo $urloModal; ?>';
            var attivoDisattivo = '<?php echo $attivoDisattivo; ?>' ;
            var zoomConfig ='<?php echo $zoom ?>'
            var responsive;
            var tipoMappa = '<?php echo $this->YM_Settings['tipoMappa'] ?>';
        <?php if ($this->YM_Settings['responsive'] == TRUE) { ?>
                responsive='responsive';
        <?php } else { ?>
                responsive='custom'
        <?php } ?>
            var visualizzaMarker=false;
            var arrayMarker = new Array();
        <?php if ($visualizzaMarker) { ?>
                visualizzaMarker=true;
            <?php $arrayMarker = $this->getMarkerObj(); ?>
                    arrayMarker = <?= json_encode($arrayMarker) ?> ;
        <?php } ?>
            var spessore = <?php echo $this->YM_Settings['spessore'] ?>
        </script>
        <?php
        wp_register_style('YMBESTYLE', YM_PLUGIN_URI . '/css/default.css', array(), '1.0', 'screen');
        wp_enqueue_style('YMBESTYLE');

        wp_register_script('gestMappa', YM_PLUGIN_URI . '/js/gestMappa.js', array(), '1.0', TRUE);
        wp_register_script('blockUI', YM_PLUGIN_URI . '/js/blockUI.js', array(), '1.0', TRUE);
        wp_register_script('fileUpload', YM_PLUGIN_URI . '/js/fileuploader.js', array(), '1.0', TRUE);
        wp_register_script('gestUpload', YM_PLUGIN_URI . '/js/gestUpload.js', array(), '1.0', TRUE);
        wp_register_script('GoogleAPI', 'http://maps.google.com/maps/api/js?libraries=places&sensor=true', array(), '1.0', TRUE);

        wp_enqueue_script('iris');
        wp_enqueue_script('jquery-ui-draggable');

        wp_enqueue_script('gestMappa');
        wp_enqueue_script('blockUI');
        wp_enqueue_script('fileUpload');
        wp_enqueue_script('gestUpload');
        wp_enqueue_script('GoogleAPI');
    }

    /**
     * Funzione per visualizzare il messaggio di conferma attuazione modifiche
     * 
     * @param string $messaggio
     * @return HTML
     * @uses funzione anonima 
     */
    function showMyMessage($messaggio) {
        if ($messaggio != '') {
            $msg = '<div class="updated fade"><p>' . $messaggio . '</p></div>';
            add_action('admin_notices', create_function('', "echo '$msg';"));
        }
    }

    /**
     * Update settings Map option in DB
     * @param void
     * @return void, viene eseguito un redirect
     * @uses updateSettingsInDatabase()
     */
    function YMUpdateSettings() {
        if (!current_user_can('manage_options'))
            wp_die('Non hai i permessi per modificare le opzioni del plugin!');

        $attivo = $_POST[YM_SETTINGS]['active'];
        if ($attivo == 'null')
            $attivo = TRUE;

        if ($attivo == 'false')
            $attivo = FALSE;

        if ($attivo == 'true')
            $attivo = TRUE;
        $responsive = $_POST[YM_SETTINGS]['responsive'];
        $isResp;
        if ($responsive == "responsive") {
            $isResp = TRUE;
        }
        if ($responsive == "custom") {
            $isResp = FALSE;
        }

        $width = $_POST[YM_SETTINGS]['width'];
        $campi = array(
            'version' => '0.2', //la metto a mano perchè non la passo in post
            'active' => $attivo,
            'width' => $width,
            'height' => $_POST[YM_SETTINGS]['height'],
            'responsive' => $isResp,
            'zoom' => $_POST[YM_SETTINGS]['zoom'],
            'tipoMappa' => $_POST[YM_SETTINGS]['tipoMappa'],
            'color' => $_POST[YM_SETTINGS]['color'],
            'spessore' => $this->YM_Settings['spessore']
        );
        $this->YM_Settings = $campi;
        $this->updateSettingsInDatabase();
        $referrer = str_replace(array('&YMUpdateSettings', '&YMDeleteSettings'), '', $_POST['_wp_http_referer']);
        wp_redirect($referrer . '&YMUpdateSettings');
    }

    /**
     * Validate configuration and save user Map in DB
     * @param void
     *  @return  
     */
    function YMUpdateUserSettings() {
        $ok = true;

        if (!current_user_can('manage_options'))
            wp_die('Non hai i permessi per modificare le opzioni del plugin!');

        $idMappa;
        $max = 0;
        $flag = false;
        if ($this->YM_userConfiguration[0]['lat'] == 0) { //primo inserimento
            $idMappa = 0;
        } else {
            //CALCOLARE IL PRIMO ID LIBERO!!
            foreach ($this->YM_userConfiguration as $k => $el) {//devo verificarlo: posso aver canellato una foto "immezzo"
                if ($k >= $max)
                    $max = $k;
            }
            $idMappa = ++$max;
            $flag = TRUE;
        }

        $nomeImg = (string) '';
        $nomeImg = $_POST[YM_USER_MAP]['nome'];

        $pathImg = (string) '';
        $pathImg = $_POST[YM_USER_MAP]['path'];

        $tooltip = (string) '';
        $toolTip = $_POST[YM_USER_MAP]['tooltip'];


        if (strlen($_POST[YM_USER_MAP]['lat']) > 0 && isset($_POST[YM_USER_MAP]['lat'])) {
            $lat = $_POST[YM_USER_MAP]['lat'];
        } else {
            $ok = false;
        }
        if (strlen($_POST[YM_USER_MAP]['lgt']) > 0 && isset($_POST[YM_USER_MAP]['lgt'])) {

            $lgt = $_POST[YM_USER_MAP]['lgt'];
        } else {
            $ok = FALSE;
        }
        if ($ok) {
            $campi = array(
                'version' => '0.2',
                'isUsed' => false,
                'lat' => $lat,
                'lgt' => $lgt,
                'marker' => $pathImg,
                'toolTip' => $toolTip
            );
            /*
             * Conservo i vecchi valori e appendo i nuovi 
             * TODO prevedere indice per eliminare foto da eliminare (volendo anche per l'ordine nello slider :-)
             * 
             */
            if ($flag) { //inserimenti successivi
                $flag = FALSE;
                $this->YM_userConfiguration[$idMappa] = $campi;
            } else { //primo inserimento
                $this->YM_userConfiguration[$idMappa] = $campi;
            }
            //var_dump($this->YM_userConfiguration);exit;
            if ($this->updateUserConfiguration()) {
                //echo "Ha ritornato true";exit;
            } else {
                // echo "Ha ritornato FALSE";exit;
            }


            $referrer = str_replace(array('&YMUpdateSettings', '&YMDeleteSettings'), '', $_POST['_wp_http_referer']);
            wp_redirect($referrer . '&YMUpdateSettings');
        } else {
            $referrer = str_replace(array('&YMUpdateSettings', '&YMDeleteSettings'), '', $_POST['_wp_http_referer']);
            wp_redirect($referrer . '&YMUpdateFailed');
        }
    }

    /**
     * restituisce l'html contentente le miniature dei marker, se presenti altrimentio visualizza quello di default di Google
     * 
     * @param int $width width delle foto
     * @param int $heigth heigth delle foto
     * @return string 
     */
    function YM_getThumbnail($width, $height) {
        $str = '';
        if ($this->YM_userConfiguration[0]['lat'] != '0') {
            foreach ($this->YM_userConfiguration as $elem => $k) {
                foreach ($k as $i => $elemento) {
                    if (in_array($i, array('marker'))) {
                        if ($elemento == '') {
                            $elemento = YM_PLUGIN_URI . '/images/default-marker.png';
                        }
                        $str.='<div class="YM-wrapper-thumb"><div class="area-modifica"></div><div class="area-cancellami"></div><div class="area-wrap"></div>';

                        $str.= '<img id="slider-foto_' . $elem . '" src="' . $elemento . '" width="' . $width . '" height="' . $height . '"  />';
                        $str.='</div>';
                    }
                }
            }
        }
        return $str;
    }

    function YM_getNewThumbConfiguration() {
        $return = array('thumbnail' => array(), 'riuso' => array());
        $return['thumbnail'] = $this->YM_getThumbnail(60, 60);
        $return['riuso'] = $this->YM_getExistMarker(60, 60);

        echo json_encode($return);
        exit;
    }

    /**
     * Return exist marker
     * @param type $width
     * @param type $height 
     * 
     * @return string HTML
     */
    function YM_getExistMarker($width, $height) {
        $str = '';
        $markers = array();
        $isUsed = false;
        foreach ($this->YM_userConfiguration as $elem => $k) {
            foreach ($k as $i => $elemento) {
                if (in_array($i, array('isUsed')))
                    $isUsed = $elemento;
                if (in_array($i, array('marker')) && !$isUsed) {
                    if (!in_array($elemento, $markers) && $elemento != '') {
                        $str.='<div class="YM-wrapper-thumb">';

                        $str.= '<img id="want-foto_' . $elem . '" src="' . $elemento . '" width="' . $width . '" height="' . $height . '"  />';
                        $str.='</div>';
                        $markers[] = $elemento;
                    }
                }
            }
        }
        return $str;
    }

    /**
     * return array marker for use in js
     * @param void
     * @return array 
     * TODO: DA COMPLETARE
     */
    function getMarkerObj() {
        $markers = array();
        foreach ($this->YM_userConfiguration as $elem => $k) {
            foreach ($k as $i => $elemento) {
                $markers[$elem]['id_marker'] = $elem;
                if (in_array($i, array('marker')))
                    $markers[$elem]['immagine'] = $elemento;
                if (in_array($i, array('lat')))
                    $markers[$elem]['latitudine'] = $elemento;
                if (in_array($i, array('lgt')))
                    $markers[$elem]['longitudine'] = $elemento;
                if (in_array($i, array('toolTip')))
                    $markers[$elem]['toolTip'] = $elemento;
            }
        }
        return $markers;
    }

    /**
     * Get markert info to ajax request
     * 
     * @param $_POST id_marker
     * @return JSON 
     */
    function YM_getInfoMarker() {
        if (!isset($_POST['id_marker']))
            wp_die('You have no access to this informations');
        $return = array('ok' => TRUE, 'latitudine' => '', 'longitudine' => '', 'toolTip' => '');

        $id = $_POST['id_marker'];
        $info = $this->YM_userConfiguration[$id];
        $isDelete = false;

        if (isset($_POST['for_delete']) && $_POST['for_delete'] == '1')
            $isDelete = TRUE;


        $return['latitudine'] = $info['lat'];
        $return['longitudine'] = $info['lgt'];
        $return['toolTip'] = $info['toolTip'];
        if ($isDelete) {
            if (strlen($info['marker']) > 1) {
                $return['marker'] = $info['marker'];
            } else {
                $return['marker'] = '';
            }
        }

        echo json_encode($return);
        exit;
    }

    /**
     * Return Lat Lng via AJAX request for center map 
     * 
     * @param id_marker
     * @return JSON
     */
    function YM_getLatLongMarker() {
        if (!isset($_POST['id_marker']))
            wp_die('You have no access to this informations');

        $return = array('ok' => true, 'latitudine' => '', 'longitudine' => '');

        $latitudine = $this->YM_userConfiguration[$_POST['id_marker']]['lat'];
        $longitudine = $this->YM_userConfiguration[$_POST['id_marker']]['lgt'];

        $return['latitudine'] = $latitudine;
        $return['longitudine'] = $longitudine;

        echo json_encode($return);
        exit;
    }

    /**
     * Update marker info via ajax request
     * 
     * @param $_POST marker info
     * @return JSON 
     */
    function YM_updateInfoMarker() {
        if (!isset($_POST['id_marker']))
            wp_die('You have no access to this informations');
        $return = array('ok' => TRUE, 'message' => '', 'markers' => array());
        $id = $_POST['id_marker'];
        if (isset($_POST['latitudine']) && strlen($_POST['latitudine']) > 0) {
            $latitudine = $_POST['latitudine'];
        } else {
            $return['ok'] = FALSE;
        }
        if (isset($_POST['longitudine']) && strlen($_POST['longitudine']) > 0) {
            $longitudine = $_POST['longitudine'];
        } else {
            $return['ok'] = FALSE;
        }
        $toolTip = $_POST['toolTip'];
        if ($return['ok'] == true) {
            $this->YM_userConfiguration[$id]['lat'] = $latitudine;
            $this->YM_userConfiguration[$id]['lgt'] = $longitudine;
            $this->YM_userConfiguration[$id]['toolTip'] = $toolTip;
            $this->updateUserConfiguration();
            $return['message'] = __('Marker successfully updated!', 'YM-language');
            $return['markers'] = $this->YM_userConfiguration;
        } else {
            $return['message'] = __('Error in Marker update, please try again!', 'YM-language');
        }
        echo json_encode($return);
        exit;
    }

    /**
     * Update marker position via AJAX reques
     * 
     * @param new position, marker ID
     * 
     * @return JSON 
     */
    function YM_updateMarkerPosition() {
        if (!isset($_POST['id_marker']))
            wp_die('You have no access to this informations');
        $return = array('ok' => TRUE, 'message' => '', 'markers' => array());
        if (isset($_POST['id_marker']) && isset($_POST['latitudine']) && isset($_POST['longitudine'])) {
            if (strlen($_POST['id_marker']) < 2) {
                $id = $_POST['id_marker'];
                $latitudine = $_POST['latitudine'];
                $longitudine = $_POST['longitudine'];
            } else {
                $return['message'] = __('Error in marker position update, please try again later', 'YM-language');
                $return['ok'] = FALSE;
            }
        } else {
            $return['ok'] = FALSE;
        }

        if ($return['ok']) {

            $this->YM_userConfiguration[$id]['lat'] = $latitudine;
            $this->YM_userConfiguration[$id]['lgt'] = $longitudine;
            if ($this->updateUserConfiguration()) {
                $return['message'] = __('Marker position have been updated', 'YM-language');
                $return['markers'] = $this->YM_userConfiguration;
            } else {
                $return['ok'] = FALSE;
                $return['message'] = __('Error in marker update position, try again please', 'YM-language');
            }
        } else {
            $return['message'] = __('Forse non dovresti essere qui', 'YM-language');
        }
        $return['markers'] = $this->YM_userConfiguration;
        echo json_encode($return);
        exit;
    }

    /**
     * Delete user Marker from wp_option via AJAX request.
     * 
     * @param id_marker $_POST
     * @return JSON 
     */
    function confirmDeleteMyMarker() {
        if (!isset($_POST['id_marker']))
            wp_die('You have no access to this informations');
        $return = array('ok' => TRUE, 'message' => '', 'markers' => array());



        if (isset($_POST['id_marker'])) {
            $id = $_POST['id_marker'];
        } else {
            $return['ok'] = false;
            $return['message'] = __('You have no access to this information', 'YM-language');
        }

        if ($return['ok'] == TRUE) {
            $nomeMarker = $this->YM_userConfiguration[$id]['marker'];
            if (strlen($nomeMarker) > 2 && isset($nomeMarker)) {
                $baseName = pathinfo($nomeMarker);
                $baseName = $baseName['basename'];
                /*
                 * RIDEFINISCO IL PATH DI UPLOAD 
                 * con la chiamata ajax non ho la classe istanziata!!!!
                 */
                $YsliderUpload = YM_PLUGIN_URL;
                $YsliderUpload.='/marker/';

                if (file_exists($YsliderUpload . $baseName)) { //controllo se l'immagine esiste
                    if (unlink($YsliderUpload . $baseName)) { //dopo l'immaggine unsetto l'array
                        if (sizeof($this->YM_userConfiguration) > 1) { //NON è l'ultimo marker
                            unset($this->YM_userConfiguration[$id]);
                            $nuovo = array();
                            foreach ($this->YM_userConfiguration as $marker => $el) {
                                $nuovo[] = $this->YM_userConfiguration[$marker];
                            }
                            $this->YM_userConfiguration = $nuovo;
                        } else { //è l'ultimo marker!! TODO: TESTARE QUESTO CASO!!!
                            unset($this->YM_userConfiguration[$id]);
                            $this->YM_userConfiguration = $this->YM_getBaseUserSettings();
                        }
                    } else {
                        $return['message'] = __('Unable to delete your marker image, please try again later', 'YM-language');
                    }
                }
            } else { //ripeto la cancellazione, senza toccare il marker.
                if (sizeof($this->YM_userConfiguration) > 1) { //NON è l'ultimo marker
                    unset($this->YM_userConfiguration[$id]);
                    $nuovo = array();
                    foreach ($this->YM_userConfiguration as $marker => $el) {
                        $nuovo[] = $this->YM_userConfiguration[$marker];
                    }
                    $this->YM_userConfiguration = $nuovo;
                } else { //è l'ultimo marker!! TODO: TESTARE QUESTO CASO!!!
                    unset($this->YM_userConfiguration[$id]);
                    $this->YM_userConfiguration = $this->YM_getBaseUserSettings();
                }
            }
        }
        if ($this->updateUserConfiguration()) {
            $return['message'] = __('Operation of cancellation is successful', 'YM-language');
        } else {
            $return['message'] = __('Operation of cancellation is not successful', 'YM-language');
            $return['OK'] = FALSE;
        }

        $return['markers'] = $this->YM_userConfiguration;
        echo json_encode($return);
        exit;
    }

    /**
     * Update my option in DB
     */
    function YM_updateInDBSceltaBordo() {
        if (!isset($_POST['spessore']))
            wp_die('You have no access to this informations');

        $risposta = array('ok' => TRUE, 'message' => '', 'spessore' => 0);
        $spessore = $_POST['spessore'];
        $messaggio = (string) '';
        if ($this->YM_Settings['spessore'] != $spessore) {

            $this->YM_Settings['spessore'] = $spessore;
            if ($this->updateSettingsInDatabase()) {


                if (WPLANG == 'it_IT') {
                    $messaggio = "Hai impostato uno spessore di " . $spessore . " px";
                } else {
                    $messaggio = "You have set a thickness of " . $spessore . " px";
                }
            } else {
                if (WPLANG == 'it_IT') {
                    $messaggio = "Si è verificato un errore durante il salvataggio della tua impostazione, ti preghiamo di riprovare";
                } else {
                    $messaggio = "There was an error saving your settings, please try again";
                }
            }
        }else{
            
                if (WPLANG == 'it_IT') {
                    $messaggio = "Hai impostato uno spessore di " . $spessore . " px";
                } else {
                    $messaggio = "You have set a thickness of " . $spessore . " px";
                }
        }
        $risposta['message'] = $messaggio;
        $risposta['spessore'] = $spessore;
        echo json_encode($risposta);
        exit;
    }

    /**
     * Return default settings for custom Map
     * @return Array
     */
    function YM_getBaseUserSettings() {
        return array(
            array(
                'version' => '0.2',
                'isUsed' => false,
                'lat' => '0',
                'lgt' => '0',
                'marker' => '',
                'toolTip' => ''
            )
        );
    }

    /**
     * Update in DB user configuration
     * @param void
     * @return bool 
     */
    function updateUserConfiguration() {
        if (update_option(YM_USER_MAP, $this->YM_userConfiguration)) {
            return true;
        } else {
            return FALSE;
        }
    }

    /**
     * Create a copy of a marker (with similar name)
     * 
     * @param name of the marker (by AJAX request)
     * 
     * @return JSON 
     */
    function YM_createMarkerCopy() {
        if (!isset($_POST['nome_marker']) || !isset($_POST['id_da_copiare']))
            wp_die('You have no access to this informations');
        $return = array('ok' => TRUE, 'message' => '', 'marker' => '');

        $YsliderUpload = YM_PLUGIN_URI;
        $YsliderUpload.='/marker/';

        $YM_marker_abs = YM_PLUGIN_URL . '/marker/';

        $aggiunta = 1;

        $nomeEsistente = $_POST['nome_marker'];

        $ext = pathinfo($nomeEsistente, PATHINFO_EXTENSION);
        $nomeEsistente = basename($nomeEsistente, '.' . $ext);
        $primaDiAggiunta = $nomeEsistente . '.' . $ext;
        $nomeEsistente.='_' . $aggiunta;

        while (file_exists($YM_marker_abs . $nomeEsistente . '.' . $ext)) {
            $aggiunta++;
            $tmp = split('_', $nomeEsistente);
            $nomeEsistente = $tmp[0] . '_' . $aggiunta;
        }
        $dopoAggiunta = $YM_marker_abs . $nomeEsistente . '.' . $ext;
        $nomeEsistente = $YsliderUpload . $nomeEsistente . '.' . $ext;

        if (copy($YM_marker_abs . $primaDiAggiunta, $dopoAggiunta)) {
            $return['messaggio'] = 'Marker copiato!';
            $return['marker'] = $nomeEsistente;
            $this->YM_userConfiguration[$_POST['id_da_copiare']]['isUsed'] = true;
            $this->updateUserConfiguration();
        } else {
            $return['ok'] = FALSE;
        }
        echo json_encode($return);
        exit;
    }

    /**
     * Update the object configuration $this->YsliderSettings sul DB 
     * @param void
     * @return void
     */
    function updateSettingsInDatabase() {
        return update_option(YM_SETTINGS, $this->YM_Settings);
    }

    /**
     * Upload foto in AJAX
     * @param void
     * @return JSON parsificato in HTML
     */
    function uploadYMMArker() {

        /*
         * RIDEFINISCO IL PATH DI UPLOAD 
         * con la chiamata ajax non ho la classe istanziata!!!!
         */
        $uploads_info = YM_PLUGIN_URI;
        $YsliderUpload = YM_PLUGIN_URL;
        $YsliderUpload.='/marker/';
        $pathAssoluto = $uploads_info . '/marker/';


        $allowedExtensions = array("png");
// max file size in bytes
        $sizeLimit = 2.5 * 1024 * 1024;

        $uploader = new YMFileUploader($allowedExtensions, $sizeLimit);
        $path = $YsliderUpload; //attenzione in produzione!!!!!!!
        $result = $uploader->handleUpload($path);
// to pass data through iframe you will need to encode all html tags
        if (isset($result['success'])) {

            $img = $YsliderUpload . $result['file'];
            $result['newImg'] = $pathAssoluto . $result['file'];
            $result['path'] = $YsliderUpload . $result['file'];
        }
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
        exit;
    }

}

/**
 * PARTE DI FILE UPLOLAD!!! 
 */

/**
 * Handle file uploads via XMLHttpRequest
 */
class YMUploadedFileXhr {

    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);

        if ($realSize != $this->getSize()) {
            return false;
        }

        $target = fopen($path, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);

        return true;
    }

    function getName() {
        return $_GET['qqfile'];
    }

    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])) {
            return (int) $_SERVER["CONTENT_LENGTH"];
        } else {
            throw new Exception('Getting content length is not supported.');
        }
    }

}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class YMUploadedFileForm {

    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if (!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)) {
            return false;
        }
        return true;
    }

    function getName() {
        return $_FILES['qqfile']['name'];
    }

    function getSize() {
        return $_FILES['qqfile']['size'];
    }

}

class YMFileUploader {

    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760) {
        $allowedExtensions = array_map("strtolower", $allowedExtensions);

        $this->allowedExtensions = $allowedExtensions;
        $this->sizeLimit = $sizeLimit;

        $this->checkServerSettings();

        if (isset($_GET['qqfile'])) {
            $this->file = new YMUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new YMUploadedFileForm();
        } else {
            $this->file = false;
        }
    }

    private function checkServerSettings() {
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));

        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit) {
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");
        }
    }

    private function toBytes($str) {
        $val = trim($str);
        $last = strtolower($str[strlen($str) - 1]);
        switch ($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }

    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE) {
        if (!is_writable($uploadDirectory)) {
            return array('error' => "Server error. Upload directory isn't writable.");
        }

        if (!$this->file) {
            return array('error' => 'No files were uploaded.');
        }

        $size = $this->file->getSize();

        if ($size == 0) {
            return array('error' => 'File is empty');
        }

        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }

        $pathinfo = pathinfo($this->file->getName());
        $filename = str_replace(" ", "-", $pathinfo['filename']);
        $filename .='-';
        $filename .= date('Ymdh'); //md5(uniqid());
        $ext = $pathinfo['extension'];

        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of ' . $these . '.');
        }

        if (!$replaceOldFile) {
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 9999);
            }
        }

        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)) {
            return array('success' => true, 'file' => $filename . '.' . $ext);
        } else {
            return array('error' => 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
    }

}
?>
