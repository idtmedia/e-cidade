(function($) {

  /**
   * Retorna somente numeros
   */
  $.fn.returnNumbers = function(str) {
    str = str.toString();

    return str.replace(/[^\d]+/g, '');
  };

  /**
   * Retorna valores em formato da moeda brasileira
   *
   * @example
   *   $().number_format('99,999.99', 2, ',', '.'); // Retorna: '99.999,99'
   */
  $.fn.number_format = function(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
      prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
      sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
      dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
      toFixedFix = function(n, prec) {
        var k = Math.pow(10, prec);
        return '' + Math.round(n * k) / k;
      };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    var s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');

    if (s[0].length > 3) {
      s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }

    if ((s[1] || '').length < prec) {
      s[1] = s[1] || '';
      s[1] += new Array(prec - s[1].length + 1).join('0');
    }

    return s.join(dec);
  };

  /**
   * Auto complete
   */
  $.fn.autoComplete = function(myurl, myFunction) {
    $(this).autocomplete({
      select: function(event, ui) {
        myFunction(ui.item.id);
      },
      source: function(request, response) {
        var term = request.term;
        $.ajax({
          url    : myurl + '/term/' + term,
          success: function(data) {
            data = JSON.parse(data);
            response($.map(data, function(item, i) {
              return { label: item, value: item, id: i };
            }));
          }
        });
      }
    });
  };

  /**
   * Reseta o formulario
   */
  $.fn.formReset = function(options) {
    var settings = $.extend({'form': $(this) }, options);

    settings.form.each(function() {
      this.reset();
    });
  };

  /**
   * Processa o formulario do elemento via ajax, retornando erros (html) no fieldset pai do elemento
   *
   * @namespace error.exception
   * @namespace data.fields
   * @params eThis              Elemento, geralmente Submit do Form
   * @params eForm              Formulario
   * @params eMessageContainer  Elemento onde serao exibidos os erros
   * @params eMessage           Elemento dinamico com as mensagens de retorno
   * @params beforeSendCallback Funcao de retorno executada somente se o script for processado com sucesso
   * @params completeCallback   Funcao de retorno executada ao completar o processo
   * @params errorCallback      Funcao de retorno executada somente se o script for processado com erros
   * @addon  bootbox            Utilizado para estilizar "alert" e "confirm" (http://bootboxjs.com/)
   * @returns object (this)
   */
  $.fn.submitForm = function(options) {
    var $eThis = $(this);
    var settings = $.extend({
      'eThis'             : $(this),
      'eForm'             : $(this).closest('form'),
      'eMessageContainer' : '',
      'eMessage'          : $('.ajax-message'),
      'beforeSendCallback': function() {
        $eThis.attr('disabled', true);
      },
      'completeCallback'  : function() {
        $eThis.attr('disabled', false);
      },
      'errorCallback'     : function(xhr) {

        if (xhr.responseText) {

          var error = $.parseJSON($.trim(xhr.responseText));

          bootbox.alert(error.message);

          if (error.exception) {
            console.log(error.exception);
          }
        } else {
          bootbox.alert('Ocorreu um erro desconhecido!<br>readyState: ' + xhr.readyState + '<br>status: ' + xhr.status);
        }

        $eThis.attr('disabled', false);
      }
    }, options);

    // Remove mensagem com erros
    settings.eMessage.remove();

    $.ajax({
      dataType  : 'json',
      url       : settings.eForm.attr('action'),
      data      : settings.eForm.serialize(),
      success   : function(data) {

        settings.eForm.find('.control-group').removeClass('error'); // Retira a classe de erros

        if (data.status) {

          if (data.success && typeof settings.successCallback == 'function') {
            settings.successCallback.call(this, data);
          } else if (data.success && data.reload == true) {
            bootbox.alert(data.success, function() {
              location.reload();
            });
          } else if (data.success && data.url) {
            bootbox.alert(data.success, function() {
              location.href = data.url;
            });
          } else if (data.success) {
            bootbox.alert(data.success);
          } else if (data.url) {
            location.href = data.url;
          }
        } else if (data.error) {

          var errors = '';

          $.each(data.error, function(i) {
            errors = errors + data.error[i] + '\n';
          });

          if (undefined != data.fields) {

            $.each(data.fields, function(i) {
              $('#' + data.fields[i]).closest('.control-group').addClass('error');
            });
          }

          var sMessage = '<div class="alert alert-error ajax-message"> ' +
            '  <button type="button" class="close" data-dismiss="alert">×</button>' + errors +
            '</div>';

          if (settings.eMessageContainer) {
            settings.eMessageContainer.html(sMessage);
          } else {
            settings.eThis.closest('fieldset').before(sMessage);
          }
        } else {
          bootbox.alert('Ocorreu um erro desconhecido!');
        }
      },
      beforeSend: settings.beforeSendCallback,
      error     : settings.errorCallback,
      complete  : settings.completeCallback
    });

    return this;
  };

  /**
   * Carrega conteudo Html em um elemento alvo via ajax
   *
   * @param options
   * @param options.target   Elemento alvo, que recebera o html
   * @param options.url      Url de processamento do html
   * @param options.loading  Mensagem mostrando enquando aguardo o processamento
   * @param callback         Função a ser executada após o processamento
   * @returns object (this)
   */
  $.fn.loadHtml = function(options, callback) {
    var settings = $.extend({
      'target' : $(this),
      'url'    : '',
      'loading': '<div class="alert alert-info">Carregando...</div>'
    }, options);

    if (settings.target.data('loading')) {
      settings.loading = '<div class="alert alert-info">' + settings.target.data('loading') + '</div>';
    }

    $.ajax({
      url       : settings.url,
      error     : function(xhr) {
        settings.target.html('Erro ao processar!<br>readyState: ' + xhr.readyState + '<br>status: ' + xhr.status);
      },
      beforeSend: function() {
        settings.target.html(settings.loading);
      },
      success   : function(data) {
        settings.target.html(data);
      },
      complete  : function() {

        // Executa a funcao callback
        if (typeof callback == 'function') {
          callback.call(this);
        }
      }
    });

    return this;
  };

  /**
   * Calcula valores DMS
   *
   * @param options
   * @param options.url   Url para processar a requisicao ajax, deve retornar um json
   * @param options.oThis Elemento alvo, geralmente o botao submit do form
   * @param options.oForm Formulario onde os parametros serao enviados
   * @param callback      Função a ser executada após o processamento
   * @returns object (this)
   */
  $.fn.calculaValoresDms = function(options, callback) {
    var oJsonValoresCalculados = null; // Valores calculados, retornados pelo servidor
    var settings = $.extend({
      'url'  : '',
      'oThis': $(this),
      'oForm': $(this).closest('form')
    }, options);

    $.ajax({
      dataType: 'json',
      url     : settings.url,
      data    : settings.oForm.serialize(),
      async   : false,
      error   : function(xhr) {
        bootbox.alert('Ocorreu um erro ao processar!<br>readyState: ' + xhr.readyState + '<br>status: ' + xhr.status);
      },
      success : function(data) {
        if (data) {
          oJsonValoresCalculados = data;
        } else {
          bootbox.alert('Ocorreu um erro ao recuperar valores do serviço');
        }
      }
    });

    // Executa a funcao callback
    if (typeof callback == 'function') {
      callback.call(this, oJsonValoresCalculados);
    }

    return this;
  };

  /**
   * Buscador via ajax
   *
   * @param options
   * @param options.statusInput
   * @param options.success
   * @param options.preSearch
   * @param options.complete
   * @param options.not_found
   * @returns object (this)
   */
  $.fn.buscador = function(options) {
    var input_busca = $(this);
    var input_resultado = $(options.statusInput);
    var botao_busca = $(this).find('~ button#buscador');
    var success_callback = options.success;
    var pre_search = options.preSearch;
    var post_search = options.complete;
    var not_found_callback = options.not_found;
    var data = !options.data ? null : options.data;
    var url = $(this).attr('url-buscador');

    if (!url) {
      url = options.url;
    }

    function resultado_msg(msg) {

      if (input_resultado.is('input')) {
        input_resultado.val('').attr('placeholder', msg);
      } else if (input_resultado.is('span')) {
        input_resultado.text(msg);
      } else {
        input_resultado.html(msg);
      }
    }

    input_busca.keydown(function(e) {
      if (e.which == 13) {

        e.preventDefault();
        botao_busca.click();
      }
    });

    botao_busca.click(function(e) {
      e.preventDefault();

      var query = input_busca.val();

      botao_busca.find('i').removeClass('icon-search');
      botao_busca.find('i').addClass('icon-repeat');

      resultado_msg('Aguarde...');

      var data_string = '&';

      if (data) {

        $.each(data, function(k, v) {

          if (v.attr('type') === 'checkbox') {
            data_string += k + '=' + !!v.attr('checked');
          }
        });
      }

      if (pre_search) {
        pre_search();
      }

      $.ajax({
        url     : url,
        data    : 'term=' + query + data_string,
        success : function(data) {
          //data = JSON.parse(data);

          if (data != null) {

            resultado_msg('Não encontrado');
            success_callback(data);
          } else {

            resultado_msg('Não encontrado');

            if (not_found_callback) {
              not_found_callback();
            }
          }
        },
        complete: function(data, status) {
          var msg = '';

          switch (status) {
            case 'timeout'     :
              msg = 'Erro na comunicação.';
              break;
            case 'error'       :
              msg = 'Erro interno de servidor.';
              break;
            case 'parsererror' :
              msg = 'Erro ao interpretar resposta.';
              break;
          }

          if (msg != '') {
            resultado_msg(msg);
          }

          botao_busca.find('i').removeClass('icon-repeat');
          botao_busca.find('i').addClass('icon-search');

          if (post_search) {
            post_search(data, status);
          }
        }
      });
    });

    return this;
  };

  /**
   * Adiciona mascaras nos elementos
   *
   * @returns object (this)
   */
  $.fn.addMascarasCampos = function() {
    var currentTime = new Date();
    var minDate = new Date(currentTime.getFullYear(), currentTime.getMonth() + 0, +1);
    var maxDate = new Date(currentTime.getFullYear(), currentTime.getMonth() + 1, +0);

    $('.mask-cep').setMask('cep');
    $('.mask-data').setMask('date').datepicker();
    $(".mask-data-dias-mes-corrente").setMask('date').datepicker({ minDate: minDate, maxDate: maxDate });
    $('.mask-porcentagem').setMask({ 'mask': '99,99', 'type': 'reverse' });
    $('.mask-cpf').setMask({ 'mask': '999.999.999-99' });
    $('.mask-cnpj').setMask({ 'mask': '99.999.999/9999-99' });
    $('.mask-valores').setMask({
      'mask'     : '99,999.999.9',
      'type'     : 'reverse',
      'maxlength': ($(this).attr('maxlength') ? $(this).attr('maxlength') : 11)
    });
    $('.mask-numero').setMask({
      'mask'     : '9',
      'type'     : 'repeat',
      'maxlength': ($(this).attr('maxlength') ? $(this).attr('maxlength') : -1)
    });

    $('.mask-fone').keyup(function() {
      var phone, element;

      element = $(this);
      element.unsetMask();
      phone = element.val().replace(/\D/g, '');

      if (phone.length > 10) {
        element.setMask('(99) 999-999-999?9');
      } else {
        element.setMask('(99) 9999-99999?9');
      }
    }).trigger('keyup');

    $('.mask-cpf-cnpj').keyup(function() {
      var number, element;

      element = $(this);
      element.unsetMask();
      number = element.val().replace(/\D/g, '');

      if (number.length > 11) {
        element.setMask('99.999.999/9999-99?9');
      } else {
        element.setMask('999.999.999-999?9');
      }
    }).trigger('keyup');

    return this;
  };

  /**
   * Função genérica para buscar os dados por ajax e carregar em um elemento alvo
   *
   * Pode ser setado os atributos diretamente no elemento com o prefixo "ajax"
   *
   * @param options.url         Link para requisicao ajax
   * @param options.target      Elemento alvo que recebera as informacoes retornadas
   * @param options.data        Dados enviados por parametro. Formato: { 'param1' : 'valor_param1' }
   * @param options.beforeSend  Funcao para ser executada antes do processamento (beforeSend
   * @param options.done        Funcao para ser executada quando o processamento e executado com sucesso (success)
   * @param options.fail        Funcao para ser executada quando ocorrem erros no processamento (error)
   * @param options.always      Funcao para ser executada ao final do processamento (complete)
   * @returns object (this)
   */
  $.fn.ajaxSelect = function(options) {
    var $this = $(this);
    var settings = $.extend({
      'url'       : $this.attr('ajax-url'),
      'target'    : $($this.attr('ajax-target')),
      'data'      : {},
      'beforeSend': function(xhr) {
      },
      'done'      : false,
      'fail'      : false,
      'always'    : false
    }, options);

    if ($this.attr('ajax-param')) {
      settings.data[$this.attr('ajax-param')] = $this.val();
    }

    // Limpa o elemento alvo
    settings.target.html('');

    // Executa a busca de dados
    var ajax = $.ajax({
      url       : settings.url,
      data      : settings.data,
      beforeSend: function(xhr) {
        settings.beforeSend(xhr);
      }
    });

    /**
     * Executa funcoes enviadas por parametro ou executa a padrao
     */
    // Equivale a success
    if (typeof settings.done === 'function') {
      ajax.done(settings.done);
    } else {

      // Padrao se nao enviada nenhuma funcao por parametro
      ajax.done(function(data) {

        if (data) {

          $.each(data, function(indice, valor) {

            if (valor) {
              settings.target.append('<option value="' + indice + '">' + valor + '</option>');
            }
          });
        }
      });
    }

    // Equivale a "complete"
    if (typeof settings.always === 'function') {
      ajax.always(settings.always);
    }

    // Equivale a "error"
    if (typeof settings.fail === 'function') {
      ajax.fail(settings.fail);
    }

    return $this;
  };

  /**
   * Exibe uma div com CSS no padrão Twitter Bootstrap
   *
   * @param options.mensagem
   * @param options.tipo
   */
  $.fn.mostrarDivErro = function(options) {

    var $this = $(this);
    var settings = $.extend({
      'mensagem': '',
      'tipo'    : 'error'
    }, options);

    var sDivMessagem = '<div class="alert alert-' + settings.tipo + ' ajax-message"> ' +
      '  <button type="button" class="close" data-dismiss="alert">×</button>' + settings.mensagem +
      '</div>';
    $this.html(sDivMessagem);
  };

  /**
   * Adiciona um contador em todas as textareas que possuam a classe "exibir-contador-maxlength"
   *
   * @param options
   * @param options.jquery_element elemento_alvo [Exemplo: $('#id'), $('.classe'), etc..]
   * @param options.numero_caracteres
   */
  $.fn.adicionarNumeroCaracteresTextArea = function(options) {

    var settings = $.extend({
      'elemento_alvo'    : $('.exibir-contador-maxlength'),
      'numero_caracteres': '2000'
    }, options);

    // Varre todas os elementos
    settings.elemento_alvo.each(function(id, oTextArea) {

      var oParenteNode = oTextArea.parentNode;
      var oDivContador = document.createElement('div');
      oDivContador.id = 'div_contador_textarea_' + oTextArea.id;
      oDivContador.className = 'div_contador_textarea';

      if (oTextArea.getAttribute('maxlength') == null || oTextArea.getAttribute('maxlength') == '') {
        oTextArea.setAttribute('maxlength', settings.numero_caracteres);
      }

      var iLimiteCaracteres = oTextArea.getAttribute('maxlength');
      var sContadorConteudo = '<span id="div_contador_textarea_span' + oTextArea.id + '">0</span>';

      oDivContador.innerHTML = 'Caracteres: ' + sContadorConteudo + ' / ' + iLimiteCaracteres;
      oParenteNode.appendChild(oDivContador);

      $(oTextArea).keyup(function() {

        var sTexto       = $(this).val();
        var iTotalLetras = (sTexto.length + sTexto.split('\\n').length) - 1;

        $('#div_contador_textarea_span' + $(this).attr('id')).html(iTotalLetras);
      });
    });
  };
}(jQuery));