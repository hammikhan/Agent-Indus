@extends('admin.layouts.app')

@section('styles')

@endsection

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ $api->name }} API Management</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.setting.apis.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $api->id }}">
                                @foreach ($api->data as $key => $value)
                                    <div class="mb-3 row">
                                        <label for="example-text-input" class="col-md-2 col-form-label">{{ $key }}:</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" name="data[{{ $key }}]" value="{{ $value }}" id="example-text-input">
                                        </div>
                                    </div>
                                @endforeach
                                <div class="d-flex align-items-start gap-3 mt-4">
                                    <button type="submit" class="btn btn-primary w-sm ms-auto">Update</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')

@endsection