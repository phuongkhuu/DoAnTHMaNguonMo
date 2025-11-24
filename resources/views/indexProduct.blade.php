@extends('layoutProduct')

@section('content')
    <div class = "container">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h3>Product</h3>
                    </div>
                    <div class="col-md-6">
                        <a href="{{route('product.create')}}" class="btn btn-primary float-end">Thêm</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if (Session::has('thongbao'))
                    <div class="alert alert-success">
                        {{ Session::get('thongbao') }}
                    </div>
                
                @endif
                <table class="table table-bodered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Stock</th>
                            <th>Image</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($product as $pd)
                        <tr>
                            <td>{{++$i}}</td>
                            <td>{{$pd->product_name}}</td>
                            <td>{{$pd->description}}</td>
                            <td>{{$pd->price}}</td>
                            <td>{{$pd->discount}}</td>
                            <td>{{$pd->stock}}</td>
                            <td>{{$pd->image}}</td>
                            <td>
                                <form action="{{ route('product.destroy', $pd->product_id) }}" method="POST">
                                    <a href="{{ route('product.edit', $pd->product_id) }}" class="btn btn-info">Sửa</a>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection