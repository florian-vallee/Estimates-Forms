<?php

class Ef_widget extends WP_Widget {
    
    public function __construct()
    {
        parent::__construct(
            'ef_form',
            'Estimation form',
            array(
                'description'   => 'Un formulaire d\'estimation de prix selon les champs désigner.',
                'classname'     => 'container',
            )
        );
        
    }

    /**
     * Echoes the widget content.
     *
     * Sub-classes should over-ride this function to generate their widget code.
     * Effectivement cette fonction a été surchargé pour faire apparaitre le contenu désiré.
     * 
     * @since 2.8.0
     * @param array $args     Display arguments including 'before_title', 'after_title',
     *                        'before_widget', and 'after_widget'.
     * @param array $instance The settings for the particular instance of the widget.
     */
    public function widget($args, $instance)
    {        
        global $wpdb;
        
        $select_form = "SELECT * FROM {$wpdb->prefix}ef_formulaire_settings WHERE `nom` = '{$instance['title']}'";
        $query_select_form = $wpdb->query($select_form);
        if ($query_select_form === 1 ) {
            $results_form = $wpdb->get_results($select_form);
            foreach ($results_form as $data_form) {
                $id_form    = $data_form->id_form;
                $nom        = $data_form->nom;
                $css_class  = $data_form->css_class;
            }

            echo $args['before_widget'];
            echo $args['before_title'];
            echo apply_filters('widget_title', $instance['title']);
            echo $args['after_title'];

            ?>
            <form action="" id="<?php echo $data_form->id_form; ?>" class="<?php echo $data_form->css_class; ?>">
                <p><?php echo $instance['description'] ?></p>
                <?php 
                $select_input = "SELECT * FROM {$wpdb->prefix}ef_input_settings WHERE `input_id_form` = '{$id_form}'";
                $query_select_input = $wpdb->query($select_input);
                if (!empty($query_select_input)) {
                    $results_input = $wpdb->get_results($select_input);
                    foreach ($results_input as $data_input) {
                        switch ($data_input->input_type) {
                            case 'text':
                                ?>
                                <label for="<?php echo $data_input->id_input ?>"><?php echo $data_input->nom; ?></label>
                                <input type="<?php echo$data_input->input_type; ?>" name="<?php echo $data_input->nom; ?>" id="<?php echo $data_input->id_input ?>">
                                <?php
                                break;
                            
                            // If checked the data send to server is the value attribute. 
                            case 'checkbox':
                                ?>
                                <label for="<?php echo $data_input->id_input ?>"><?php echo $data_input->nom; ?></label>
                                <input type="<?php echo$data_input->input_type; ?>" name="<?php echo $data_input->nom; ?>" id="<?php echo $data_input->id_input ?>" value="<?php echo $data_input->input_valeur ?>">
                                <?php
                                break;

                            case 'range':
                                ?>
                                <div class ="form-group">
                                    <label for="<?php echo $data_input->id_input ?>"><?php echo $data_input->nom; ?>:</label>
                                    <output class="ml-3" name="output_range" id="output_range_id" value="5"> (5 pages minimum) </output>
                                    <input name="<?php echo $data_input->nom; ?>" id="<?php echo $data_input->id_input ?>" type="<?php echo $data_input->input_type ?>" class="<?php echo $data_input->css_class ?>" valeur="5" step="5" min="5" max="20"  oninput="output_range_id.value = <?php echo $data_input->id_input ?>.value">
                                </div>
                                <?php
                                break;

                            case 'number':
                                ?>
                                <label for="<?php echo $data_input->id_input ?>"><?php echo $data_input->nom; ?></label>
                                <input type="<?php echo$data_input->input_type; ?>" name="<?php echo $data_input->nom; ?>" id="<?php echo $data_input->id_input ?>">
                                <?php
                                break;

                            case 'select':
                                ?>
                                <label for="<?php echo $data_input->id_input ?>"><?php echo $data_input->nom; ?></label>
                                <select name="" id=""></select>
                                <?php
                                    if ( !empty($data->options) ) {
                                        foreach ($variable as $key => $value) {
                                            # code...
                                        }
                                        ?>
                                        <option value=""></option>
                                        <?php
                                    }
                                break;
                            case 'radio':
                                ?>
                                
                                <?php
                        }
                        
                        ?>
                        <?php
                    }
                }
                ?>
                
                    <!-- <div class ="form-group">
                        <label for="input_range"> Nombre de page (min 5):</label>
                        <output class="ml-3" name="output_range" id="output_range_id">5</output>
                        <input class="form-control-range" type="range" name="test" id="input_range" min= "5" max="25" step="5" value="5" oninput="output_range_id.value = input_range.value">
                    </div> -->
                <br>
                <button type="submit" class="btn btn-primary">envoyer</button>
                <button type="submit" class="btn btn-primary">TOTAL</button>
            </form>
            <?php

            echo $args['after_widget'];
        }
        }
        
        

    /**
     * Outputs the settings update form.
     * Fonction surchargee, all widgets settings stored in widget_{widget_ID} option key in {prefix}_options table ----> soit wp-option table.
     * Afin de comprendre le comportement des options ajouter au widget j'ai rajouter une description. 
     * Cette description s'enregistre bien en base de donnée quand j'utilise le bouton enregistrer de wordpress.
     * @since 2.8.0
     * @param Array $instance Current settings.
     * @return String Default return is 'noform'.
     */
    public function form($instance){
        $description = isset($instance['description']) ? $instance['description'] : '';
        $title = isset($instance['title']) ? $instance['title'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo  $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_name( 'description' ); ?>"><?php _e( 'Description:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>" type="text" value="" />
        </p>
        <p><?php echo  $description; ?></p>
        <?php
        }
}

?>