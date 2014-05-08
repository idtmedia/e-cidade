$(function() {
  $('.select-estados').live('change', function() {
    var estado          = $(this).val();
    var id_select_munic = $(this).attr('select-munic');
    var myurl           = $(this).attr('ajax-url');
    var $select_munic   = $('#' + id_select_munic);
    var haskey          = $select_munic.attr('key');
    
    $select_munic.html('');
    
    if (estado != '0') {
      $.ajax({
        url      : myurl,
        data     : { 'uf' : estado },
        async    : false,
        success  : function(data) {
          if (data) {
            $.each(data, function(key, val) {
              if (val != null)
                $select_munic.append('<option value="' +(haskey ? key : val)+ '">' +val+ '</option>');
            });
          }
        }
      });
    }
  });
  
  $('.select-paises').live('change', function() {
    var pais             = $(this).val();
    var id_select_estado = $(this).attr('select-estado');
    var myurl            = $(this).attr('ajax-url');
    var $select_estado   = $('#' + id_select_estado);
    var haskey           = $select_estado.attr('key');
    
    $select_estado.html('');
    
    if (pais != '0') {
      $.ajax({
        url    : myurl,
        data  : { 'cod' : pais },
        async  : false,
        success  : function(data) {
          if (data) {
            $.each(data, function(key, val) {
              if (val != null)
                $select_estado.append('<option "value="' +(haskey ? key : val)+ '">' +val+ '</option>');
            });
          }
        }
      });
    }
  });
});