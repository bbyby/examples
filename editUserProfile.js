(function($, undefined){
    $.fn.createWidget('EditUserProfile', {
        options: {
            template: '#editUser',
            linkClassName: '.edit-user-profile',
        },

        init: function(){
            this.initEvents();
        },

        initEvents: function () {
           this.element.on('click','.edit-user-profile a', function (e) {
                $.ajax({
                    type: 'POST',
                    url: '/index.php?do=getuserprofile',
                    data: '',
                    success: function (data) {
                        var html =  $($.templates(this.options.template).render({
                                userinfo: data
                        }));

                        $(document.body).append(html);
                        $(html).modal({
                            'onShow': function (modal) {
                                $.fn.AutoWidget.activate();

                                modal.$element.data('bs.modal').$element.on("hidden.bs.modal", function () {
                                    modal.$element.data('bs.modal').$element.remove();
                                }.proxy(this));
                            }.proxy(this)
                        });

                    }.proxy(this),

                    error: function () {
                    }
                });

            }.proxy(this));
        },


    });
})(jQuery);