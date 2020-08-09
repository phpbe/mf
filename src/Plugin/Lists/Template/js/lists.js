var vueLists = new Vue({
    el: '#app',
    data: function() {
        return {
            be_saving: false,
            be_form: this.$form.createForm(this),
            be_modal: {
                title: "",
                visible: false
            },
            be_drawer: {
                title: "",
                visible: false,
                placement: "right",
                closable: true
            }
        };
    },
    methods: {
        handleSubmit: function (e) {
            e.preventDefault();

            var _this = this;
            this.be_form.validateFields(function(err, values){
                if (!err) {
                    _this.be_saving = true;
                    _this.$http.post(g_sPluginListsUrl, values)
                        .then(function (response) {
                            _this.be_saving = false;
                            if (response.status == 200) {
                                if (response.data.success) {
                                    _this.$message.success(response.data.message);
                                } else {
                                    _this.$message.error(response.data.message);
                                }
                            }
                        })
                        .catch(function (error) {
                            _this.be_saving = false;
                            _this.$message.error(error);
                        });
                }
            });
        },

        toolbarAction: function (o) {
            var sTarget = '';
            if (typeof oMenu.target != 'undefined') {
                sTarget = oMenu.target;
            }

            if (sTarget == 'ajax') {
                var _this = this;
                this.$http.post(g_sPluginListsUrl, this.formData)
                    .then(function (response) {
                        if (response.status == 200) {
                            if (response.data.success) {
                                _this.$message.success(response.data.message);
                            } else {
                                _this.$message.error(response.data.message);
                            }
                        }
                    })
                    .catch(function (error) {
                        _this.$message.error(error);
                    });
            } else if (sTarget == 'modal') {


            } else if (sTarget == 'drawer') {


            } else {

                var eForm = document.createElement("form");
                eForm.action = oMenu.url;
                eForm.target = "_blank";
                eForm.method = "post";
                eForm.style.display = "none";

                for (var x in this.formData) {
                    var e = document.createElement("textarea");
                    e.name = x;
                    if (this.formData[x] instanceof Array) {
                        e.value = this.formData[x].join(",");
                    } else {
                        e.value = this.formData[x];
                    }
                    eForm.appendChild(e);
                }

                document.body.appendChild(eForm);
                eForm.submit();

                setTimeout(function () {
                    $(eForm).remove();
                }, 3000);

                return false;
            }
        }
    },
    
    

    mounted: function () {
    }

});

function closeModal() {
    vueLists.be_modal.visible = false;
}

function closeDrawer() {
    vueLists.be_drawer.visible = false;
}

function close() {
    closeModal();
    closeDrawer();
}


//console.log(vueLists);