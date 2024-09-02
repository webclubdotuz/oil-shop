@extends('layouts.master')
@section('main-content')
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/vendor/datatables.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/styles/vendor/nprogress.css')}}">
<link rel="stylesheet" href="{{asset('assets/styles/vendor/daterangepicker.css')}}">
@endsection

<div class="breadcrumb">
    <h1>Отчет касса</h1>
</div>

<div class="separator-breadcrumb border-top"></div>

<div id="section_sale_report">

    <div class="row mt-3">
        <div class="col-md-12">
            <form action="" method="get" class="row g-2">
                <div class="col-6">
                    <label for="start_date">Начало</label>
                    <input type="text" name="start_date" id="start_date" class="form-control" value="{{ $start_date }}">
                </div>
                <div class="col-6">
                    <label for="end_date">Конец</label>
                    <input type="text" name="end_date" id="end_date" class="form-control" value="{{ $end_date }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Показать</button>
                </div>
            </form>
        </div>
        <div class="col-12">
            <div class="row">
                <div class="col-6">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th colspan="2">Состояние склада</th>
                            </tr>
                            <tr>
                                <td>Остаток товара</td>
                                {{-- <td>{{ $product_summa }}</td> --}}
                                <td>$ {{ nf($product_summa) }}</td>
                            </tr>
                            <tr>
                                <td>Долг от поставщиков</td>
                                <td>$ {{ nf($total_debt) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="table">
                        <tbody>
                            <tr>
                                <th colspan="2">Касса</th>
                            </tr>
                            <tr>
                                <td>Одобрено</td>
                                <td>{{ nf($payment_sales->sum('montant')) }} uzs</td>
                            </tr>
                            @foreach ($payment_methods as $payment_method)
                            <tr>
                                <td>{{ $payment_method->title }}</td>
                                <td>{{ nf($payment_sales->where('payment_method_id', $payment_method->id)->sum('montant')) }} uzs</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="col-6">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th colspan="2">Продажа и прибыль</th>
                            </tr>
                            <tr>
                                <td>Сумма продаж</td>
                                <td>{{ nf($sales->sum('GrandTotal')) }} uzs</td>
                            </tr>
                            <tr>
                                <td>Одобрено</td>
                                <td>{{ nf($sales->sum('paid_amount')) }} uzs</td>
                            </tr>
                            <tr>
                                <td>Долг</td>
                                <td>{{ nf($sales->sum('GrandTotal') - $sales->sum('paid_amount')) }} uzs</td>
                            </tr>
                            <tr>
                                <td>Себестоимость продаж</td>
                                <td>{{ nf($CostTotalUzs) }} uzs (${{ nf($sales->sum('CostTotal')) }})</td>
                            </tr>
                            <tr>
                                <td>Общий прибыль</td>
                                <td>{{ nf($sales->sum('GrandTotal') - $CostTotalUzs) }} uzs</td>
                            </tr>
                            <tr>
                                <td>Чистая прибыль с долгом</td>
                                <td>
                                    {{ nf($sales->sum('paid_amount')-$CostTotalUzs) }} uzs
                                </td>
                            </tr>
                            <tr>
                                <td>Процент прибылья</td>
                                <td>
                                    @if ($sales->sum('GrandTotal'))
                                    {{ nf(($sales->sum('GrandTotal') - $CostTotalUzs) * 100 / $sales->sum('GrandTotal')) }}%
                                    @else
                                    0%
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Количество чеков</td>
                                <td>
                                    {{ $sales->count() }}
                                </td>
                            </tr>
                            <tr>
                                <td>Средний чек</td>
                                <td>
                                    @if ($sales->count())
                                    {{ nf($sales->sum('GrandTotal') / $sales->count()) }} uzs
                                    @else
                                    0 uzs
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


</div>
@endsection

@section('page-js')

<script src="{{asset('assets/js/vendor/datatables.min.js')}}"></script>
<script src="{{asset('assets/js/daterangepicker.min.js')}}"></script>
<script src="{{asset('assets/js/nprogress.js')}}"></script>



@endsection
