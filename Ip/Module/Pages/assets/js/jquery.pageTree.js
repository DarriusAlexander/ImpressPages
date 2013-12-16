/**
 * @package ImpressPages
 *
 *
 */

(function($) {
    "use strict";

    var methods = {
        init : function(options) {
            return this.each(function() {
                var $this = $(this);

                var data = $this.data('ipPageTree');

                // If the plugin hasn't been initialized yet
                if ( ! data ) {
                    $this.data('ipPageTree', {
                        zoneName: options.zoneName,
                        languageId: options.languageId
                    });

                    $.proxy(refresh, $this)(options.zoneName, options.languageId);


                }

            });
        },
        destroy : function() {
            // TODO
        }



    };

    var refresh = function (zoneName, languageId) {
        var $this = this;
        var data = {
            zoneName : zoneName,
            languageId : languageId,
            aa : 'Pages.getPages'
        };

        $.ajax({
            type : 'GET',
            url : ip.baseUrl,
            data : data,
            context : $this,
            success : refreshResponse,
            dataType : 'json'
        });
    };

    var refreshResponse = function (response) {
        var $this = this;
        var $properties = $('.ipsProperties');

        $.proxy(initializeTreeManagement, $this)(response.tree);

        $this.bind('select_node.jstree', function(e) {
            var tree = jQuery.jstree._reference($this.attr('id'));
            var node = tree.get_selected();
            $properties.ipPageProperties({
                pageId : node.attr('pageId'),
                zoneName : node.attr('zoneName')
            });
        });

//        $this.bind("move_node.jstree", movePage);
//        $this.bind('close_node.jstree', closeNode);
//        $this.bind('select_node.jstree', function (e, data) {
//            // expands menu item when it is selected (shows children)
//
//            $this.jstree('open_node', data.rslt.obj);
//        });
//
//        $this.bind('refresh.jstree', function (e, data) {
//            // when new page is created, this method immediately shows it by opening parent node
//            $this.jstree('open_node', data.rslt.obj);
//        });
//
//        $('#controlls').delegate('#buttonNewPage', 'click', createPageForm);
//        $('#controlls').delegate('#buttonDeletePage', 'click', deletePageConfirm);
//        $('#controlls').delegate('#buttonCopyPage', 'click', copyPage);
//        $('#controlls').delegate('#buttonPastePage', 'click', pastePage);
//
//        $('#formCreatePage').bind('submit', function () {
//            createPage();
//            return false;
//        });

    }



    /**
     * Initialize tree management
     *
     * @param id
     *            id of div where management should be initialized
     */
    function initializeTreeManagement(data) {
        var $this = this;
        var plugins = [ 'themes', 'json_data', 'types', 'ui'];
        plugins.push('dnd');
        plugins.push('crrm');
        plugins.push('contextmenu');

        $this.jstree({

            'plugins': plugins,
            'json_data': {data: data},
            'types': {
                // -2 do not need depth and children count checking
                'max_depth': -2,
                'max_children': -2,
                'types': {
                    // The default type
                    'page': {
                        'valid_children': [ 'page' ],
                        'icon': {
                            'image': ipFileUrl('Ip/Module/Pages/assets/img/file.png')
                        }
                    }
                }
            },

            'ui': {
                'select_limit': 1,
                'select_multiple_modifier': 'alt',
                'selected_parent_close': 'select_parent',
                'select_prev_on_delete': true
            },
            'cookies': {
                'save_opened': 'PagesOpen',
                'save_selected': 'PagesSelected'
            },
            'dnd': {
                'open_timeout': 1
            }
// TODO reimplement
//            'contextmenu': {
//                'show_at_node': false,
//                'select_node': true,
//                'items': jsTreeCustomMenu
//            }


        });



    }




    $.fn.ipPageTree = function(method) {

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.ipAdminWidgetButton');
        }

    };

})(jQuery);