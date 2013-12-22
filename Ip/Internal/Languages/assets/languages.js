$(function() {
    "use strict";

    $('.ipsGrid').on('init.grid', ipLanguages.init);


});

var ipLanguages = new function() {

    this.init = function(e) {
        $('.ipsCustomAdd').off('click').on('click', showAddModal);
    };

    var showAddModal = function() {
        var $modal = $('.ipsAddModal');
        $modal.modal();
        $modal.find('.ipsAdd').off('click').on('click', function(){$modal.find('form').submit()});
        $modal.find('form').off('submit').on('submit', function(e) {
            e.preventDefault();
            var code = $modal.find('input[name=languageCode]').val();
            addLanguage(code);
            $modal.modal('hide');
        });

    };


    var addLanguage = function(code) {
        var data = {
            aa: 'Languages.addLanguage',
            code: code,
            securityToken: ip.securityToken
        }
        $.ajax({
            type: 'POST',
            url: ip.baseUrl,
            data: data,
            context: this,
            success: function (response) {
                refresh();
            },
            error: function(response) {
                if (ip.developmentEnvironment || ip.debugMode) {
                    alert('Server response: ' + response.responseText);
                }
            },
            dataType: 'json'
        });
    }

    var refresh = function(){
        $('.ipsGrid').ipGrid('refresh');
    }

};

