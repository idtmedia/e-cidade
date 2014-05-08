var tempo_verifica_sessao_segundos = 30;                        // Tempo para verificar a sessao em segundos
var tempo_parado                   = 0;                         // Tempo parado na tela
var url_verifica_sessao            = '/auth/sessao/verificar';  // Url para verificacao da sessao PHP

$(function() {
  setInterval('verificaSessao()', 3600); // 5 seg
  $(this).mousemove(function(e){ tempo_parado = 0; });
  $(this).keypress(function(e) { tempo_parado = 0; });
});

/** 
 * Verifica sessao
 */
function verificaSessao() {
  tempo_parado++;
  
  if (tempo_parado >= tempo_verifica_sessao_segundos) {
    
    $.get(url_verifica_sessao, function(sessao) {
      
      if (sessao && sessao.status == true) {
        tempo_parado = 0;
      } else if (sessao && sessao.status == false) {
        
        bootbox.alert(sessao.message, function() { location.href = sessao.url; });
        tempo_parado = -9999;
      }
    });
  }
}