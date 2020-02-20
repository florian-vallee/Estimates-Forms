<?php
include_once plugin_dir_path( __FILE__ ).'/ef_widget.php';

class Ef_form
{
    /**
     * Cette variable nous servira à instancier un objet d'une autre class dans cette class.
     * @var null
     */
    public $widget = null;

    /**
     * Constructeur de class Ef_form.
     */
    public function __construct()
    {
        // On dit à l'objet de la class Ef_form d'appliquer la méthode load_ef_widget pour permettre d'inclure une instance de la class Ef_widget comme propriété de cette class.
        $this->load_ef_widget();
        
        // Fonction qui execute la fonction 'register_widget' lors de l'initialisation du widget"
        add_action( 'widgets_init', array( $this, 'register_widget' ) );

        // send_email est appelé a chaque chargement de page. NE MARCHE PAS. 
        add_action('wp_loaded', array($this, 'send_email'));
        
    } // EO __construct


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// ------  CLASS INIT  ------ ///////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Load the widget file, then instantiate the widget class, and assign it a property of this class.
     * @return bool
     */
    public function load_ef_widget() {

        require_once( plugin_dir_path( __FILE__ ) . 'ef_widget.php' );
        $this->widget = new Ef_widget();

        return true;
    } // EO load_ef_widget

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// ------  EO CLASS INIT  ------ ////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// ------  WIDGET  ------ ///////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Tell WordPress about your Widget.
     * @return bool
     */
    public function register_widget() {

        if ( !$this->widget ) {
            return false;
        }
        register_widget( 'Ef_widget' );
        
        return true;
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// ------  EO WIDGET  ------ ////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Fonction de récupération d'info et d'envoie de mail. NE MARCHE PAS.
 */
public function send_email()
{
    $client_mail;
    $object = 'test';
    $message = 'test';
    $sender = 'owner.website@gmail.com';
    $header = array('From: ' . $sender);
    $client_tel;


    if (isset($_POST['mail-client'])) {
        $client_mail = $_POST['mail-client'];
        if (isset($_POST['tel-client'])) {
            $client_tel = $_POST['tel-client'];
            $message .= $client_tel;
        }
        wp_mail($client_mail, $object, $message, $header);
    }
    
}

} // EO Ef_form


?>
