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
											<label>Product <span class="text-danger">*</span></label>
											<select class="select2 form-select form-control" name="product" id="product"> 
												@foreach ($categories as $category)
													@if (!empty($category->name))
														@if (!($category->quantity <= 0))

															<option value="{{$category->id}}" data-price="{{$category->price}}" data-stock="{{$category->quantity}}">{{$category->name}}</option>
														@endif
													@endif
												@endforeach
											</select>
										</div>
										<div class="form-group">
											<label for="quantity">Quantity:</label>
											<input type="number" class="form-control" id="quantity">
										</div>
										<button type="button" class="btn btn-primary" onclick="addProduct()">Add Product</button>
										<button type="button" class="btn btn-primary" onclick="confirmOrder()"> Confirm Order </button>
									</form>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Added Products</h5>
									<ul id="productList" class="list-group">
									</ul>
									<h5 class="card-title mt-4">Total: <span id="total"></span></h5>
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

		function addProduct() {
			var productSelect = document.getElementById('product');
			var product = productSelect.value;
			var productName = productSelect.options[productSelect.selectedIndex].text;
			var quantity = parseFloat(document.getElementById('quantity').value);
			var quantityInput = document.getElementById('quantity');
			var price = parseFloat(productSelect.options[productSelect.selectedIndex].getAttribute('data-price'));
			var stock = parseFloat(productSelect.options[productSelect.selectedIndex].getAttribute('data-stock'));
			var productDetails = {product: product, quantity: quantity, price: price};

			products.push(productDetails);

			if (quantityInput.value === '') {
				alert('Please enter a quantity');
				return;
   			}
			if (quantity <= 0) {
				alert('Quantity must be greater than 0');
        	return;
    		}
			if (quantity > stock ) {
				alert('Stock obat kurang')
				return;
			}

			var li = document.createElement('li');
			li.textContent = productName + ' x ' + quantity + ' = ' + (quantity * price).toFixed(2);
			li.className = 'list-group-item';
			//contain the removed product and then remove it from the input
			removedOptions[product] = productSelect.options[productSelect.selectedIndex];
			productSelect.remove(productSelect.selectedIndex);
			document.getElementById('quantity').value = "";

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

				var productSelect = document.getElementById('product');
				var option = removedOptions[productDetails.product];
				productSelect.add(option);
			};

			li.appendChild(removeButton);
			document.getElementById('productList').appendChild(li);
			calculateTotal();
		}

		function calculateTotal() {
			var total = 0;
			for (var i = 0; i < products.length; i++) {
				total += products[i].quantity * products[i].price;
			}
			document.getElementById('total').textContent = total.toFixed(2);
		}

		function confirmOrder() {
			// get the products array as a JSON string
			var productsJSON = JSON.stringify(products);
			// create a hidden input element to store the products data
			var input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'products';
			input.value = productsJSON;
			// append the input element to the form
			var form = document.getElementById('productForm');
			form.appendChild(input);
			// ask the user for confirmation
			var answer = confirm('Are you sure you want to place this order?');
			// if the answer is true, submit the form
			if (answer) {
			form.submit();
			}
		}
    </script>
@endpush