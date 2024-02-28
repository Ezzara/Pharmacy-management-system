@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Categories</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Categories</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<a href="#add_categories" data-toggle="modal" class="btn btn-primary float-right mt-2">Add Category</a>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="category-table" class="datatable table table-striped table-bordered table-hover table-center mb-0">
						<thead>
							<tr style="boder:1px solid black;">
								<th>Nama Obat</th>
								<th>Produsen</th>
								<th>Jenis</th>
								<th>Harga</th>
								<th>Stock</th>
								<th>Tgl Expired</th>
								<th class="text-center action-btn">Actions</th>
							</tr>
						</thead>
						<tbody>
												
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>			
</div>

<!-- Add Modal -->
<div class="modal fade" id="add_categories" aria-hidden="true" role="dialog">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Add Category</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="POST" action="{{route('categories.store')}}">
					@csrf
					<div class="row form-row">
						<div class="col-12">
							<div class="form-group">
								<label>Nama Produk</label>
								<input type="text" name="name" class="form-control">
								<label>Producer</label>
								<input type="text" name="producer" class="form-control">
								<label>Jenis</label>
								<input type="text" name="type" class="form-control">
								<label>Jenis Satuan</label>
								<input type="text" name="unit" class="form-control">
								<label>Harga</label>
								<input type="number" name="price" class="form-control">
							</div>
						</div>
					</div>
					<button type="submit" class="btn btn-primary btn-block">Save Changes</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- /ADD Modal -->

<!-- Edit Details Modal -->
<div class="modal fade" id="edit_category" aria-hidden="true" role="dialog">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Edit Category</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="post" action="{{route('categories.update')}}">
					@csrf
					@method("PUT")
					<div class="row form-row">
						<div class="col-12">
							<input type="hidden" name="id" id="edit_id">
							<div class="form-group">
								<label>Nama Obat</label>
								<input type="text" class="form-control edit_name" name="name">
								<label>Produsen</label>
								<input type="text" class="form-control edit_producer" name="producer">
								<label>Jenis</label>
								<input type="text" class="form-control edit_type" name="type">
								<label>Harga</label>
								<input type="number" class="form-control edit_price" name="price">
								<label>Stock</label>
								<input type="number" class="form-control edit_quantity" name="quantity">
								<label>Unit</label>
								<input type="text" class="form-control edit_unit" name="unit">
								<label>Tgl Expired</label>
								<input type="date" class="form-control edit_expiry_date" name="expiry_date">
							</div>
						</div>
						
					</div>
					<button type="submit" class="btn btn-primary btn-block">Save Changes</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- /Edit Details Modal --> 
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        var table = $('#category-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{route('categories.index')}}",
            columns: [
                {data: 'name', name: 'name'},
                {data: 'producer',name: 'producer'},
				{data: 'type',name:'type'},
				{data: 'price',name:'price'},
				{data: 'quantity',name:'quantity'},
				{data: 'expiry_date',name:'expiry_date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
        $('#category-table').on('click','.editbtn',function (){
            $('#edit_category').modal('show');
            var id = $(this).data('id');
            var name = $(this).data('name');
			var producer = $(this).data('producer');
			var type = $(this).data('type');
			var price = $(this).data('price');
			var quantity = $(this).data('quantity');
			var unit = $(this).data('unit');
			var expiry_date = $(this).data('expiry_date');

            $('#edit_id').val(id);
            $('.edit_name').val(name);
			$('.edit_producer').val(producer);
			$('.edit_type').val(type);
			$('.edit_price').val(price);
			$('.edit_quantity').val(quantity);
			$('.edit_unit').val(unit);
			$('.edit_expiry_date').val(expiry_date);
        });
        //
    });
</script> 
@endpush