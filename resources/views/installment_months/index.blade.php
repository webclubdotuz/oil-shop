@extends('layouts.master')
@section('main-content')
@section('page-css')
    <link rel="stylesheet" href="{{ asset('assets/styles/vendor/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/styles/vendor/nprogress.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/styles/vendor/datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/styles/vendor/flatpickr.min.css') }}">
@endsection

<div class="breadcrumb">
    <h1>Рассрочка месяцы</h1>
</div>

<div class="separator-breadcrumb border-top"></div>

<div id="section_purchase_list">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="text-end mb-3">
                        @can('purchases_add')
                            <!-- Button trigger modal -->
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modelId">
                                {{ __('translate.add') }}
                            </button>
                        @endcan
                    </div>

                    <div class="table-responsive">
                        <table id="purchase_table" class="display table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('translate.Month') }}</th>
                                    <th>{{ __('translate.Percentage') }}</th>
                                    <th>{{ __('translate.Description') }}</th>
                                    <th class="not_show">{{ __('translate.Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($installment_months as $installment_month)
                                    <tr>
                                        <td>{{ $installment_month->id }}</td>
                                        <td>{{ $installment_month->month }} {{ __('translate.Month') }}</td>
                                        <td>{{ $installment_month->percentage }} %</td>
                                        <td>{{ $installment_month->description }}</td>
                                        <td>
                                            <form action="{{ route('installment-months.destroy', $installment_month->id) }}" method="post">
                                                @csrf
                                                @method('DELETE')
                                                <div class="btn-group">
                                                    <button class="btn btn-primary btn-sm" type="button" onclick="editInstallmentMonth({{ $installment_month->id}},'{{ $installment_month->month }}','{{ $installment_month->percentage }}','{{ $installment_month->description }}')">{{ __('translate.Edit') }}</button>
                                                    <button class="btn btn-danger btn-sm" type="submit" onclick="return confirm('Вы уверены?')">{{ __('translate.Delete') }}</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Add Modal -->
<div class="modal fade" id="modelId" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('translate.Add_Installment') }}</h5>
                <button type="button" class="btn" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('installment-months.store') }}" method="post" class="row">
                    @csrf
                    <div class="col-6">
                        <label for="">{{ __('translate.Month') }}</label>
                        <input type="number" class="form-control" name="month" min="2"
                            required>
                    </div>
                    <div class="col-6">
                        <label for="">{{ __('translate.Percentage') }}</label>
                        <input type="number" class="form-control" name="percentage" min="0"
                            max="100" required>
                    </div>
                    <div class="col-12">
                        <label for="">{{ __('translate.Description') }}</label>
                        <textarea name="description" class="form-control" cols="30" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary mt-3">{{ __('translate.add') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('translate.Edit') }}</h5>
                <button type="button" class="btn" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="post" class="row">
                    @csrf
                    @method('PUT')
                    <div class="col-6">
                        <label for="">{{ __('translate.Month') }}</label>
                        <input type="number" class="form-control" name="month" id="month" min="2"
                            required>
                    </div>
                    <div class="col-6">
                        <label for="">{{ __('translate.Percentage') }}</label>
                        <input type="number" class="form-control" name="percentage" id="percentage" min="0"
                            max="100" required>
                    </div>
                    <div class="col-12">
                        <label for="">{{ __('translate.Description') }}</label>
                        <textarea name="description" id="description" class="form-control" cols="30" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary mt-3">{{ __('translate.Edit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>





@endsection

@section('page-js')
<script src="{{ asset('assets/js/vendor/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/js/nprogress.js') }}"></script>
<script src="{{ asset('assets/js/datepicker.min.js') }}"></script>

<script>
    function editInstallmentMonth(id, month, percentage, description) {
        $('#editModal').modal('show');
        $('#month').val(month);
        $('#percentage').val(percentage);
        $('#description').val(description);
        $('#editModal form').attr('action', 'installment-months/' + id);
    }
</script>


@endsection
