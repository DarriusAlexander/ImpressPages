/**
 * @package ImpressPages
 *
 *
 */

(function($) {

    var methods = {
    init : function(options) {

        return this.each(function() {

            var $this = $(this);

            var data = $this.data('ipBlock');

            // If the plugin hasn't been initialized yet
            if (!data) {
                $this.delegate('.ipActionWidgetMove', 'click', function(e){e.preventDefault();});
                $this.sortable( {
                    connectWith : '.ipBlock',
                    revert : true,
                    dropOnEmpty : true,
                    forcePlaceholderSize : false,
                    placeholder: 'ipAdminWidgetPlaceholder',
                    handle : '.ipAdminWidgetControls .ipActionWidgetMove',
                    start : function (event, ui) {
                        ui.item.addClass('ipAdminWidgetDrag');
                        ui.item.width(ui.item.find('.ipAdminWidgetMoveIcon').outerWidth());
                        ui.item.height(ui.item.find('.ipAdminWidgetMoveIcon').outerHeight());
                    },
                    stop : function (event, ui) {
                        ui.item.removeClass('ipAdminWidgetDrag');
                        ui.item.width('auto');
                        ui.item.height('auto');
                    },
                    
                    // this event is fired twice by both blocks, when element is moved from one block to another.
                    update : function(event, ui) {
                        if (!$(ui.item).data('widgetinstanceid')) {
                            return;
                        }
    
                        // item is dragged out of the block. This action will be handled by the reciever using "receive"
                        if ($(ui.item).parent().data('ipBlock').name != $this.data('ipBlock').name) {
                            return;
                        }
    
                        var instanceId = $(ui.item).data('widgetinstanceid');
                        var position = $(ui.item).index();
    
                        var data = Object();
                        data.aa = 'Content.moveWidget';
                        data.securityToken = ip.securityToken;
                        data.instanceId = instanceId;
                        data.position = position;
                        data.blockName = $this.data('ipBlock').name;
                        data.revisionId = $this.data('ipBlock').revisionId;

                        $.ajax( {
                            type : 'POST',
                            url : ip.baseUrl,
                            data : data,
                            context : $this,
                            success : methods._moveWidgetResponse,
                            dataType : 'json'
                        });
    
                    },
    
                    receive : function(event, ui) {
                        $element = $(ui.item);
                        // if received element is AdminWidgetButton (insert new widget)
                        if ($element && $element.is('.ipActionWidgetButton')) {
                            $duplicatedDragItem = $('.ipBlock .ipActionWidgetButton');
                            $position = $duplicatedDragItem.index();
                            var newWidgetName = $element.data('ipAdminWidgetButton').name;
                            $duplicatedDragItem.remove();
                            $block = $this;
                            $block.ipBlock('_createWidget', newWidgetName, $position);
                        }
    
                    }
                });
                
                $this.data('ipBlock', {
                    name : $this.attr('id').substr(8),
                    revisionId : $this.data('revisionid'),
                    widgetControlsHtml : options.widgetControlsHtml
                });

                var widgetOptions = new Object;
                widgetOptions.widgetControlls = $this.data('ipBlock').widgetControlsHtml;
                $this.children('.ipWidget').ipWidget(widgetOptions);

                $this.delegate('.ipWidget .ipActionWidgetDelete', 'click', function(event) {
                    // ignore events which bubble up from nested blocks
                    if ( $(event.target).closest('.ipBlock')[0] != $this[0] )
                        return;
                    event.preventDefault();
                    $(this).trigger('deleteClick.ipBlock');
                });

                $this.delegate('.ipWidget', 'deleteClick.ipBlock', function(event) {
                    // ignore events which bubble up from nested blocks
                    if ( $(event.target).closest('.ipBlock')[0] != $this[0] )
                        return;
                    // trigger deleteWidget event for the widget in question,
                    // as well as any subwidgets it may host
                    // TODO: sending n requests for n widgets may not be the
                    //       most elegant thing to do, however the backend does
                    //       not know a thing about nesting (to fix this, the 
                    //       backend must be extended so it can delete more than
                    //       one widget in a single request). 
                    var $instance = $(this),
                        instanceData = $instance.data('widgetdata'),
                        instanceId = $instance.data('widgetinstanceid'),
                        $subwidgets = $instance.find('.ipWidget');

                    $subwidgets.each(function () {
                        $(this).trigger('deleteWidget.ipBlock', {
                            'instanceId': $(this).data('widgetinstanceid')
                        });
                    });
                    
                    $instance.trigger('deleteWidget.ipBlock', {
                        'instanceId': instanceId
                    });
                });

                $this.bind('deleteWidget.ipBlock', function(event, data) {
                    // ignore events which bubble up from nested blocks
                    if ( $(event.target).closest('.ipBlock')[0] != $this[0] )
                        return;
                    $(this).ipBlock('deleteWidget', data.instanceId);
                });

                $this.bind('reinitRequired.ipWidget', function(event) {
                    // ignore events which bubble up from nested blocks
                    if ( $(event.target).closest('.ipBlock')[0] != $this[0] )
                        return;
                    $(this).ipBlock('reinit');
                });

            }
        });
    },

    reinit : function() {
        return this.each(function() {
            var $this = $(this);
            var widgetOptions = new Object;
            widgetOptions.widgetControlls = $this.data('ipBlock').widgetControlsHtml;
            $(this).children('.ipWidget').ipWidget(widgetOptions);

        });
    },

    _moveWidgetResponse : function(response) {
        var $this = $(this);
        if (response.status == 'success') {
            $('#ipWidget-' + response.oldInstance).replaceWith(response.widgetHtml);
            $this.trigger('reinitRequired.ipWidget');
        }
        // todo show error on error response
    },

    pageSaveStart : function() {
        return this.each(function() {
            var $this = $(this);
            $(this).children('.ipWidget').ipWidget('fetchManaged').ipWidget('save');
        });
    },

    destroy : function() {
        // TODO
    },

    _showError : function(errorMessage) {
        alert(errorMessage);

    },

    deleteWidget : function(instanceId) {
        return this.each(function() {

            var $this = $(this);

            var data = Object();
            data.aa = 'Content.deleteWidget';
            data.securityToken = ip.securityToken;
            data.instanceId = instanceId;


            $.ajax( {
            type : 'POST',
            url : ip.baseUrl,
            data : data,
            context : $this,
            success : methods._deleteWidgetResponse,
            dataType : 'json'
            });
        });
    },

    _deleteWidgetResponse : function(response) {
        var $this = $(this);
        $this.find('#ipWidget-' + response.widgetId).remove();
        if ($this.children('.ipWidget').length == 0) {
            $this.addClass('ipbEmpty');
        }
    },

    _createWidget : function(widgetName, position) {

        return this.each(function() {

            var $this = $(this);

            var data = Object();
            data.aa = 'Content.createWidget';
            data.securityToken = ip.securityToken;
            data.widgetName = widgetName;
            data.position = position;
            data.blockName = $this.data('ipBlock').name;
            data.zoneName = $this.data('ipBlock').zoneName;
            data.pageId = $this.data('ipBlock').pageId;
            data.revisionId = $this.data('ipBlock').revisionId;

            $.ajax( {
            type : 'POST',
            url : ip.languageUrl,
            data : data,
            context : $this,
            success : methods._createWidgetResponse,
            dataType : 'json'
            });

        });

    },

    _createWidgetResponse : function(response) {
        var $this = $(this);
        if (response.status == 'error') {
            $.fn.ipBlock('_showError', response.errorMessage);
        }

        if (response.status == 'success') {
            var $newWidget = $(response.widgetHtml);
            if (response.position == 0) {
                $(this).prepend($newWidget);
            } else {
                $secondChild = $(this).children('.ipWidget:nth-child(' + response.position + ')');
                $($newWidget).insertAfter($secondChild);
            }
            $this.trigger('reinitRequired.ipWidget');
            $this.trigger('addWidget.ipWidget',{
                'instanceId': response.instanceId,
                'widget': $newWidget
            });
            var widgetController = $newWidget.ipWidget('widgetController');
            if (widgetController && typeof(widgetController['onAdd']) === 'function') {
                widgetController.onAdd();
            }
            // $this.ipBlock('reinit');

        }
        if ($this.hasClass('ipbEmpty')) {
            $this.removeClass('ipbEmpty');
        }
    }

    };
    


    $.fn.ipBlock = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.ipAdminWidgetButton');
        }

    };

})(jQuery);