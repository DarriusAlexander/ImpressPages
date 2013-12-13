/**
 * @package ImpressPages
 *
 *
 */


(function ($) {
    "use strict";

    var methods = {
        init: function (options) {

            return this.each(function () {
                var $this = $(this);
                var data = $this.data('ipGrid1Init');
                // If the plugin hasn't been initialized yet
                if (!data) {
                    $this.data('ipGrid1Init', Object());

                    //window.location.hash

                    var data = $this.data('gateway');
                    data.jsonrpc = '2.0';
                    data.method = 'init';
                    data.params = {
                        hash: window.location.hash
                    };

                    $.ajax({
                        type: 'GET',
                        url: ip.baseUrl,
                        data: data,
                        context: $this,
                        success: initResponse,
                        error: function (response) {
                            if (ip.debugMode || ip.developmentMode) {
                                alert(response);
                            }
                        },
                        dataType: 'json'
                    });

                }
            });
        }
    };


    var initResponse = function (response) {
        $.proxy(doCommands, this)(response.result);
    };

    var doCommands = function (commands) {
        var $this = this;
        $.each(commands, function (key, value) {
            switch (value.command) {
                case 'setHtml':
                    $this.html(value.html);
                    $.proxy(bindEvents, $this)();
                    break;
            }
        });
    };

    var bindEvents = function () {
        var $grid = this;

        $grid.find('.ipsAction[data-method]').off().click(function() {
            var $this = $(this);
            var data = $grid.data('gateway');
            data.jsonrpc = '2.0';
            data.method = $this.data('method');

            var params = $this.data('params');
            if (params !== null) {
                data.params = params;
            }

            $.ajax({
                type: 'GET',
                url: ip.baseUrl,
                data: data,
                context: $grid,
                success: initResponse,
                error: function (response) {
                    if (ip.debugMode || ip.developmentMode) {
                        alert(response);
                    }
                },
                dataType: 'json'
            });
        });
    };

    $.fn.ipGrid1 = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.ipWidget');
        }

    };

})(jQuery);