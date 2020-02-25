<?php
class Ef_queries
{
    public function __construct()
    {
        $this->ef_register_form_settings_custom_table();
        $this->ef_register_input_settings_custom_table();
    }

    /**
     * Cette fonction permet d'enregistrer les options de formulaire dans la table fourni par le plugin.
     */
    public function ef_register_form_settings_custom_table()
    {
        global $wpdb;
        $options = get_option( 'ef_form_option' );
        
        $data = array(
            'id_form'   => $options['ef_formulaire_id'],
            'nom'       => $options['ef_formulaire_name'],
            'css_class' => $options['ef_formulaire_css']
        );
        
        $select = "SELECT * FROM {$wpdb->prefix}ef_formulaire_settings WHERE `id_form` = '{$data['id_form']}'";
        $query_select = $wpdb->query($select);
        
        if ($query_select < 1 && $data['id_form'] != '') {
            $insert = "INSERT INTO {$wpdb->prefix}ef_formulaire_settings (id_form, nom, css_class) VALUES ('{$data['id_form']}', '{$data['nom']}', '{$data['css_class']}')";
            $wpdb->query($insert);

            //delete wp_option data "ef_form_option"
            $delete = "DELETE FROM {$wpdb->prefix}options WHERE option_name = 'ef_form_option'";
            $wpdb->query($delete);
        }
        else {
            //delete wp_option data "ef_form_option"
            $delete = "DELETE FROM {$wpdb->prefix}options WHERE option_name = 'ef_form_option'";
            $wpdb->query($delete);
        }
        
    }

    public function ef_register_input_settings_custom_table()
    {
        global $wpdb;
        $options = get_option( 'ef_input_option' );
        
        $data = array(
            'nom'               => $options['ef_input_name'],
            'id_input'          => $options['ef_input_id'],
            'input_id_form'     => $options['ef_input_id_form'],
            'input_type'        => $options['ef_input_type'],
            'input_valeur'      => $options['ef_input_valeur'],
            'input_css'         => $options['ef_input_css'],
            'input_description' => $options['ef_input_description']
        );
        
        $select = "SELECT * FROM {$wpdb->prefix}ef_input_settings WHERE `id_input` = '{$data['id_input']}'";
        $query_select = $wpdb->query($select);
        
        if ($query_select < 1 && $data['id_input'] != '') {
            $insert = "INSERT INTO {$wpdb->prefix}ef_input_settings (id_input, nom, input_type, input_valeur, css_class, input_id_form, input_description) VALUES ('{$data['id_input']}', '{$data['nom']}', '{$data['input_type']}', '{$data['input_valeur']}', '{$data['input_css']}', '{$data['input_id_form']}', '{$data['input_description']}')";
            $wpdb->query($insert);

            //delete wp_option data "ef_input_option"
            $delete = "DELETE FROM {$wpdb->prefix}options WHERE option_name = 'ef_input_option'";
            $wpdb->query($delete);
        }
    }
}

?>