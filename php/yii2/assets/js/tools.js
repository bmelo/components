//Created by BRUNO MELO <bruno.melo@idor.org>
(function ($) {
  $.tools = {
    formAjaxSubmit: function (form, success, dataType) {
      dataType = dataType || 'text';
      bloquearTudo();
      var url = $(form).attr('action');
      var data = $(form).serialize();
      success = success || function () {
        $.msgs.success('Salvo');
      };
      return $.post(url, data, success, dataType);
    },
    getAsync: function (url, data, json) {
      json = json || true;
      var out;
      $.ajax({
        async: false,
        type: 'GET',
        dataType: json ? 'json' : 'text',
        url: url,
        data: data,
        success: function (result) {
          out = result;
        }
      });
      return out;
    },
    openDialog: function (box, obj) {
      var href = $(obj).attr('href');
      var title = $(obj).attr('diag-title') || $(obj).attr('title');
      var height = $(obj).attr('diag-height') || $(obj).attr('height') || 450;
      $(box).dialog({
        title: title,
        height: height
      });
      $(box).dialog('open');
      $.get(href, [], function (data) {
        if ($(box).children('.loading').length > 0) {
          $(box).html(data);
        }
      });
      return false;
    },
    openDialog_: function (dialog, obj) {
      $.get($(obj).attr('href'), '', function (data) {
        dialog.html(data);
        $("body").css("overflow", "hidden");
        dialog.dialog({
          title: $(obj).attr('title'),
          height: $(window).height() - $('section#cabecalho').height()
        });
        dialog.dialog('open');
      });
    },
    confirmLink: function (obj, msg, url) {
      $(obj).click(function () {
        if (confirm(msg)) {
          window.location.href = url;
        }
        return false;
      });
    },
    requestValue: function (obj, msg, url, value, callback) {
      value = value || '';
      callback = callback || function (result) {
        if(result!==null)
          window.location.href = url + '/' + result;
      };
      bootbox.prompt({
        title: msg,
        value: value,
        callback: callback
      });
    },
    retiraAcentos: function (texto) {
      var stringAcentos = new String('àâêôûãõáéíóúçüÀÂÊÔÛÃÕÁÉÍÓÚÇÜ');
      var stringSemAcento = new String('aaeouaoaeioucuAAEOUAOAEIOUCU');

      var i = new Number();
      var j = new Number();
      var cString = new String();
      var varRes = '';

      for (i = 0; i < texto.length; i++) {
        cString = texto.substring(i, i + 1);
        for (j = 0; j < stringAcentos.length; j++) {
          if (stringAcentos.substring(j, j + 1) === cString) {
            cString = stringSemAcento.substring(j, j + 1);
          }
        }
        varRes += cString;
      }
      return varRes;
    },
    criarDialog: function (id) {
      dialog = $("div#" + id);
      if (dialog.length === 0) {
        $("body").append("<div id='" + id + "'><div class=\"loading\"></div></div>");
        dialog = $("div#" + id);
        dialog.dialog({
          autoOpen: false,
          draggable: false,
          resizable: false,
          width: '45em',
          height: '450',
          modal: true,
          open: function () {
            $.tools.lastPosScroll = $(window).scrollTop();
          },
          close: function () {
            $(dialog).html('<div class=\"loading\"></div>');
            $(window).scrollTop($.cna.lastPosScroll);
          }
        });
      }
      return dialog;
    },
    array2dict: function (ar, filter, removeMatch) {
      filter = filter || '.*';
      removeMatch = removeMatch || false;
      var regexp = new RegExp(filter, "i");
      var out = {};
      $.each(ar, function () {
        var key = this.name;
        if (key.match(regexp)) {
          if (removeMatch)
            key = key.replace(regexp, '');
          out[key] = this.value;
        }
      });
      return out;
    },
    updateGrid: function (id, dataExtra) {
      var dataVal = $.tools.queryUrl(dataExtra);
      $.fn.yiiGridView.update(id, {data: dataVal});
    },
    queryUrl: function (extra) {
      extra = extra || {};
      var query = {};
      var qstr = window.location.search.replace(/^\?/, '');
      var a = qstr.split('&');
      for (var i in a) {
        var b = a[i].split('=');
        if (b.length === 2) {
          query[decodeURIComponent(b[0])] = decodeURIComponent(b[1]);
        }
      }
      return $.extend(query, extra);
    }
  };
})(jQuery);