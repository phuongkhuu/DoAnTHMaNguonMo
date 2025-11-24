@extends('layoutProduct')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h3>Thêm product</h3>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('product.index') }}" class="btn btn-primary float-end">Danh sách sản phẩm</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('product.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <strong>ID</strong>
                                <input type="text" name="product_id" class="form-control" placeholder="Nhập ID">
                            </div>
                            <div class="form-group">
                                <strong>Name</strong>
                                <input type="text" name="product_name" class="form-control" placeholder="Nhập tên">
                            </div>
                            <div class="form-group">
                                <strong>Description</strong>
                                <input type="text" name="description" class="form-control" placeholder="Nhập mô tả">
                            </div>
                            <div class="form-group">
                                <strong>Price</strong>
                                <input type="text" name="price" class="form-control" placeholder="Nhập giá">
                            </div>
                            <div class="form-group">
                                <strong>Discount</strong>
                                <input type="text" name="discount" class="form-control" placeholder="Nhập giá đã giảm">
                            </div>
                            <div class="form-group">
                                <strong>Stock</strong>
                                <input type="text" name="stock" class="form-control" placeholder="Nhập số lượng">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success mt-2">Lưu</button>
                </form>
            </div>
        </div>
    </div>
@endsection