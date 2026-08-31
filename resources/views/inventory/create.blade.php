@extends('layouts.app') @section('content')<div class="page-head"><h1>New Inventory Product</h1></div><div class="panel form-panel"><form method="post" action="{{ route('inventory.store') }}" enctype="multipart/form-data">@csrf<div class="form-grid"><label>Product name*<input name="name" required></label><label>SKU<input name="sku" readonly id="skuField"></label><label>Barcode<input name="barcode"></label><label>Main Category*<select name="main_category_id" id="mainCategorySelect" required onchange="filterSubcategories()"><option value="">Select Main Category</option>@foreach($mainCategories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></label><label>Subcategory*<select name="category_id" id="subcategorySelect" required><option value="">Select Main Category First</option></select></label><label>Brand<input name="brand"></label><label>Part number<input name="part_number"></label><label>Cost price<input name="cost_price" type="number" step=".01"></label><label>Selling price*<input name="selling_price" type="number" step=".01" required></label><label>Minimum stock*<input name="minimum_stock" type="number" value="0" required></label><label>Opening stock*<input name="opening_stock" type="number" step=".001" value="0" required></label><label class="wide">Product Image<input type="file" name="image" accept="image/*"></label></div><button class="primary">Create Product</button></form></div>

<script>
const subcategoriesData = @json($subcategories);
document.getElementById('skuField').value='PRD-{{ date('Y') }}-'+'{{ str_pad(\App\Models\Product::max('id')+1, 6, '0', STR_PAD_LEFT) }}';
function filterSubcategories(){
const mainCategoryId=document.getElementById('mainCategorySelect').value;
const subcategorySelect=document.getElementById('subcategorySelect');
subcategorySelect.innerHTML='<option value="">Select Subcategory</option>';
if(mainCategoryId){
const categorySubcategories=subcategoriesData.filter(c=>c.parent_id==mainCategoryId);
categorySubcategories.forEach(c=>{
const option=document.createElement('option');
option.value=c.id;
option.textContent=c.name;
subcategorySelect.appendChild(option);
});
}
}
</script>@endsection
