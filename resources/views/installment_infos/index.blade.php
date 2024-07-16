@extends('layouts.master')
@section('main-content')
@section('page-css')
    <link rel="stylesheet" href="{{ asset('assets/styles/vendor/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/styles/vendor/nprogress.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/styles/vendor/datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/styles/vendor/flatpickr.min.css') }}">
@endsection

<div class="breadcrumb">
    <h1>{{ __('translate.Installment') }}</h1>
</div>

<div class="separator-breadcrumb border-top"></div>

<div id="section_purchase_list">
    <div class="card">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-12">
                    <form action="" method="get" class="row">
                        <div class="col-md-3">
                            <label for="fact_due_month_count">{{ __('translate.Unpaid') }} {{ __('translate.Month') }}</label>
                            <select class="form-select" name="fact_due_month_count" id="fact_due_month_count">
                                <option value="">{{ __('translate.All') }}</option>
                                @foreach ($fact_due_month_counts as $key => $value)
                                    <option value="{{ $value }}" @if (request()->fact_due_month_count == $value) selected @endif>
                                        {{ $value }} {{ __('translate.Month') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary mt-3">
                                <i class="i-Filter-2 me-2 font-weight-bold"></i> {{ __('translate.Filter') }}
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="purchase_table" class="table table-bordered">
                            {{-- Клиент	Дата покупки	Сумма	Оплачен	Общий долг	Фактической долг	Неоплачен	График --}}
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('translate.Client') }}</th>
                                    <th>{{ __('translate.Date') }}</th>
                                    <th>{{ __('translate.Amount') }}</th>
                                    <th>{{ __('translate.Paid') }}</th>
                                    <th>{{ __('translate.Total debt') }}</th>
                                    <th>{{ __('translate.Factual Debt') }}</th>
                                    <th>{{ __('translate.Unpaid') }}</th>
                                    <th>{{ __('translate.Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($installment_infos as $installment_info)
                                    <tr>
                                        <td>
                                            {{ $installment_info->id }}
                                        </td>
                                        <td>
                                            {{ $installment_info->sale->client->username }}
                                        </td>
                                        <td>
                                            {{ date('d-m-Y', strtotime($installment_info->sale->date)) }}
                                        </td>
                                        <td>
                                            {{ $installment_info->sale->GrandTotal }}
                                        </td>
                                        <td>
                                            {{ $installment_info->sale->paid_amount }}
                                        </td>
                                        <td>
                                            {{ $installment_info->sale->GrandTotal - $installment_info->sale->paid_amount }}
                                        </td>
                                        <td>
                                            {{ $installment_info->fact_due }}
                                        </td>
                                        <td>
                                            {{ $installment_info->fact_due_month_count }} {{ __('translate.Month') }}
                                        </td>
                                        <td>
                                            <!-- Button trigger modal -->
                                            <button type="button" class="btn btn-primary btn-sm Show_Payments"
                                                @click="Get_Installments({{ $installment_info->sale->id }})">
                                                Посмотреть график
                                            </button>
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


    <!-- Modal Filter -->
    <div class="modal fade" id="filter_purchase_modal" tabindex="-1" role="dialog"
        aria-labelledby="filter_purchase_modal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('translate.Filter') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <form method="POST" id="filter_purchase">
                        @csrf
                        <div class="row">

                        </div>

                        <div class="row mt-3">

                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="i-Filter-2 me-2 font-weight-bold"></i> {{ __('translate.Filter') }}
                                </button>
                                <button id="Clear_Form" class="btn btn-danger">
                                    <i class="i-Power-2 me-2 font-weight-bold"></i> {{ __('translate.Clear') }}
                                </button>
                            </div>
                        </div>


                    </form>

                </div>

            </div>
        </div>
    </div>

    <!-- Modal Show_payment -->
    <div class="modal fade" id="Show_payment" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('translate.Show_Payments') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <h2>{{ __('translate.Installment') }}</h2>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-md">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{ __('translate.date') }}</th>
                                            <th scope="col">{{ __('translate.Amount') }}</th>
                                            <th scope="col">{{ __('translate.Debt') }}</th>
                                            <th scope="col">{{ __('translate.Status') }}</th>
                                            <th scope="col">{{ __('translate.Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="installments.length <= 0">
                                            <td colspan="5">{{ __('translate.No_data_Available') }}</td>
                                        </tr>
                                        <tr v-for="installment in installments">
                                            <td>@{{ installment.date }}</td>
                                            <td>@{{ formatNumber(installment.amount, 2) }}</td>
                                            <td>@{{ formatNumber(installment.due, 2) }}</td>
                                            <td v-html="installment.status_html"></td>
                                            <td>

                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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
    Vue.component('v-select', VueSelect.VueSelect)
    Vue.component('validation-provider', VeeValidate.ValidationProvider);
    Vue.component('validation-observer', VeeValidate.ValidationObserver);

    var app = new Vue({
        el: '#section_purchase_list',
        data: {
            installments: [],
            installment_info: {},
            installment_next: 0,
            Sale_id: "",

        },

        methods: {

            //----------------------------------------- Get Installments  -------------------------------\\
            Get_Installments(id) {
                axios.get("/sale/sales/installments/" + id)
                    .then(response => {
                        this.installments = response.data.installments;
                        this.installment_info = response.data.installment_info;
                        this.installment_next = response.data.installment_next;
                        $('#Show_payment').modal('show');
                        setTimeout(() => {
                            NProgress.done();
                        }, 1000);
                    })
                    .catch(() => {
                        setTimeout(() => NProgress.done(), 500);
                    });
            },

            //------------------------------Formetted Numbers -------------------------\\
            formatNumber(number, dec) {
                const value = (typeof number === "string" ?
                    number :
                    number.toString()
                ).split(".");
                if (dec <= 0) return value[0];
                let formated = value[1] || "";
                if (formated.length > dec)
                    return `${value[0]}.${formated.substr(0, dec)}`;
                while (formated.length < dec) formated += "0";
                return `${value[0]}.${formated}`;
            },
        },

        //-----------------------------Autoload function-------------------
        created() {}

    })
</script>
@endsection
