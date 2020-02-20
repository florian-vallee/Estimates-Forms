<?php

class Ef_widget extends WP_Widget {
    
    public function __construct()
    {
        parent::__construct(
            'ef_form',
            'Estimates form',
            array(
                'description'   => 'Un formulaire d\'estimation de prix selon les champs désigner.',
                'classname'     => 'container mt-3 pb-2',
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
            } // EO foreach

            echo $args['before_widget'];
            echo $args['before_title'];
            echo apply_filters('widget_title', $instance['title']);
            echo $args['after_title'];

            ?>
            <div id="container_form" class="container">
                <form method="POST" action="" id="<?php echo $data_form->id_form; ?>" class="form-block-end <?php echo $data_form->css_class; ?>">
                    <p><?php echo $instance['description'] ?></p>
                    <?php 
                    $select_input = "SELECT * FROM {$wpdb->prefix}ef_input_settings WHERE `input_id_form` = '{$id_form}'";
                    $query_select_input = $wpdb->query($select_input);
                    if (!empty($query_select_input)) {
                        $results_input = $wpdb->get_results($select_input);
                        foreach ($results_input as $data_input) {
                            // Ici le switch me permet un affichage spécifique selon le type de l'input enregistrer en BDD. 
                            switch ($data_input->input_type) { 
                                case 'checkbox':
                                    ?>
                                    
                                    <div class="form-group d-flex justify-content-between">
                                        <label class="" for="<?php echo $data_input->id_input ?>"><?php echo $data_input->nom; ?></label>
                                        <input hidden type="<?php echo $data_input->input_type; ?>" name="<?php echo $data_input->id_input; ?>" id="<?php echo $data_input->id_input ?>">
                                        <input type="hidden" id="hidden_<?php echo $data_input->id_input ?>" value="<?php echo $data_input->input_valeur; ?>">
                                    
                                        <button type="button" name="<?php echo $data_input->id_input; ?>" id="<?php echo $data_input->id_input;?>-btn" class="btn btn-danger" onclick="toggle(this)">Non</button>
                                    </div>                                    
                                    <?php
                                    break;

                                case 'range':
                                    if (isset($data_input->range_value)) {
                                        $array_range_value  = $data_input->range_value;
                                        $range_values       = explode('-', $array_range_value); 
                                    ?>
                                        <div class ="form-group">
                                            <label for="<?php echo $data_input->id_input ?>"><?php echo $data_input->nom; ?>:</label>
                                            <output class="ml-3" name="output_range" id="output_range_id" value=""></output>
                                            <input type="text" name="price_one" id="price_one" value="<?php echo $data_input->price_for_one ?>" hidden>
                                            <input name="<?php echo $data_input->id_input; ?>" id="<?php echo $data_input->id_input ?>" type="<?php echo $data_input->input_type ?>" class="<?php echo $data_input->css_class ?>" valeur="<?php echo $range_values[0] ?>" step="<?php echo $range_values[3] ?>" min="<?php echo $range_values[1] ?>" max="<?php echo $range_values[2] ?>"  oninput="output_range_id.value = <?php echo $data_input->id_input ?>.value" onchange="rangeValue(this)">
                                        </div>
                                    <?php
                                    }
                                    break;

                                case 'number':
                                    ?>
                                    <label for="<?php echo $data_input->id_input ?>"><?php echo $data_input->nom; ?></label>
                                    <input type="<?php echo $data_input->input_type; ?>" name="<?php echo $data_input->id_input; ?>" id="<?php echo $data_input->id_input ?>">
                                    <?php
                                    break;

                                case 'select':
                                    ?>
                                    <div class ="form-group">
                                        <label for="<?php echo $data_input->id_input ?>"><?php echo $data_input->nom; ?></label>
                                        <select name="<?php echo $data_input->id_input ?>" id="<?php echo $data_input->id_input ?>" class="<?php echo $data_input->css_class; ?>" onchange="select(this)">
                                        <option value="">--Please choose an option--</option>
                                        <?php
                                            if (isset($data_input->option_input) && isset($data_input->option_value) ) {

                                                // On récupére les valeurs de la base de donnée. 
                                                $array_option = $data_input->option_input;
                                                $array_option_value = $data_input->option_value;
                                                
                                                // On construit des tableaux contenant les valeurs, ces valeurs sont séparés par des "-" donc on utilise la fonction explode pour créer les tableaux et enregistrer chaque valeur sur des index différents.
                                                $option_value_datas = explode('-', $array_option_value);
                                                $options_datas = explode("-", $array_option);

                                                $index = 0;
                                                // On parcours les options du select pour afficher pour chaque options, son nom et la valeur correspondante qui se trouve dans l'autre tableau. 
                                                foreach ($options_datas as $option) {                                                    
                                                    ?>
                                                    <option value="<?php echo $option_value_datas[$index] ?>"><?php echo $option; ?></option>
                                                    <?php
                                                    $index += 1;
                                                } // EO foreach
                                            } // EO if
                                        ?>
                                        </select>
                                    </div>
                                    <?php
                                    break;

                                case 'radio':
                                    ?>
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <label for="" class="form-check-label"><?php echo $data_input->nom; ?></label>
                                        </div>
                                        <div id="radio_container" class="d-flex">
                                            <div class="col-6">
                                                <input class="form-check-input" type="<?php echo $data_input->input_type; ?>" name="<?php echo $data_input->id_input; ?>">Yes</input>
                                            </div>
                                            <div class="col-6">
                                                <input class="form-check-input" type="<?php echo $data_input->input_type; ?>" name="<?php echo $data_input->id_input; ?>">No</input>
                                            </div>
                                        </div>
                                    </div>
                                    <?php

                            } // EO switch
                            ?>
                            <?php
                        } // EO foreach
                    } // EO if
                    ?>
                    <div class="container">
                        <div class="btn btn-primary d-flex">
                            <div class="col-4">
                                <p class="mt-auto mb-auto border-right">Total:</p>
                            </div>
                            <div class="col-8">
                            
                                <p id="total_area" class="mt-auto mb-auto"> 0€ </p>
                            
                            </div>
                        </div>
                    </div>
                </form>
                <div class="container">
                    <div class="d-flex flex-column" >
                        <button type="button" class="btn btn-custom-send" data-toggle="collapse" data-target="#div_collapse-send" aria-expanded="false" aria-controls="div_collapse-send">Obtenir un récapitulatif</button>
                        <div class="collapse mt-3" id="div_collapse-send">
                            <form action="" method="POST">
                                <div class="row">
                                    <div class="col"><label for="mail-client">Saisissez votre E-mail (requis):</label></div>
                                    <div class="col"><input type="email" class="form-text" name="mail-client" id="mail-client" required></div>
                                </div>
                                <div class="row">
                                    <div class="col"><label for="tel-client">Saisissez votre numéro de téléphone (optionnel):</label></div>
                                    <div class="col"><input type="tel" name="tel-client" id="tel-client" pattern="[0-9]{2}[0-9]{2}[0-9]{2}[0-9]{2}[0-9]{2}"></div>
                                </div>
                                <div class="row">
                                    <input type="text" name="mail-host" id="mail-host" hidden value="">
                                </div>
                                <button type="submit" class="btn btn-info">Envoyer le récapitulatif</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php

            echo $args['after_widget'];
        } // EO if
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