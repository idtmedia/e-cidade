$(document).ready(function(){
  
  $("form[name=busca] input#im").buscador({
    statusInput: "span#status",
    url: "/contribuinte/parametro/busca-contribuinte/",
    success: function(data) {
      
      $("span#status").text("");
      
      $("input[name=im]").val(data.dados.inscricao);
      $("input#nome_contribuinte").val(data.dados.nome);

      $.each(data.parametros,function(i,v) {
        var elmnt = $("#" + i);
        if(elmnt.attr('type') == 'checkbox') {
          elmnt.attr('checked',v);
        }else {
          elmnt.val(v);
        }
      });
    }
  });
});