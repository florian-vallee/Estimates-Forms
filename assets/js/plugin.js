  // -----------------------------     INITIALISATION DES VARIABLES GLOBALES ------------------------------

  // On initialise un prix en variable globale pour pouvoir la traiter avec toute les fonctions. 
  var price             = 0;
  // Ancienne valeur de l'input select. 
  var old_select_value  = 0;
  // Ancienne valeur de l'input range. 
  var old_range_value   = 0;
  // Ancienne valeur de l'input number.
  var old_value  = 0;
  // ------------------------------------------------------------------------------------------------------

  // --------------------------------------     FONCTIONS DE CALCUL ---------------------------------------

  // For checkbox calcul
  function toggle(element) {
    var checkbox_id = element.name;
    var checkbox = document.getElementById(checkbox_id);
    var hidden_box_name = "hidden_" + element.name;
    var hidden_box = document.getElementById(hidden_box_name);
    var total = document.getElementById("total_area");

    // To change the "checked" value to true or false and the text on the button. 
    if (element.className === 'btn btn-danger' || element.className === 'btn btn-danger far fa-frown-open') {
      element.className = 'btn btn-primary far fa-smile-wink';
      element.textContent = ' Oui';
      checkbox.setAttribute('checked', 'true');
    }
    else {
      element.className = 'btn btn-danger far fa-frown-open';
      element.textContent = ' Non';
      checkbox.removeAttribute('checked', 'false');
    }

    // On ajoute au prix la valeur correspondante si la checkbox et true et on soustrait l'ancienne valeur si la checkbox est false. 
    if (checkbox.getAttribute('checked') == 'true') {
      price = price + Number(hidden_box.value);
    }
    else{
      price = price - Number(hidden_box.value);
    }
    total.innerHTML = price + "€";
  }

  // For select calcul
  function select(elmnt) {
    var select_value = elmnt.value;
    var total = document.getElementById("total_area");

    if (select_value != '' || select_value != 0) {
      // On ajoute au prix la valeur correspondante, et on soustrait l'ancienne valeur. 
      price = price + Number(select_value) - old_select_value;
    }
    else {
      price = price + 0 - old_select_value;
    }
    
    // On insère le resultat dans la zone approprié. 
    total.innerHTML = price + "€";
    
    // On assigne la valeur en tant qu'ancienne valeur à la fin du traitement. 
    old_select_value = select_value;
  }

  // For range calcul
  function rangeValue(item) {
    var value = Number(item.value);
    var input_price_one = document.getElementById('price_one');
    var price_one = Number(input_price_one.value);
    var total = document.getElementById("total_area");
    var result = value*price_one;

    // On ajoute au prix la valeur correspondante, et on soustrait l'ancienne valeur. 
    price = price + result - old_range_value;
    
    // On insère le resultat dans la zone approprié. 
    total.innerHTML = price + "€";

    // On assigne la valeur en tant qu'ancienne valeur à la fin du traitement. 
    old_range_value = result;
  }

  // For number calcul
  function numberValue(elmnt) {
    var value         = Number(elmnt.value);
    var input_value   = elmnt.getAttribute('valeur');
    var number_id     = elmnt.id;
    console.log(number_id);
    var result        = value*input_value;
    var total         = document.getElementById("total_area");
    var prix = 0;
    // On ajoute au prix la valeur correspondante, et on soustrait l'ancienne valeur. 
    prix = result + old_value;
    // On insère le resultat dans la zone approprié. 
    total.innerHTML = prix + "€";

    // On assigne la valeur en tant qu'ancienne valeur à la fin du traitement. 
    old_value = result;
  }
  // ------------------------------------------------------------------------------------------------------
