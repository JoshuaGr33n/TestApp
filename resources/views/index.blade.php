@extends('layout')
@section('title', 'Home')
@section('content')





<div class="main" id="app">



    <div class="todoListContainer">
        <div class="heading">
            <h2 id="title">My Task</h2>

        </div>

    </div>

    <div class="form-wrapper">
        <form class="form-horizontal" autocomplete="off" role="form" method="POST" action="{{ route('task.store') }}" @submit.prevent="onSubmit">
            @csrf
            <div class="form-group" :class="['form-group', allerros.product_name ? 'has-error' : '']">
                <label for="product_name" class="col-lg-4 col-sm-4 control-label"></label>
                <div class="col-lg-5">
                    <input type="text" class="form-control" name="product_name" id="product_name" v-model="form.product_name" placeholder="Product Name*">
                    <span v-if="allerros.product_name" :class="['label label-danger']">@{{ allerros.product_name[0] }}</span>
                </div>
            </div>
            <div class="form-group" :class="['form-group', allerros.quantity ? 'has-error' : '']">
                <label for="quantity" class="col-lg-4 col-sm-4 control-label"></label>
                <div class="col-lg-5">
                    <input type="text" class="form-control" name="quantity" id="quantity" v-model="form.quantity" placeholder="Quantity in Stock*">
                    <span v-if="allerros.quantity" :class="['label label-danger']">@{{ allerros.quantity[0] }}</span>
                </div>
            </div>
            <div class="form-group" :class="['form-group', allerros.price ? 'has-error' : '']">
                <label for="price" class="col-lg-4 col-sm-4 control-label"></label>
                <div class="col-lg-5">
                    <input type="text" class="form-control" name="price" id="price" v-model="form.price" placeholder="Price*">
                    <span v-if="allerros.price" :class="['label label-danger']">@{{ allerros.price[0] }}</span>

                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-offset-4 col-lg-5">
                    <button type="submit" class="btn btn-primary form-control">Submit</button>
                </div>
            </div>
        </form>
    </div>
    <div class="table">
        <table id="datatable" class="table table-striped table-bordered nowrap" style="width:70%;margin:auto">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Quantity in Stock</th>
                    <th>Price</th>
                    <th>Date Submitted</th>
                    <th>Total value Number</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

                @foreach($data as $key => $d)

                <tr>

                    <td>{{ $loop->iteration }}</td>
                    <td>{{ucwords($d->product_name)}}</td>
                    <td><span class="">{{$d->quantity}}</span></td>
                    <td>{{$d->price}}</td>
                    <td id="title">
                        {{ $d->created_at->diffForHumans()}}
                    </td>
                    <td>{{$d->quantity * $d->price}}</td>

                    <td id="title">
                        <button class="btn btn-xs btn-primary" data-toggle="modal" data-target="#updateProductModal{{$d->id}}">
                            <i class="fa fa-pen" aria-hidden="true"></i>
                        </button>
                        <button class="btn btn-danger btn-xs delete{{$d->id}}" @click="deleteProduct({{$d->id}})">
                            <span class="fas fa-trash-alt mr-2" style='cursor:pointer;'></span>
                        </button>
                    </td>

                </tr>
                @endforeach

                <tr>

                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>{{$total_value_numbers_sum}}</td>
                    <td> </td>

                </tr>

            </tbody>
        </table>

    </div>








    @foreach($data as $key => $d)
    <div class="modal fade" id="updateProductModal{{$d->id}}" tabindex="-1" role="dialog" aria-labelledby="updateProductModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateTaskModalLabel">Edit Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span v-if="updateSuccess" :class="['label label-success']">Product Updated!</span>
                    <!-- <form method="POST" action="{{url('update_task')}}" @submit.prevent="updateTask"> -->
                    {{ csrf_field() }}
                    <div class="form-group" :class="['form-group', allerros.edit_product_name ? 'has-error' : '']">
                        <label for="edit_product_name" class="col-form-label">Product Name:</label>
                        <input type="text" class="form-control" name="edit_product_name" id="edit_product_name{{$d->id}}" value="{{$d->product_name}}">
                        <span v-if="uniqueID === {{$d->id}}">
                            <span v-if="allerros.edit_product_name" :class="['label label-danger']">@{{ allerros.edit_product_name[0] }}</span>
                        </span>
                    </div>
                    <div class="form-group" :class="['form-group', allerros.edit_quantity ? 'has-error' : '']">
                        <label for="edit_quantity" class="col-form-label">Quantity:</label>
                        <input type="text" class="form-control" name="edit_quantity" id="edit_quantity{{$d->id}}" value="{{$d->quantity}}">
                        <span v-if="uniqueID === {{$d->id}}">
                            <span v-if="allerros.edit_quantity" :class="['label label-danger']">@{{ allerros.edit_quantity[0] }}</span>
                        </span>
                    </div>
                    <div class="form-group" :class="['form-group', allerros.edit_price ? 'has-error' : '']">
                        <label for="edit_price" class="col-form-label">Price:</label>
                        <input type="text" class="form-control" name="edit_price" id="edit_price{{$d->id}}" value="{{$d->price}}">
                        <span v-if="uniqueID === {{$d->id}}">
                            <span v-if="allerros.edit_price" :class="['label label-danger']">@{{ allerros.edit_price[0] }}</span>
                        </span>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" @click="updateProduct({{$d->id}})">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                    <!-- </form>     -->
                </div>

            </div>
        </div>
    </div>
    @endforeach


</div>

<script>
    $(document).ready(function() {
        var table = $('#datatable').DataTable({
            responsive: true
        });

        new $.fn.dataTable.FixedHeader(table);
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.16/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.min.js"></script>
<script type="text/javascript">
    //    const submitResume = document.getElementById("submitResume").value;
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
                    setTimeout(function() {
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
                    setTimeout(function() {
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
                            action: function() {
                                axios.delete('{{url("delete")}}/' + id)
                                    .then(response => {
                                        if (response.data.res == 1) {
                                            $('.delete' + id).closest('tr').css('background', 'red');
                                            $('.delete' + id).closest('tr').fadeOut(800, function() {
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
                        cancel: function() {

                        }
                    }
                });
            }
        }
    });
</script>



@endsection