<?php
/*
Plugin Name: Estimates Form
Description: Un plugin dont la fonctionnalité se résume en une création de formulaire, ayant des champs auxquels ont peut attribuer des valeurs, pour ainsi appliquer des calculs et obtenir un résultat. Très pratique pour façonner des formulaire qui sont semblablent à une estimation, un peu comme un devis.
Version: 0.2
Author: Florian Vallée
License: GPL2
*/

/**
 * Le but de ce fichier et en particulier de cette classe est de charger les fichiers nécessaires au chargement du plugin et d'executer les fonctions principales du plugin.
 */
class Ef_core_plugin
{
    /**
     * Cette variable nous servira à instancier un objet d'une autre classe dans cette class.
     * @var $ef_form
     */
    public $ef_form = null;

    /**
     * Cette variable nous servira à instancier un objet d'une autre classe dans cette classe.
     * @var $ef_queries
     */
    public $ef_queries = null;
    
    /**
     * Constructeur de classe, avec initialisation du menu d'administration
     */
    public function __construct()
    {
        // On dit à l'objet de la class Ef_core_plugin d'appliquer la méthode load_ef_form pour permettre d'inclure une instance de la class Ef_form comme propriété de cette class.
        $this->load_ef_form();

        $this->load_ef_queries();
        
        // On ajoute la fonction qui nous permet d'ajouter une page de menu.
        add_action('admin_menu', array($this, 'ef_add_admin_menu'));
        
        // On ajoute la fonction qui nous permet d'ajouter une page de sous-menu.
        add_action('admin_menu', array($this, 'ef_add_admin_submenu'));
        
        // On ajoute la fonction qui nous permet de déclarer la zone de widget, ce qui rend possible sont affichage dans le menu d'administration WP.
        add_action('widgets_init', array($this, 'ef_widgets_init'));

        // On ajoute la fonction d'ajout du fichier admin.css.
        add_action('admin_print_styles', array($this, 'add_admin_assets'));

        // On ajoute la fonction d'ajout de fichier stylesheets. 
        add_action('wp_enqueue_scripts', array($this, 'add_plugin_assets'));

        // Pour l'installation du plugin.
        register_activation_hook(__FILE__, array($this, 'install'));

        // Pour la désactivation du plugin.
        register_deactivation_hook(__FILE__, array($this, 'uninstall'));

        // Pour la suppression du plugin.
        register_uninstall_hook(__FILE__, array($this, 'delete_plugin'));

        // Enregistrement des groupes d'options.
        add_action('admin_init', array($this, 'ef_register_settings'));

        // Ici on applique la fonction de suppression du formulaire d'option.
        add_action('admin_post_ef_delete_config', array($this, 'ef_delete_config'));

        // Ici on applique la fonction de mise à jour du formulaire d'option.
        add_action('admin_post_ef_update_config', array($this, 'ef_update_config'));

        // Ici on applique la fonction de suppression d'un input.
        add_action('admin_post_ef_delete_input', array($this, 'ef_delete_input'));

        // Ici on applique la fonction de MAJ d'un input.
        add_action('admin_post_ef_update_input', array($this, 'ef_update_input'));

        // Ici on applique la fonction widgets_admin_page pour faire en sorte de remplacer la description du widget
        add_action('widgets_admin_page', array($this, 'widgets_admin_page'));
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// ------  CLASS INIT  ------ ///////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Fonction qui nous sert à incorporer le fichier ef_form.php et instancier un objet de la class Ef_Form.
     */
    private function load_ef_form()
    {
        require_once(plugin_dir_path(__FILE__) . '/ef_form.php');
        $this->ef_form = new Ef_form();
        return true;
    }

    /**
     * Fonction qui nous sert à incorporer le fichier ef_query.php et instancier un objet de la class Ef_queries.
     */
    public function load_ef_queries()
    {
        require_once(plugin_dir_path(__FILE__) . '/ef_query.php');
        $this->ef_queries = new Ef_queries();
        return true;
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// ------  EO CLASS INIT  ------ ////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// ------  WIDGET  ------ ///////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * Fonction qui nous permet de déclarer la zone de widget sidebar.
     */
    public function ef_widgets_init()
    {
        /**
         * Ici on définis les arguments ($args) de notre widget, et la fonction enregistre les données en BDD.
         * @args name           = noms de la zone de widget
         * @args id             = id du widget, à rentrer dans le 'dynamic_sidebar('$id');', cette id est utilisé dans le menu widget de WP pour le conteneur de la sidebar.
         * @args description    = La description qui apparait dans la zone de widget du menu widget de WP.
         * @args before_widget  = Contenu HTML pour le conteneur du widget (effectif lors de l'affichage du contenu du widget). (%1$s et %2$s sont des variables, le premier correspond a l'ID du widget, le second a la class css ajouter lors de l'appelle du contructeur de la class EF_widget dans le fichier 'ef_widget.php'.)
         * @args class          = Class CSS additionnelle du conteneur de la sidebar, celle-ci est préfixé avec 'sidebar-', pour l'administration du widget (apparence->widget). 
         * @args after_widget   = Contenu HTML pour la fin du conteneur du widget (effectif lors de l'affichage du contenu du widget).
         * @args before_title   = Contenu HTML pour le conteneur du titre du widget (effectif lors de l'affichage du contenu du widget).
         * @args after_title    = Contenu HTML pour la fin du conteneur du titre du widget (effectif lors de l'affichage du contenu du widget).
         */
        register_sidebar(
            array(
                'name'          => __('Devis widget area', 'textdomain'),
                'id'            => 'main-area-1',
                'description'   => '',
                'class'         => 'ef_class_test',
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h2 class="widget-title widget-title-custom">',
                'after_title'   => '</h2>',
            )
        );
    }

    /**
     * Cette fonction nous permet lors de l'initialisation de la page de widget de faire appel à la fonction dynamic_sidebar_before()
     */
    public function widgets_admin_page() {
        add_action('dynamic_sidebar_before', array($this, 'dynamic_sidebar_before'));
    }

    /**
     * Cette fonciton nous permet de chamger le description et d'ajouter du contenu dans la zone de widget coté admin. 
     */
    public static function dynamic_sidebar_before($index) {
        if (substr($index, 0, strlen('main-area-1')) === 'main-area-1') {
            echo '<div class="description">Display this widget area in your theme with: <pre style="white-space: pre-wrap;overflow:hidden;">&lt;?php dynamic_sidebar(\'' . $index . '\'); ?&gt;</pre></div>';
        }

    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// ------  EO WIDGET  ------ ////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// ------  MENU ET SOUS-MENU  ------ ////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   

    /**
     * Cette fonction ajoute le menu dans wordpress pour gérer le plugin.
     */
    public function ef_add_admin_menu()
    {
        add_menu_page(
            'Accueil',
            'Estimates Form',
            'manage_options',
            'ef_menu',
            array($this, 'ef_menu_html')
        );
    }

    /**
     * Cette fonction ajoute le sous-menu dans wordpress pour gérer les formulaire du plugin.
     */
    public function ef_add_admin_submenu()
    {
        add_submenu_page(
            'ef_menu',
            'Editeur',
            'Editeur',
            'manage_options',
            'ef_submenu_editor',
            array($this, 'ef_submenu_html')
        );
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// ------  EO MENU ET SOUS-MENU  ------ //////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// ------  INIT SECTION-OPTION, OPTIONS, GROUP-OPTION  ------ ////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * Cette fonction enregistre les groupes d'options.
     */
    public function ef_register_settings()
    { 
        
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    //------------------------------------------- FORMULAIRE ----------------------------------------------//
    /////////////////////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Ajout d'une section incluant des champs dans le groupe d'option "ef_formulaire_settings".
         * add_settings_section( string $id, string $title, callable $callback, string $page )
         */
        add_settings_section(
            'ef_formulaire_section',
            'Création du formulaire d\'estimation de devis',
            array($this, 'form_section_html'),
            'ef_formulaire_settings'
        );


         
        /**
         * Permet d'ajouter des champs dans la section "ef_formulaire_section".
         * add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
         */
        add_settings_field(
            'ef_formulaire_name',
            'Nom du formulaire',
            array($this, 'form_name_html'),
            'ef_formulaire_settings',
            'ef_formulaire_section',
            array(
                'label_for' => 'ef_formulaire_name'
            )
        );

        /**
         * Permet d'ajouter des champs dans la section "ef_formulaire_section".
         * add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
         */
        add_settings_field(
            'ef_formulaire_id',
            'ID du formulaire',
            array($this, 'form_id_html'),
            'ef_formulaire_settings',
            'ef_formulaire_section',
            array(
                'label_for' => 'ef_formulaire_id'
                )
        );

        /**
         * Permet d'ajouter des champs dans la section "ef_formulaire_section".
         * add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
         */
        add_settings_field(
            'ef_formulaire_css',
            'Classe css additionnel',
            array($this, 'form_css_html'),
            'ef_formulaire_settings',
            'ef_formulaire_section',
            array(
                'label_for' => 'ef_formulaire_css'
                )
        );

        /**
         * Enregistrement des options dans la base de donnée à la table {wp-prefix}options.
         * register_setting( string $option_group, string $option_name, array $args = array() )
         */
        register_setting(
            'ef_formulaire_settings',
            'ef_form_option',
        );

    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    //------------------------------------------- INPUT ---------------------------------------------------//
    /////////////////////////////////////////////////////////////////////////////////////////////////////////


        /**
         * Ajout d'une section incluant des champs dans le groupe d'option "ef_formulaire_settings".
         * add_settings_section( string $id, string $title, callable $callback, string $page )
         */
        add_settings_section(
            'ef_input_section',
            'Paramètres d\'édition des inputs',
            array($this, 'input_section_html'),
            'ef_input_settings'
        );
        
        
        /**
         * Permet d'ajouter des champs dans la section "ef_input_section".
         * add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
         */        
        add_settings_field(
            'ef_input_name',
            'Nom de l\'input',
            array($this, 'input_name_html'),
            'ef_input_settings',
            'ef_input_section',
            array(
                'label_for' => 'ef_input_name'
                )
        );

        /**
         * Permet d'ajouter des champs dans la section "ef_input_section".
         * add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
         */        
        add_settings_field(
            'ef_input_valeur',
            'Valeur attribué à l\'input pour le calcul',
            array($this, 'input_valeur_html'),
            'ef_input_settings',
            'ef_input_section',
            array(
                'label_for' => 'ef_input_valeur'
                )
        );

        /**
         * Permet d'ajouter des champs dans la section "ef_input_section".
         * add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
         */
        add_settings_field(
            'ef_input_id',
            'ID de l\'input',
            array($this, 'input_id_html'),
            'ef_input_settings',
            'ef_input_section',
            array(
                'label_for' => 'ef_input_id'
                )
        );

        /**
         * Permet d'ajouter des champs dans la section "ef_input_section".
         * add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
         */
        add_settings_field(
            'ef_input_id_form',
            'ID du formulaire',
            array($this, 'input_id_form_html'),
            'ef_input_settings',
            'ef_input_section',
            array(
                'label_for' => 'ef_input_id_form'
                )
        );

        /**
         * Permet d'ajouter des champs dans la section "ef_input_section".
         * add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
         */
        add_settings_field(
            'ef_input_type',
            'Type de l\'input',
            array($this, 'input_type_html'),
            'ef_input_settings',
            'ef_input_section',
            array(
                'label_for' => 'ef_input_type'
                )
        );
        
        /**
         * Permet d'ajouter des champs dans la section "ef_input_section".
         * add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
         */
        add_settings_field(
            'ef_input_css',
            'Classe css additionnel',
            array($this, 'input_css_html'),
            'ef_input_settings',
            'ef_input_section',
            array(
                'label_for' => 'ef_input_css'
                )
        );

        /**
         * Permet d'ajouter des champs dans la section "ef_input_section".
         * add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
         */
        add_settings_field(
            'ef_input_description',
            'Rajoutez une description',
            array($this, 'input_description_html'),
            'ef_input_settings',
            'ef_input_section',
            array(
                'label_for' => 'ef_input_description'
                )
        );
        
        /**
         * Enregistrement des options dans la base de donnée à la table {wp-prefix}options.
         * register_setting( string $option_group, string $option_name, array $args = array() )
         */
        register_setting(
            'ef_input_settings',
            'ef_input_option',
        );
    }
    

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////// ------  EO INIT SECTION-OPTION, OPTIONS, GROUP-OPTION  ------ ////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////---------------------- Rendu input ADMNI --------------------------------////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Fonction du rendu de la section.
     */
    public function input_section_html()
    {
        ?>
        <div>
            <h5>Renseigner les informations nécessaire pour l'enregistrement d'un input.</h5>
        </div>
        <?php
    }
    
    /**
     * Fonction de rendu de l'input 'ef_input_id'
     */
    public function input_id_html()
    {
        ?>
        <div class="d-flex">
            <input type="text" name="ef_input_option[ef_input_id]" value=""/>
            <p class="para-admin">
                Mentionner l'ID de l'input, celui-ci <strong>doit être unique, sans majuscules et sans espaces</strong>.</br>
                Rajouter un préfix de type (nom_du_formulaire) pour en être sur.
            </p>
        </div>
        <?php
    }

    /**
     * Fonction de rendu de l'input 'ef_input_id_form'
     */
    public function input_id_form_html()
    {
        ?>
        <div class="d-flex">
            <input type="text" name="ef_input_option[ef_input_id_form]" value=""/>
            <p class="para-admin">Mentionner <strong>l'ID du formulaire</strong> dans lequel cet input apparaîtra.</p>
        </div>
        <?php
    }

    /**
     * Fonction de rendu de l'input 'ef_input_name'
     */
    public function input_name_html()
    {
        ?>
        <div class="d-flex">
            <input type="text" name="ef_input_option[ef_input_name]" value=""/>
            <p class="para-admin">Le nom de l'input s'affichera en tant que <strong>label</strong> pour l'input ainsi que pour <strong>l'attribut name</strong> de celui-ci.</p>
        </div>
        <?php
    }

    /**
     * Fonction de rendu de l'input 'ef_input_type'
     */
    public function input_type_html()
    {
        ?>
        <div class="d-flex">
            <input type="text" name="ef_input_option[ef_input_type]" value=""/>
            <p class="para-admin">
                Les types sont: <strong>select</strong>, <strong>checkbox</strong>, <strong>radio</strong>, <strong>range</strong>, <strong>number</strong>.
            </p>
        </div>
        <?php
    }

    /**
     * Fonction de rendu de l'input 'ef_input_css'
     */
    public function input_css_html()
    {
        ?>
        <div class="d-flex">
            <input type="text" name="ef_input_option[ef_input_css]" value=""/>
            <p class="para-admin">
               Vous pouvez ajouter plusieurs classe en les séparant par des espaces comme cela: "class1 class2 class3".
            </p>
        </div>
        <?php
    }

    /**
     * Fonction de rendu de l'input 'ef_input_valeur'
     */
    public function input_valeur_html()
    {
        ?>
        <div class="d-flex">
            <input type="number" name="ef_input_option[ef_input_valeur]" value=""/>
            <p class="para-admin">
               La valeur représente le coût du service, il sert pour le calcul de devis.
            </p>
        </div>
        <?php
    }

    /**
     * Fonction de rendu de l'input 'ef_input_description'
     */
    public function input_description_html()
    {
        ?>
        <div class="d-flex">
            <input type="text" name="ef_input_option[ef_input_description]" value=""/>
            <p class="para-admin">
               Cette description sert à guider l'utilisateur du formulaire d'estimation.
            </p>
        </div>
        <?php
    }


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////---------------------- EO Rendu input ADMIN --------------------------------////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////
//------------------------------- RENDU Création du formulaire d'estimation de devis ADMIN ------------//
/////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Fonction du rendu de la section.
     */
    public function form_section_html()
    {
        ?>
        <div>
            <h5>Renseigner les informations nécessaire pour construire la base du formulaire d'estimation de devis.</h5>
            <div class="d-flex flex-column">
                <p class="para-info">Pour <strong>le nom et l'ID</strong> il est conseillé de  prendre sur ce model: </p>
                <p class="para-info">Nom: "Exemple de nom"</p>
                <p class="para-info">ID: "exemple_de_nom" <strong>Sans majuscules</strong>, les espaces sont interdits <U>/!\</U></p>

            </div>
            
        </div>
        <?php
    }

    /**
     * Fonction de rendu de l'input 'ef_formulaire_name'
     */
    public function form_name_html()
    {   
        // $ef_form_option = get_option( 'ef_form_option' ); // get plugin options from the database
        ?>
        
        <input type="text" name="ef_form_option[ef_formulaire_name]" value=""/>
        
        <?php
    }

    /**
     * Fonction de rendu de l'input 'ef_formulaire_id'
     */
    public function form_id_html()
    {   
        // $ef_form_option = get_option( 'ef_form_option' ); // get plugin options from the database 

        ?>
        
        <input type="text" name="ef_form_option[ef_formulaire_id]" value=""/>
        
        <?php
        
    }

    /**
     * Fonction de rendu de l'input 'ef_formulaire_css'
     */
    public function form_css_html()
    {   
       // $ef_form_option = get_option( 'ef_form_option' ); // get plugin options from the database

        ?>
        
        <input type="text" name="ef_form_option[ef_formulaire_css]" value=""/>
        
        <?php
    }
    
/////////////////////////////////////////////////////////////////////////////////////////////////////////
//---------------------------- EO RENDU Création du formulaire d'estimation de devis ADMIN ------------//
/////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// ------  RENDER page ADMIN  ------ /////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Cette fonction affiche la page d'administration du plugin dans wordpress.
     */
    public function ef_menu_html()
    {
        echo '<h1>'.get_admin_page_title().'</h1>';
        echo '<p>Bienvenue sur la page d\'accueil du plugin</p>';
        ?>
        
        <div class="d-flex flex-column">
            <h3 style="text-decoration: underline;">Description du projet / Project description</h3>
            <div>
                <h5>Français</h5>
                <p> 
                Le projet est inscrit dans le cadre d'un stage en développement web d'une durée de 2 mois.</br>
                L'idée ? Créer un plugin pour le CMS Wordpress.</br>
                Ce plugin permettra à un utilisateur de construire, via une interface de création et de gestion situé sur le back-office de Worpdress, des formulaires.</br>
                Ces formulaires ont pour vocation à être des éstimations de devis, c'est-à-dire des formulaires contenant des inputs permettant à un utilisateur web de gérer des options et de voir l'impact sur le prix du devis.</br>
                Chaques inputs représentera un champ auquel on ajoute une valeur pour ensuite pratiquer un calcul total des champs. Ceci constituera donc une sorte d'estimation de devis.
                </p>
            </div>
            <div>
                <h5>English</h5>
                <p>
                This project is a project carried out for my 2 month web development internship.</br>
                The idea ? Create a plugin for Wordpress CMS.</br>
                This plugin will allow an user to build, through the management and creation interface located in the back office of Wordpress, some forms.</br>
                This forms are particular, the purpose is to make price estimates. Users create their owns configurate forms with some inputs, and when they displays it on a web page forms can be manage by some web users to show them price and cost of the different inputs.</br>
                Every inputs add on forms have a value that can be manage into a total value. It's a kind of a service's price estimates.
                </p>
            </div>

        </div>
        <div class="d-flex flex-column">
            <h2 style="text-decoration: underline;">Condition d'utilisation</h2>
            <div>
                <h3>Renseigner les informations nécessaire pour le formulaire.</h3>                
                <div>
                    <p> Dans cette premiere étape l'utilisateur du plugin doit rentrer 3 choses: </p>
                    <ul class="border-left border-info pl-3">
                       
                        <li>Le nom du formulaire</li>
                        <li>L'ID du formulaire</li>
                        <li>Une class CSS additionnel</li>
                    </ul>
                </div>
                <p>
                    Tout d'abord le nom du formulaire. Il est utilisé pour l'affichage. Le plugin vérifie la correspondace entre le nom fourni dans cette rubrique et le titre fourni dans le panneau de gestion du widget.
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Cette fonction affiche la page d'édition de formulaire du plugin dans wordpress.
     * Le formulaire doit appeler le fichier wp-admin/options.php lors de la soumission des données pour enregistrer les données dans la table des options de la base de données wordpress. 
     */
    public function ef_submenu_html()
    {   
        global $wpdb;        
        
        // On initialise un tableau qui contient les données des différentes options de la table wp_ef_formulaire_settings.
        $form_option = "SELECT * FROM `{$wpdb->prefix}ef_formulaire_settings`";
        $form_datas = $wpdb->get_results($form_option);

        // On initialise un tableau qui contient les données des différentes options de la table wp_ef_formulaire_settings.
        $input_option = "SELECT * FROM `{$wpdb->prefix}ef_input_settings`";
        $input_datas = $wpdb->get_results($input_option);

        // On initialise un conteur pour le nombre de configuration, un autre pour le nombre d'input. 
        $nbr_config = 0;
        $nbr_input = 0;

        echo '<h1>'.get_admin_page_title().'</h1>';
        echo '<p>Bienvenue sur la page d\'édition du plugin</p>'; 
        
        ?>
        <div class="background-custom text-custom container rounded border-custom-container mb-3">
        <!-- Ici on commence l'affichage du formulaire qui enregistre les caractéristiques du devis que l'on souhaite. -->
        <form method="post" action="options.php">
            <?php settings_fields('ef_formulaire_settings') ?>
            <?php do_settings_sections('ef_formulaire_settings') ?>
            <?php submit_button($text = null, $type = 'primary', $name = 'submit-ef_formulaire_settings'); ?>
        </form></div>
        <!-- Fin du premier formulaire pour les caractéristiques du devis. -->
        <div class="container d-flex justify-content-lg-between">
            <!-- Bouton qui ouvre le bloc qui permet de voir l'ensemble des différentes configurations de devis et qui permet la modification ou la suppression des éléments. -->
            <button class="btn btn-info mb-4" id="btn_display_config" type="button" data-toggle="collapse" data-target="#div_collapse-see_config" aria-expanded="false" aria-controls="div_collapse-see_config">
                Modifier les configurations
            </button>
            <!-- Bouton qui permet l'affichage du bloc permettant l'ajout d'un nouveau input. -->
            <button class="btn btn-info mb-4" id="btn_display_add_input" type="button" data-toggle="collapse" data-target="#div_collapse-add_input" aria-expanded="false" aria-controls="div_collapse-add_input">
                + Ajouter un input
            </button>

        </div>
        

        <!-- Ici on commence l'affichage du bloc qui contient le formulaire de création d'un nouvel input. -->
        <div class="collapse" id="div_collapse-add_input">            
            <form action="options.php" method="post">
                <?php settings_fields('ef_input_settings') ?>
                <?php do_settings_sections('ef_input_settings') ?>
                <?php submit_button($text = null, $type = 'primary', $name = 'submit-ef_input_settings'); ?>
            </form>                  
        </div>
        <!-- Fin du deucieme formulaire pour les caractéristiques de l'input. -->

        <!-- Ici on commence l'affichage du bloc qui affiche les caractéristiques des différentes configurations. -->
        <div class="collapse" id="div_collapse-see_config">
            <!-- Conteneur principale des blocs qui constituent l'affichage de la configuration courante. -->
            <div class="d-flex flex-wrap justify-content-between">
                <?php
                // On vérifie si il existe bien des configuration en BDD. 
                if (isset($form_datas)) {
                    foreach ($form_datas as $data) {
                        $nbr_config += 1; 
                        $nbr_input = 0;                     
                ?>      
                        <!-- Ici on commence l'affichage du bloc qui affiche LA configuration COURANTE, celle-ci est voué à être répété autant de fois que le nombre de configuration. -->
                        <div class="container rounded border-custom-container p-0 mb-3">
                            <!-- Ici on commence l'affichage du bloc qui contient l'ensemble des données de la configuration. -->
                            <div class="d-flex flex-column">
                                <!-- Ici on commence l'affichage du bloc qui contient le formulaire d'update des caractéristiques du formulaire ET le bloc qui contient les boutons "modifier les inputs" et "Delete confoguration". -->
                                <div class="d-flex flex-column background-custom">
                                    <!-- Ici on commence l'affichage du bloc Titre et Description. -->
                                    <div class="text-center">
                                        <span style="text-decoration-line: underline;">
                                            <h4 class="mb-3 mt-1">Configuration n° <?php echo $nbr_config; ?>: <?php echo $data->nom; ?></h4>
                                        </span>
                                        <p>Vous pouvez ici changer les informations concernant ce formulaire puis en validant en appuyant sur "Modifier le formulaire".</p>
                                    </div>
                                    <!-- Formulaire d'update des caractéristiques du formulaire. -->
                                    <form action="admin-post.php" method="POST">
                                        <!-- Bloc label/input ID Form. -->
                                        <div class="d-flex mb-2">
                                            <div class="col-4 text-left"><label for="id_form">ID du formulaire:</label></div>
                                            <div class="col-8 text-center"><input type="text" name="new_id_form" id="id_form<?php echo "-" . $nbr_config; ?>" value="<?php echo $data->id_form; ?>"></div>
                                        </div>
                                        <!-- Bloc label/input Nom Form. -->
                                        <div class="d-flex mb-2">
                                            <div class="col-4 text-left"><label for="new_name">Nom du formulaire:</label></div>
                                            <div class="col-8 text-center"><input type="text" name="new_name" id="name_form<?php echo "-" . $nbr_config; ?>" value="<?php echo $data->nom; ?>"></div>
                                        </div>
                                        <!-- Bloc label/input css additionnel Form. -->
                                        <div class="d-flex mb-2">
                                            <div class="col-4 text-left"><label for="new_css">CSS additionnel:</label></div>
                                            <div class="col-8 text-center"><input type="text" name="new_css" id="css_form<?php echo "-" .  $nbr_config; ?>" value="<?php echo $data->css_class; ?>"></div>
                                        </div>
                                        <!-- Bloc bouton submit "Modifier le formulaire". -->
                                        <div class="pl-2 mb-2">                                            
                                            <input type="text" name="update_by_form_id" id="update_by_form_id-<?php echo '-' . $nbr_config; ?>" value="<?php echo $data->id_form; ?>" hidden>
                                            <input type="hidden" id="hidden_update_field-<?php echo $nbr_config; ?>" name="action" value="ef_update_config">
                                            <input class="btn btn-success mr-2" type="submit" value="Modifier le formulaire">
                                        </div>

                                    </form>
                                    
                                    <!-- Div qui contient les boutons "modifier les inputs" et "Delete confoguration". -->
                                    <div class="d-flex flex-row mb-4 pl-2 pr-2 justify-content-between">                                        
                                        <!-- Bouton "Modifier les inputs", C'est un bouton qui permet l'affichage des inputs du formulaire en question. -->
                                        <button class="btn btn-info" id="btn_display_input_config-<?php echo $nbr_config; ?>" type="button" data-toggle="collapse" data-target="#div_collapse-input_config_<?php echo $nbr_config; ?>" aria-expanded="false" aria-controls="div_collapse-input_config_<?php echo $nbr_config; ?>">
                                            Modifier les inputs
                                        </button>
                                        <!-- Bouton "Delete configuration", C'est un formulaire qui permet la suppression du formulaire en question. -->
                                        <form action="admin-post.php" method="POST">
                                            <input type="text" name="delet_by_form_id" id="delet_by_form_id-<?php echo '-' . $nbr_config; ?>" value="<?php echo $data->id_form; ?>" hidden>
                                            <input type="hidden" id="hidden_field-<?php echo $nbr_config; ?>" name="action" value="ef_delete_config">
                                            <input class="btn btn-danger" type="submit" value="Delete configuration">
                                        </form>
                                        
                                    </div>
                                
                                </div>
                                <!-- Fin du bloc qui contient le formulaire d'update des caractéristiques du formulaire ET le bloc qui contient les boutons. -->
                                <!-- Div collapse activer/désactiver par le bouton "Modifier les inputs". Il contient les inputs. -->
                                <div class="collapse" id="div_collapse-input_config_<?php echo $nbr_config; ?>">                                
                                    <!-- Conteneur principale des inputs -->
                                    <div class="d-flex flex-column">
                                        <?php 
                                        // On vérifie s'il existe bien des inputs en BDD
                                        if (isset($input_datas)) {   
                                            $nbr_compt = 0;               
                                            foreach ($input_datas as $data_input) {
                                                // On vérifie que les id de formulaire correspond à celui de la configuration en cours
                                                if ($data_input->input_id_form === $data->id_form) {
                                                    $nbr_input += 1;                                                
                                                    $nbr_compt += 1;
                                                    //var_dump($data_input);
                                                    ?>
                                                    <!-- Bloc qui contient 1 inputs et qui se répéte en boucle tant qu'il y a des inputs appartenant à ce formulaire -->
                                                    <div class="d-flex flex-column mt-4 pt-2 pb-2 background-custom">
                                                        <!-- Bloc titre de l'input. -->
                                                        <div class="text-center mb-3">
                                                            <h4>
                                                                <span style="text-decoration-line: underline;">Input n° <?php echo $nbr_input; echo ': ' . $data_input->nom; ?></span>
                                                            </h4>
                                                        </div>
                                                        <!-- Formulaire d'update de l'input. -->
                                                        <form action="admin-post.php" method="POST">
                                                            <!-- Bloc Label/input ID du formulaire d'appartenance -->
                                                            <div class="d-flex mb-2">
                                                                <div class="col border-right border-dark"><label for="input_id_form<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>">ID du formulaire d'appartenance:</label></div>
                                                                <div class="col"><input type="text" name="input_id_form" id="input_id_form<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>" placeholder="<?php echo  $data_input->input_id_form; ?>"></div>
                                                            </div>
                                                            <!-- Bloc Label/input Nom de l'input -->
                                                            <div class="d-flex mb-2">
                                                                <div class="col border-right border-dark"><label for="name_input<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>">Nom de l'input:</label></div>
                                                                <div class="col"><input type="text" name="name_input" id="name_input<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>" value="<?php echo $data_input->nom; ?>"></div>
                                                            </div>
                                                            <!-- Bloc Label/input Type de l'input.-->
                                                            <div class="d-flex mb-2">
                                                                <div class="col border-right border-dark"><label for="type_input<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>">Type de l'input:</label></div>
                                                                <div class="col"><input type="text" name="type_input" id="type_input<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>" value="<?php echo $data_input->input_type; ?>"></div>
                                                            </div>
                                                            <!-- Bloc Label/input Description de l'input.-->
                                                            <div class="d-flex mb-2">
                                                                <div class="col border-right border-dark"><label for="description_value<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>">Description de l'input:</label></div>
                                                                <div class="col"><input type="text" name="description_value" id="description_value<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>" value="<?php echo $data_input->input_description; ?>"></div>
                                                            </div>
                                                            
                                                            <?php 
                                                            if ($data_input->input_type !== "select" || $data_input->input_type !== "range") {
                                                            ?>
                                                                <!-- Si l'input n'est pas de type "select" ou "range" on rajoute cette section, Bloc Label/input valeur pour l'input et son calcul dans le devis.-->
                                                                <div class="d-flex mb-2">
                                                                    <div class="col border-right border-dark"><label for="value_input<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>">Valeur attribué à l'input pour le calcul des champs lors de l'estimation de devis:</label></div>
                                                                    <div class="col"><input type="number" name="value_input" id="value_input<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>" value="<?php echo $data_input->input_valeur; ?>"></div>
                                                                </div>
                                                            <?php
                                                            }
                                                            ?>
                                                            <!-- Bloc Label/input css additionnel -->
                                                            <div class="d-flex mb-2">
                                                                <div class="col border-right border-dark"><label for="css_input<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>">Classe CSS supplémenatire:</label></div>
                                                                <div class="col"><input type="text" name="css_input" id="css_input<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>" value="<?php echo $data_input->css_class; ?>"></div>
                                                            </div>
                                                            <!-- Bloc Label/input ID input-->
                                                            <div class="d-flex mb-2">
                                                                <div class="col border-right border-dark"><label for="id_input<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>">ID de l'input:</label></div>
                                                                <div class="col"><input type="text" name="id_input" id="id_input<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>" value="<?php echo $data_input->id_input; ?>"></div>
                                                            </div>
                                                            <?php 
                                                            if ($data_input->input_type === "select") {
                                                                ?>
                                                                <!-- Si le type est "select", Bloc Label/input Ajout des options-->
                                                                <div class="d-flex mb-2">
                                                                    <div class="col border-right border-dark">
                                                                        <label for="option_input<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>">Ajouter des options pour le select:</label>
                                                                    </div>
                                                                    <div class="d-flex col">
                                                                        <input type="text" name="option_input" id="option_input<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>" value="<?php echo $data_input->option_input; ?>">
                                                                        <span class="ml-2">Format pour les options: "option1-option2-..."</span>
                                                                    </div>                                                                
                                                                </div>
                                                                <!-- Si le type est "select", Bloc Label/input Ajout des valeurs correspondantes aux options. -->
                                                                <div class="d-flex mb-2">
                                                                    <div class="col border-right border-dark">
                                                                        <label for="option_input_value<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>">Ajouter les valeurs correspondante aux option ci-dessus:</label>
                                                                    </div>
                                                                    <div class="col">
                                                                        <input type="text" name="option_value" id="option_input_value<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>" value="<?php echo $data_input->option_value; ?>">
                                                                    </div>
                                                                </div>
                                                                <?php
                                                            }

                                                            if ($data_input->input_type === "range") {
                                                                ?>
                                                                <!-- Si le type est "range", Bloc Label/input Ajout des options-->
                                                                <div class="d-flex mb-2">
                                                                    <div class="col border-right border-dark">
                                                                        <label for="range_input_value<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>">Ajouter des options pour le range:</label>
                                                                    </div>
                                                                    <div class="d-flex col">
                                                                        <input type="text" name="range_value" id="range_input_value<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>" value="<?php echo $data_input->range_value; ?>">
                                                                        <span class="ml-2">Adopter ce <strong>format</strong> pour rentrer vos valeurs numérique: "ValeurInitiale-ValeurMinimale-ValeurMaximale-ValeurDuPas" <strong>Ex: 10-0-150-5</strong></span>
                                                                    </div>                                                                
                                                                </div>
                                                                <!-- Si le type est "select", Bloc Label/input Ajout de la valeur qui sera multiplié par le nombre (option)-->
                                                                <div class="d-flex mb-2">
                                                                    <div class="col border-right border-dark">
                                                                        <label for="price_one_value<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>">Ajouter une valeur unitaire <strong>(une valeur pour 1)</strong> pour le calcul:</label>
                                                                    </div>
                                                                    <div class="d-flex col">
                                                                        <input type="text" name="price_one" id="price_one_value<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>" value="<?php echo $data_input->price_for_one; ?>">
                                                                    </div>                                                                
                                                                </div>
                                                                <?php
                                                            }
                                                            ?>
                                                            <!-- Bouton update -->
                                                            <div class="col">
                                                                <input type="text" name="update_by_input_id" id="update_by_input_id-<?php echo $nbr_config . "_" . $nbr_compt; ?>" value="<?php echo $data_input->id_input; ?>" hidden>
                                                                <input type="hidden" id="hidden_field_update-<?php echo '-' . $nbr_config . '-' . $nbr_compt; ?>" name="action" value="ef_update_input">
                                                                <input class="btn-success btn" type="submit" value="Mettre à jours l'input n° <?php echo $nbr_compt; ?>">
                                                            </div>
                                                        </form>
                                                        <!-- Fin du formulaire d'update de l'input. -->
                                                        <!-- Bloc qui contient le formulaire de supression de l'input. -->
                                                        <div class="col text-right">
                                                            <!-- Formulaire dissimuler par le bouton "Delete input" qui permet la suppression de l'input en BDD. -->
                                                            <form action="admin-post.php" method="POST">
                                                                <input type="text" name="delet_by_input_id" id="delet_by_input_id-<?php echo $nbr_config . "_" . $nbr_compt; ?>" value="<?php echo $data_input->id_input; ?>" hidden>
                                                                <input type="hidden" id="hidden_field_delet_input-<?php echo $nbr_config . "_" . $nbr_compt; ?>" name="action" value="ef_delete_input">
                                                                <input class="btn btn-danger" type="submit" value="Delete input">
                                                            </form>
                                                        </div>
                                                            
                                                            
                                                        
                                                    </div>
                                                    <!-- Fin du bloc qui contient 1 input. -->
                                                <?php
                                                } // IF ($data_input->input_id_form === $data->id_form)
                                            } // EO FOREACH ($input_datas as $data_input)
                                        } // EO IF (isset($input_datas))
                                        ?>                                        
                                    </div>
                                    <!-- Fin du conteneur principale des inputs -->
                                </div>
                                <!-- Fin du bloc collapse qui contient les inputs -->
                            </div> <!-- Fin Conteneur  qui contient l'ensemble des données de la configuration.-->
                        </div>
                        <!-- Fin de l'affichage du bloc qui affiche affiche LA configuration COURANTE. -->
                    <?php
                    } // EO FOREACH
                } // EO IF (isset($form_data))
                ?>
            </div> <!-- Fin Conteneur principale. -->
        </div>
        <!-- Fin de l'affichage du bloc qui affiche les caractéristiques des différentes configurations. -->



    <?php
    } // EO ef_submenu_html()

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// ------  EO RENDER INPUT SETTINGS  ------ //////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// ------  FORM CUSTOM SUBMIT  ------ ////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Fonction permettant la suppression de la configuration (données formulaire + données inputs).
     * Elle est appelé lors d'un submit.
     * <input class="btn btn-danger mr-2" type="submit" value="Delete configuration"> <---- voici l'input correspondant.
     */
    public function ef_delete_config()
    {
        global $wpdb;

        if (isset($_POST)) {
            $id_form = $_POST["delet_by_form_id"];
            $sql_ef_formulaire_settings = "DELETE FROM {$wpdb->prefix}ef_formulaire_settings WHERE `id_form` = '{$id_form}'";
            $sql_ef_input_settings = "DELETE FROM {$wpdb->prefix}ef_input_settings WHERE `input_id_form` = '{$id_form}'";
            $wpdb->query($sql_ef_formulaire_settings);
            $wpdb->query($sql_ef_input_settings);            
        }
        wp_redirect(admin_url('admin.php?page=ef_submenu_editor'));
        die();
    }

    /**
     * Fonction permettant la suppression d'un input.
     * Elle est appelé lors d'un submit.
     * <input class="btn btn-danger mr-2" type="submit" value="Delete input"> <---- voici l'input correspondant.
     */
    public function ef_delete_input()
    {
        global $wpdb;

        if (isset($_POST)) {
            $id_input = $_POST["delet_by_input_id"];
            $sql = "DELETE FROM {$wpdb->prefix}ef_input_settings WHERE `id_input` = '{$id_input}'";
            $wpdb->query($sql);            
        }
        wp_redirect(admin_url('admin.php?page=ef_submenu_editor'));
        die();
    }

    /**
     * Fonction permettant l'update des caractéristiques d'un formulaire.
     * Elle est appelé lors d'un submit.
     * <input class="btn btn-success mr-2" type="submit" value="Modifier le formulaire"> <---- voici l'input correspondant.
     */
    public function ef_update_config()
    {   
        global $wpdb;
        
        if (isset($_POST)) {

            $old_id     = $_POST['update_by_form_id'];
            $new_id     = $_POST['new_id_form'];
            $new_name   = $_POST['new_name'];
            $new_css    = $_POST['new_css'];
            $sql        = "UPDATE {$wpdb->prefix}ef_formulaire_settings SET `id_form`= '{$new_id}',`nom`= '{$new_name}',`css_class`= '{$new_css}' WHERE `id_form` = '{$old_id}'";
            $wpdb->query($sql);
        }
        wp_redirect(admin_url('admin.php?page=ef_submenu_editor'));
        die();        
    }

    /**
     * Fonction permettant l'update d'un input.
     * Elle est appelé lors d'un submit.
     * <input class="btn-success btn" type="submit" value="Mettre à jours l'input n°<?php echo $nbr_compt; ?>."> <---- voici l'input correspondant.
     */
    public function ef_update_input()
    {
        global $wpdb;
        
        if (isset($_POST)) {

            $old_id             = $_POST['update_by_input_id'];
            $new_id             = $_POST['id_input'];
            $new_name           = $_POST['name_input'];
            $new_value          = $_POST['value_input'];
            $new_type           = $_POST['type_input'];
            $new_css            = $_POST['css_input'];
            $new_option         = $_POST['option_input'];
            $new_option_value   = $_POST['option_value'];
            $new_range_value    = $_POST['range_value'];
            $new_price_for_one  = $_POST['price_one'];
            $new_description    = $_POST['description_value'];

            if (empty($new_value)) {
                 $new_value = 0;
             }
            $sql = "UPDATE `{$wpdb->prefix}ef_input_settings` SET `id_input`= '{$new_id}',`nom`= '{$new_name}',`input_type`='{$new_type}',`input_valeur`= '{$new_value}',`css_class`= '{$new_css}', `option_input`= '{$new_option}',`option_value`= '{$new_option_value}',`range_value`='{$new_range_value}',`price_for_one`= '{$new_price_for_one}',`input_description`= '{$new_description}'  WHERE `id_input`= '{$old_id}'";
            $wpdb->query($sql);
        }
        wp_redirect(admin_url('admin.php?page=ef_submenu_editor'));
        die();
         
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// ------  EO FORM CUSTOM SUBMIT  ------ //////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// ------  MANAGE STYLESHEETS AND JAVASCRIPT  ------ /////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Fonction qui permet d'ajouter le dossier admin.css et admin.js à la liste des link css et script js du plugin pour gérer le bacj office.
     *
     */
    public function add_admin_assets()
    {
        $admin_handle = 'admin_css';
        $admin_stylesheet = plugins_url('assets/css/admin.css', __FILE__);

        $admin_handle_js = 'admin_js';
        $admin_scripts = plugins_url('assets/js/scripts_admin.js', __FILE__);

        $jquery = 'jquery';
        $jquery_dir = plugins_url('assets/js/jquery-3.4.1.js', __FILE__);

        $bootstrap_js = 'bootstrap_js';
        $bootstrap_js_dir = plugins_url('assets/js/bootstrap.min.js', __FILE__);

        $bootstrap_css = 'bootstrap_css';
        $bootstrap_css_dir = plugins_url('assets/css/bootstrap.css', __FILE__);
    
        
        wp_enqueue_style($admin_handle, $admin_stylesheet);
        wp_enqueue_style($bootstrap_css, $bootstrap_css_dir);
        wp_enqueue_script($admin_handle_js, $admin_scripts);
        wp_enqueue_script($jquery, $jquery_dir);
        wp_enqueue_script($bootstrap_js, $bootstrap_js_dir);

    }

    /**
     * Fonction qui permet d'ajouter le dossier plugin.css et plugin.js à la liste des link css et script js du plugin pour gérer le front end du plugin sur le site.
     */
    public function add_plugin_assets()
    {
        $dir_css = plugins_url('assets/css/plugin.css', __FILE__);
        $dir_bootstrap_css = plugins_url('assets/css/bootstrap.css', __FILE__);
        $dir_js = plugins_url('assets/js/plugin.js', __FILE__);
        $dir_jquery_js = plugins_url('assets/js/jquery-3.4.1.js', __FILE__);
        $dir_bootstrap_js = plugins_url('assets/js/bootstrap.min.js', __FILE__);
        wp_enqueue_style('plugin_css', $dir_css);
        wp_enqueue_style('plugin_bootstrap_css', $dir_bootstrap_css);
        wp_enqueue_script('plugin_js', $dir_js);
        wp_enqueue_script('plugin_jquery.js', $dir_jquery_js);
        wp_enqueue_script('plugin_bootstrap_js', $dir_bootstrap_js);
        
        
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// ------  EO STYLESHEETS AND JAVASCRIPT  ------ /////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// ------  INITIALISATION DES TABLES DU PLUGIN  ------ //////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Fonction qui crée lors de l'installation du plugin les différentes tables nécessaires dans la BDD de wordpress
     * @query table ef_formulaire_settings option du formulaire
     * @query table ef_input_settings options des inputs, en relation avec l'id du formulaire (id_form)
     * {$wpdb->prefix} préfixe choisi lors de l'installation wordpress, ici dans mon cas 'wp-'
     */
    public function install()
    {
        global $wpdb;

        $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ef_formulaire_settings (id_form VARCHAR(255) PRIMARY KEY, nom VARCHAR(255) NOT NULL, css_class VARCHAR(255));");
        $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ef_input_settings (id_input VARCHAR(255) PRIMARY KEY, nom VARCHAR(255) NOT NULL, input_type VARCHAR(150), input_valeur INT NOT NULL, css_class VARCHAR(255), input_id_form VARCHAR(255) NOT NULL, FOREIGN KEY (input_id_form) REFERENCES {$wpdb->prefix}ef_formulaire_settings(id_form), option_input VARCHAR(300), option_value VARCHAR (300), range_value VARCHAR (300), price_for_one VARCHAR (12), input_description VARCHAR (300) );");
    
    }

    /**
     * Fonction qui supprime lors de la DESACTIVATION du plugin les différentes tables nécessaires dans la BDD de wordpress
     * @query table ef_formulaire_settings option du formulaire
     * @query table ef_input_settings options des inputs, en relation avec l'id du formulaire (id_form)
     * {$wpdb->prefix} préfixe choisi lors de l'installation wordpress, ici dans mon cas 'wp-'
     */
    public static function uninstall()
    {
        global $wpdb;

        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ef_formulaire_settings;");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ef_input_settings;");
    }

    /**
     * Fonction qui supprime lors de la SUPPRESSION du plugin les différentes tables nécessaires dans la BDD de wordpress
     * @query table ef_formulaire_settings option du formulaire
     * @query table ef_input_settings options des inputs, en relation avec l'id du formulaire (id_form)
     * {$wpdb->prefix} préfixe choisi lors de l'installation wordpress, ici dans mon cas 'wp-'
     */
    public static function delete_plugin()
    {
        global $wpdb;

        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ef_formulaire_settings;");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ef_input_settings;");
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// ------  EO INITIALISATION DES TABLE DU PLUGIN  ------ ////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

} // EO class Ef_core_plugin

// On instancie un objet de la class Ef_core_plugin pour lancer les fonctionnalités du plugin. 
$Ef_core_plugin = new Ef_core_plugin();

?>