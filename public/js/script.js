
$(document).ready(function () {
    var table = $('#datatable').DataTable({
        responsive: true
    });

    new $.fn.dataTable.FixedHeader(table);
});




// ########################################################################

const app = new Vue({
    el: '#app',

    data: {
        form: {
            product_name: '',
            quantity: '',
            price: '',

        },
        allerros: [],
        err: [],
        success: false,
        updateSuccess: false,
        uniqueID: 0,
    },

    methods: {
        onSubmit() {
            dataform = new FormData();
            dataform.append('product_name', this.form.product_name);
            dataform.append('quantity', this.form.quantity);
            dataform.append('price', this.form.price);

            console.log(this.form.title);

            axios.post("{{ route('task.store') }}", dataform).then(response => {
                console.log(response);
                this.allerros = [];
                this.form.product_name = '';
                this.form.quantity = '';
                this.form.price = '';
                this.success = true;
                if (response.data.success === 1) {
                    this.success = true;
                }
                setTimeout(function () {
                    location.reload();
                }, 2000);
            }).catch((error) => {
                this.allerros = error.response.data.errors;
                this.success = false;
            });
        },
        updateProduct(id) {
            console.log(id);
            const edit_product_name = document.getElementById('edit_product_name' + id).value;
            const edit_quantity = document.getElementById('edit_quantity' + id).value;
            const edit_price = document.getElementById('edit_price' + id).value;


            dataform = new FormData();
            dataform.append('edit_product_name', edit_product_name);
            dataform.append('edit_quantity', edit_quantity);
            dataform.append('edit_price', edit_price);
            dataform.append('Uid', id);
            console.log(edit_product_name);

            axios.post("{{url('update_product')}}", dataform).then(response => {
                console.log(response);
                this.allerros = [];
                this.updateSuccess = true;
                if (response.data.success === 1) {
                    this.updateSuccess = true;
                }
                setTimeout(function () {
                    location.reload();
                }, 2000);
            }).catch((error) => {
                this.uniqueID = id;
                this.allerros = error.response.data.errors;
                console.log(this.allerros);
                this.updateSuccess = false;
            });
        },

        deleteProduct(id) {
            console.log(id);
            $.confirm({
                title: 'Delete',
                content: 'Warning! Are you sure you want to remove this product?',
                buttons: {
                    Yes: {
                        text: 'Yes',
                        btnClass: 'btn-danger',
                        action: function () {
                            axios.delete('{{url("delete")}}/' + id)
                                .then(response => {
                                    if (response.data.res == 1) {
                                        $('.delete' + id).closest('tr').css('background', 'red');
                                        $('.delete' + id).closest('tr').fadeOut(800, function () {
                                            $(this).remove();
                                        });
                                    } else {
                                        alert('Invalid Selection.');
                                    }
                                })
                                .catch(error => {
                                    console.log(error);
                                })
                            setInterval('location.reload()', 1000);

                        }
                    },
                    cancel: function () {

                    }
                }
            });
        }
    }
});
