//Created by BRUNO MELO <bruno.melo@idor.org>
(function ($) {
    //CLASS
    $.dlgs = {
        lastPosScroll: 0,
        count: 0,
        stackDlgs: {},
        _refresh: 0,
        $dialog: null,
        _openDialog: function () {
            $.dlgs.lastPosScroll = $(window).scrollTop();
            $.dlgs.atualizar = false;
        },
        _closeDialog: function () {
            $("body").css("overflow", "auto");
            $.dlgs.atualizar = true;
            //window.location.href = "#";
            $(window).scrollTop($.dlgs.lastPosScroll);
        },
        criarDialog: function (id) {
            var $dialog = $("div#" + id);
            if ($dialog.length === 0) {
                $("body").append("<div id='" + id + "' class='dialogIM'></div>");
                $dialog = $("div#" + id);
                $dialog.dialog({
                    autoOpen: false,
                    width: '100%',
                    draggable: false,
                    position: {my: 'bottom', at: 'bottom'},
                    resizable: false,
                    modal: false,
                    close: this._closeDialog,
                    open: this._openDialog
                });
            }
            return $dialog;
        },
        getDialogPosition: function (attrs) {
            if (attrs.position === undefined) {
                return attrs;
            }
            switch (attrs.position) {
                case 'center':
                    attrs.position = {my: "center", at: "center", of: window};
                    break;
                case 'bottom':
                    attrs.position = {my: 'bottom', at: 'bottom'};
                    break;
                default:
                    attrs.position = eval('(' + attrs.position + ')');
            }
            return attrs;
        },
        ajustaHeight: function (height) {
            if (height === undefined || height === 'auto') {
                return height;
            }
            var inPercent = (height.indexOf('%') > -1);
            height = parseFloat(height);
            if (inPercent) {
                height = $(window).height() * (height / 100);
            }
            return height;
        },
        confDialog: function (dialog, obj) {
            var defs = {//Valores defaults
                title: obj[0].title || '',
                height: $(window).height() - $('section#cabecalho').height(),
                maxHeight: $(window).height(),
                modal: false
            };
            //Hack para tentar sempre mostrar o título
            if (defs.title.length === 0) {
                defs.title = $('div.ui-helper-hidden-accessible div:last').text() || '';
            }
            //Montagem do dialog
            var attrs = $.tools.array2dict($(obj)[0].attributes, '^dlg-', true); //Resgata atributos para dialog
            attrs = this.getDialogPosition(attrs);
            attrs.height = this.ajustaHeight(attrs.height); //Quando em percentual, é preciso calcular

            dialog.dialog($.extend(defs, attrs));
        },
        openDialog: function ($dialog, obj) {
            if ($dialog.length === 0) { //Quando não existir, gera um novo dialog
                $dialog = $.dlgs.criarDialog($(obj).data('dlg'));
            }
            $.dlgs.confDialog($dialog, $(obj)); //Ajuste o dialog
            $dialog.dialog('option', 'close', this._closeDialog);
            $.get($(obj).attr('href'), '', function (data) {
                $dialog.html(data);
                $dialog.dialog('open');
            });
        },
        execLink: function (obj) {
            var idDlg = $(obj).data('dlg');
            var $dialog = idDlg ? $(idDlg) : this.$dialog;
            bloquearTudo();
            $.dlgs.openDialog($dialog, $(obj));
        },
        prepareDialogLinks: function () {
            var dlgSelec = '.dialog[href]:not(.disabled)';
            this.$dialog = this.criarDialog('dialog');
            $(document).on('click', dlgSelec, function () {
                $.dlgs.execLink(this);
                return false;
            });
        },
        getDlgId: function (obj) {
            return $(obj).find('.ui-widget-content').attr('id');
        },
        //Abre um dialog especial que irá apresentar página em um iframe
        dialogIFrame: function (obj, params, loading) {
            loading = loading || function () {
                $(this).show();
                $(this).siblings('.pre').hide();
            };
            var url = $(obj).attr('href');
            var attrsIframe = $.extend({src: url}, params);
            var $loading = $('<i class="fa fa-5x fa fa-cog fa-spin animated">');
            var $pre = $('<div class="pre">').append($loading);
            var $iframe = $('<iframe frameborder="0">').hide().attr(attrsIframe).load(loading);
            var idDialog = $(obj).data('dlg') || 'iframeDlg';
            var $dialog = this.criarDialog(idDialog);
            this.confDialog($dialog, obj);
            //Para evitar que uma dialog encerrado continue carregando (desistência)
            $dialog.dialog('option', 'close', function () {
                $iframe.attr('src', 'about:blank');
                $.dlgs._closeDialog();
            });
            $dialog.html($pre).append($iframe);
            $dialog.dialog('open');
        },
        //Varre a pilha de dialogs em busca do valor mais alto
        dialogOnTop: function () {
            var max = -1;
            var maxKey = '';
            //Busca o dialog aberto que está no topo (último a ser aberto)
            for (var key in this.stackDlgs) {
                if (this.stackDlgs[key] > max) {
                    max = this.stackDlgs[key];
                    maxKey = key;
                }
            }
            return $('#' + maxKey);
        },
        openDlgIFrame: function (btn, opts) {
            $.dlgs.dialogIFrame($(btn), opts);
        },
        resize: function( $dialog, height ){
            $dialog.dialog("option", "height", this.ajustaHeight(height) );
        }
    };

    //Funções diversas para este sistema
    $(function () {
        //Trata botões diversos
        var dialogViewerOpts = {'dlg-position': "center", 'dlg-width': "100%", 'dlg-height': "100%", 'dlg-modal': "1", 'data-dlg': "dlgDicomViewer"};
        var dialogIFrameOpts = {'dlg-position': "center", 'dlg-width': "100%", 'dlg-height': "100%", 'dlg-modal': "1", 'data-dlg': "dlgDicomIFrame"};
        $(document)
                .on('click', '.dialogViewer', function () {
                    $(this).attr(dialogViewerOpts);
                    $.dlgs.openDlgIFrame(this, {class: 'viewer'});
                    return false;
                })
                .on('click', '.dialogIFrame', function () {
                    $(this).attr(dialogIFrameOpts);
                    $.dlgs.openDlgIFrame(this);
                    return false;
                })
                .on('click', 'button', function () {
                    $(this).find('a').trigger('click');
                });

        //Ajustes nos dialogs
        $(document)
                .on('dialogcreate', '.ui-dialog', function () {
                    var id = $.dlgs.getDlgId(this);
                    $.dlgs.stackDlgs[id] = false;
                    $(this).find('.ui-dialog-titlebar-close').remove();
                })
                .on('dialogfocus', '.ui-dialog, .ui-widget', function (event) {
                    var zLast = $(event.currentTarget).css('z-index');
                    $('.ui-widget-overlay').css('z-index', zLast - 1);
                    $(this).find('.ui-widget-content').dialog('moveToTop');
                })
                .on('dialogopen', '.ui-dialog', function () {
                    var id = $.dlgs.getDlgId(this);
                    $.dlgs.stackDlgs[id] = ++$.dlgs.count;
                })
                .on('dialogclose', '.ui-dialog', function () {
                    var id = $.dlgs.getDlgId(this);
                    $.dlgs.stackDlgs[id] = false;
                    $.dlgs.dialogOnTop().dialog('moveToTop');
                });

        //Inicializa dialogs            
        $.dlgs.prepareDialogLinks();
    });

})(jQuery);