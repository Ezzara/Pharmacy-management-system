@extends('admin.layouts.app')


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
				<div class="container">
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
														<th>Product</th>
														<th>Price</th>
														<th>Stock</th>
													</tr>
												</thead>
												<tbody>
													@foreach ($categories as $category)
														@if (!empty($category->name))
															<tr data-id="{{$category->id}}" data-price="{{$category->price}}" data-stock="{{$category->quantity}}">
																<td>{{$category->name}}</td>
																<td>{{$category->price}}</td>
																<td>{{$category->quantity}}</td>
															</tr>
														@endif
													@endforeach
												</tbody>
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
</div>
@endsection	

@push('page-js')
    <script>
		var products = [];
		var removedOptions = {};

		document.querySelectorAll('#productTable tbody tr').forEach(function(row) {
			row.addEventListener('click', function() {
				var product = this.getAttribute('data-id');
				var productName = this.cells[0].textContent;
				var price = parseFloat(this.getAttribute('data-price'));
				var stock = parseFloat(this.getAttribute('data-stock'));

				var quantity = prompt('Please enter the quantity:');
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

				var productDetails = {product: product, quantity: quantity, price: price};
				products.push(productDetails);

				var li = document.createElement('li');
				li.textContent = productName + ' x ' + quantity + ' = ' + (quantity * price).toFixed(2);
				li.className = 'list-group-item';

				removedOptions[product] = this;
				this.style.display = 'none';

				var removeButton = document.createElement('button');
				removeButton.textContent = 'Remove';
				removeButton.className = 'btn btn-danger btn-sm float-right';
				removeButton.onclick = function() {
					var index = products.indexOf(productDetails);
					if (index !== -1) {
						products.splice(index, 1);
					}
					li.parentNode.removeChild(li);
					calculateTotal();

					var row = removedOptions[productDetails.product];
					row.style.display = '';
				};

				li.appendChild(removeButton);
				document.getElementById('productList').appendChild(li);
				calculateTotal();
			});
		});

		function calculateTotal() {
			var total = 0;
			for (var i = 0; i < products.length; i++) {
				total += products[i].quantity * products[i].price;
			}
			document.getElementById('total').textContent = total.toFixed(2);
		}

		function confirmOrder() {
			var productsJSON = JSON.stringify(products);
			var input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'products';
			input.value = productsJSON;
			var form = document.getElementById('productForm');
			form.appendChild(input);

			let printReceipt = confirm("Apakah anda ingin mencetak struk?");
			document.getElementById('print_receipt').value = printReceipt ? 'yes' : 'no';
			document.getElementById('productForm').submit();
		}
    </script>
@endpush