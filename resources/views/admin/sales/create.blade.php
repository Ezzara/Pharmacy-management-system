@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Kasir</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Kasir</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
					<h2 class="text-center mt-4">Cashier Panel</h2>
					<div class="row form-row">
						<div class="col-md-6">
							<div class="card">
								<div class="card-body">
									<form id="productForm" action="{{ route('sales.store') }}" method="POST">
										@csrf
										<div class="form-group">
											<table class="table table-bordered" id="productTable">
												<thead>
													<tr>
														<th>Nama Obat</th>
														<th>Harga</th>
														<th>Stock</th>
                                                        <th>Tgl expired</th>
													</tr>
												</thead>
											</table>
										</div>
										<input type="hidden" name="print_receipt" id="print_receipt" value="no">
									</form>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Added Products</h5>
									<ul id="productList" class="list-group"></ul>
									<p>Total: <span id="total">0.00</span></p>
									<button type="button" class="btn btn-primary" onclick="confirmOrder()"> Confirm Order </button>
								</div>
							</div>
						</div>
					</div>
			</div>
		</div>
	</div>			
</div>
@endsection	

@push('page-js')
<script>
    $(document).ready(function() {
        $('#productTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('sales.list') }}',
            columns: [
                { data: 'name', name: 'name' },
                { data: 'price', name: 'price' },
                { data: 'quantity', name: 'quantity' },
                { data: 'expiry_date',name:'expiry_date'},
            ],
            pageLength: 100,
            scrollY: '500px',
            scrollCollapse: true
        });

        var products = [];
        var removedOptions = {};

        $('#productTable tbody').on('click', 'tr', function() {
            var row = $('#productTable').DataTable().row(this).data();
            var productId = row.id;
            var productName = row.name;
            var price = parseFloat(row.price);
            var stock = parseFloat(row.quantity);

            var quantity = prompt('Please enter the quantity for ' + productName + ':');
            if (quantity === null) {
                return; // User cancelled the prompt
            }
            quantity = parseFloat(quantity);

            if (isNaN(quantity) || quantity <= 0) {
                alert('Quantity must be a positive number');
                return;
            }
            if (quantity > stock) {
                alert('Stock obat kurang');
                return;
            }

            var productDetails = {product: productId, quantity: quantity, price: price};
            products.push(productDetails);

            var li = $('<li>').text(productName + ' x ' + quantity + ' = ' + (quantity * price).toFixed(2)).addClass('list-group-item');

            removedOptions[productId] = row;
            $('#productTable').DataTable().row(this).remove().draw();

            var removeButton = $('<button>').text('Remove').addClass('btn btn-danger btn-sm float-right').click(function() {
                var index = products.indexOf(productDetails);
                if (index !== -1) {
                    products.splice(index, 1);
                }
                li.remove();
                calculateTotal();

                $('#productTable').DataTable().row.add(removedOptions[productDetails.product]).draw();
            });

            li.append(removeButton);
            $('#productList').append(li);
            calculateTotal();
        });

        function calculateTotal() {
            var total = 0;
            for (var i = 0; i < products.length; i++) {
                total += products[i].quantity * products[i].price;
            }
            $('#total').text(total.toFixed(2));
        }

        window.confirmOrder = function() {
            var productsJSON = JSON.stringify(products);
            var input = $('<input>').attr('type', 'hidden').attr('name', 'products').val(productsJSON);
            $('#productForm').append(input);

            let printReceipt = confirm("Apakah anda ingin mencetak struk?");
            $('#print_receipt').val(printReceipt ? 'yes' : 'no');
            $('#productForm').submit();
        }
    });
</script>
@endpush