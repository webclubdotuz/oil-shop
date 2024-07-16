@extends('layouts.master')
@section('main-content')
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/vendor/nprogress.css')}}">
<link rel="stylesheet" href="{{asset('assets/styles/vendor/flatpickr.min.css')}}">

@endsection

<div class="breadcrumb">
  <h1>{{ __('translate.Edit_Return') }}</h1>
</div>

<div class="separator-breadcrumb border-top"></div>


<div class="row" id="section_edit_sale_return">
  <div class="col-lg-12 mb-3">
    <validation-observer ref="edit_sale_return">
      <form @submit.prevent="Submit_Sale_return">

        <div class="card">
          <div class="card-body">
            <div class="row">

              <div class="col-md-4">
                <validation-provider name="date" rules="required" v-slot="validationContext">
                  <div class="form-group">
                    <label for="picker3">{{ __('translate.Date') }}</label>

                    <input type="text" 
                    :state="getValidationState(validationContext)" 
                    aria-describedby="date-feedback" 
                    class="form-control" 
                    placeholder="{{ __('translate.Select_Date') }}"  
                    id="datetimepicker" 
                    v-model="sale_return.date">

                    <span class="error">@{{  validationContext.errors[0] }}</span>
                  </div>
                </validation-provider>
              </div>

              <!-- Sale  -->
              <div class="form-group col-md-4">
                <label>{{ __('translate.Sale') }} <span class="field_required">*</span></label>
                <input type="text"  class="form-control" disabled v-model="sale_return.sale_ref">
             </div> 


              {{-- OrderTax --}}
              <div class="form-group col-md-4">
                <validation-provider name="Order Tax" :rules="{ regex: /^\d*\.?\d*$/}" v-slot="validationContext">
                  <label for="ordertax">{{ __('translate.Order_Tax') }} </label>
                  <div class="input-group">
                    <input :state="getValidationState(validationContext)" aria-describedby="OrderTax-feedback"
                      v-model.number="sale_return.tax_rate" @keyup="keyup_OrderTax()" type="text" class="form-control">

                    <span class="input-group-text">%</span>
                  </div>
                  <span class="error">@{{ validationContext.errors[0] }}</span>
                </validation-provider>
              </div>



              {{-- Discount --}}
              <div class="form-group col-md-4">
                <validation-provider name="Discount" :rules="{ regex: /^\d*\.?\d*$/}" v-slot="validationContext">
                  <label for="Discount">{{ __('translate.Discount') }} </label>
                  <input :state="getValidationState(validationContext)" aria-describedby="Discount-feedback"
                    v-model.number="sale_return.discount" @keyup="keyup_Discount()" type="text" class="form-control">
                  <span class="error">@{{ validationContext.errors[0] }}</span>
                </validation-provider>

                <select class="form-select" id="inputGroupSelect02" @change="Calcul_Total()"
                  v-model="sale_return.discount_type">
                  <option value="fixed">Fixed</option>
                  <option value="percent">Percent %</option>
                </select>
              </div>

              {{-- shipping --}}

              <div class="form-group col-md-4">
                <validation-provider name="Shipping" :rules="{ regex: /^\d*\.?\d*$/}" v-slot="validationContext">
                  <label for="shipping">{{ __('translate.Shipping') }} </label>
                  <div class="input-group">
                    <input :state="getValidationState(validationContext)" aria-describedby="Shipping-feedback"
                      v-model.number="sale_return.shipping" @keyup="keyup_Shipping()" type="text" class="form-control">
                    <span class="input-group-text">$</span>
                  </div>
                  <span class="error">@{{ validationContext.errors[0] }}</span>
                </validation-provider>
              </div>

            </div>
          </div>
        </div>

        <div class="card mt-5">
          <div class="card-body">
            <div class="row">

              <!-- Products -->
              <div class="col-md-12">
                <div class="table-responsive">
                  <table class="table table-hover table-md">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('translate.Product_Name') }}</th>
                        <th scope="col">{{ __('translate.Net_Unit_Price') }}</th>
                        <th scope="col">{{ __('translate.Quantity_sold') }}</th>
                        <th scope="col">{{ __('translate.Qty_return') }}</th>
                        <th scope="col">{{ __('translate.Discount') }}</th>
                        <th scope="col">{{ __('translate.Tax') }}</th>
                        <th scope="col">{{ __('translate.SubTotal') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="details.length <=0">
                        <td colspan="8">{{ __('translate.No_data_Available') }}</td>
                      </tr>
                      <tr v-for="detail in details">
                        <td>@{{detail.detail_id}}</td>
                        <td>
                          <span>@{{detail.code}}</span>
                          <br>
                          <span class="badge badge-success">@{{detail.name}}</span>
                        </td>
                        <td>{{$currency}} @{{formatNumber(detail.Net_price, 2)}}</td>
                        <td>
                          <span class="badge badge-warning">@{{detail.sale_quantity}}
                            @{{detail.unitSale}}</span>
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            <span class="increment-decrement btn btn-light rounded-circle"
                              @click="decrement(detail ,detail.detail_id)">-</span>
                            <input class="fw-semibold cart-qty m-0 px-2" @keyup="Verified_Qty(detail,detail.detail_id)"
                              :min="0.00" v-model.number="detail.quantity">

                            <span class="increment-decrement btn btn-light rounded-circle"
                              @click="increment(detail ,detail.detail_id)">+</span>
                          </div>
                        </td>

                        <td>{{$currency}} @{{formatNumber(detail.DiscountNet * detail.quantity, 2)}}</td>
                        <td>{{$currency}} @{{formatNumber(detail.taxe * detail.quantity, 2)}}</td>
                        <td>{{$currency}} @{{detail.subtotal.toFixed(2)}}</td>

                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="offset-md-9 col-md-3 mt-4">
                <table class="table table-striped table-sm">
                  <tbody>
                    <tr>
                      <td class="bold">{{ __('translate.Order_Tax') }}</td>
                      <td>
                        <span>{{$currency}} @{{sale_return.TaxNet.toFixed(2)}}
                          (@{{formatNumber(sale_return.tax_rate,2)}}
                          %)</span>
                      </td>
                    </tr>
                    <tr>
                      <td class="bold">{{ __('translate.Discount') }}</td>
                      <td v-if="sale_return.discount_type == 'fixed'"><span>{{$currency}}
                          @{{sale_return.discount.toFixed(2)}}</span></td>
                      <td v-else> <span>{{$currency}} @{{sale_return.discount_percent_total.toFixed(2)}}
                          (@{{formatNumber(sale_return.discount,2)}} %)</span></td>
                    </tr>
                    <tr>
                      <td class="bold">{{ __('translate.Shipping') }}</td>
                      <td>{{$currency}} @{{sale_return.shipping.toFixed(2)}}</td>
                    </tr>
                    <tr>
                      <td>
                        <span class="font-weight-bold">{{ __('translate.Total') }}</span>
                      </td>
                      <td>
                        <span class="font-weight-bold">{{$currency}} @{{GrandTotal.toFixed(2)}}</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="form-group col-md-12">
                <label for="note" class="ul-form__label">{{ __('translate.Please_provide_any_details') }} </label>
                <textarea type="text" v-model="sale_return.notes" class="form-control" name="note" id="note"
                  placeholder="{{ __('translate.Please_provide_any_details') }}"></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-lg-6">
            <button type="submit" class="btn btn-primary" :disabled="SubmitProcessing">
              <span v-if="SubmitProcessing" class="spinner-border spinner-border-sm" role="status"
                aria-hidden="true"></span> <i class="i-Yes me-2 font-weight-bold"></i> {{ __('translate.Submit') }}
            </button>
          </div>
        </div>

      </form>
    </validation-observer>
  </div>
</div>

@endsection

@section('page-js')
<script src="{{asset('assets/js/nprogress.js')}}"></script>
<script src="{{asset('assets/js/flatpickr.min.js')}}"></script>


<script type="text/javascript">
  $(function () {
      "use strict";

      $(document).ready(function () {

        flatpickr("#datetimepicker", {
          enableTime: true,
          dateFormat: "Y-m-d H:i"
        });

      });

    });
</script>

<script>
  Vue.component('v-select', VueSelect.VueSelect)
  Vue.component('validation-provider', VeeValidate.ValidationProvider);
  Vue.component('validation-observer', VeeValidate.ValidationObserver);

    var app = new Vue({
        el: '#section_edit_sale_return',
        data: {
          SubmitProcessing:false,
          details: @json($details),
          detail: {},
          sale_return:@json($sale_return),
          total: 0,
          GrandTotal: @json($sale_return['GrandTotal']),
        },

       
       
    methods: {


    //--- Submit Validate Update Sale Return
    Submit_Sale_return() {
      this.$refs.edit_sale_return.validate().then(success => {
        if (!success) {
          toastr.error('{{ __('translate.Please_fill_the_form_correctly') }}');
        } else {
          this.Update_Return();
        }
      });
    },
  
    //---Validate State Fields
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
 
   //-----------------------------------Verified QTY ------------------------------\\
   Verified_Qty(detail, id) {
      for (var i = 0; i < this.details.length; i++) {
        if (this.details[i].detail_id == id) {
          if (isNaN(detail.quantity)) {
            this.details[i].quantity = 1;
          }

          if (detail.quantity > detail.sale_quantity) {
            toastr.error('{{ __('translate.qty_return_is_greater_than_qty_sold') }}');
            this.details[i].quantity = detail.sale_quantity;
          } else {
            this.details[i].quantity = detail.quantity;
          }

          this.Calcul_Total();
          this.$forceUpdate();
        }
      }
    },

   
      //-----------------------------------increment QTY ------------------------------\\
      increment(detail, id) {
        for (var i = 0; i < this.details.length; i++) {
          if (this.details[i].detail_id == id) {
            if (detail.quantity + 1 > detail.sale_quantity) {
              toastr.error('{{ __('translate.qty_return_is_greater_than_qty_sold') }}');
            } else {
              this.details[i].quantity = Number((this.details[i].quantity + 1).toFixed(2));
            }
          }
        }
        this.$forceUpdate();
        this.Calcul_Total();
      },
  
  
      //-----------------------------------decrement QTY ------------------------------\\
      decrement(detail, id) {
        for (var i = 0; i < this.details.length; i++) {
          if (this.details[i].detail_id == id) {
            if (detail.quantity - 1 > 0) {
              if (detail.quantity - 1 > detail.sale_quantity) {
                toastr.error('{{ __('translate.qty_return_is_greater_than_qty_sold') }}');
              } else {
                this.details[i].quantity = Number((this.details[i].quantity - 1).toFixed(2));
              }
            }
          }
        }
        this.$forceUpdate();
        this.Calcul_Total();
      },



    //------------------------------Formetted Numbers -------------------------\\
    formatNumber(number, dec) {
      const value = (typeof number === "string"
        ? number
        : number.toString()
      ).split(".");
      if (dec <= 0) return value[0];
      let formated = value[1] || "";
      if (formated.length > dec)
        return `${value[0]}.${formated.substr(0, dec)}`;
      while (formated.length < dec) formated += "0";
      return `${value[0]}.${formated}`;
    },

   //-----------------------------------------Calcul Total ------------------------------\\
  Calcul_Total() {
      this.total = 0;
      for (var i = 0; i < this.details.length; i++) {
        var tax = this.details[i].taxe * this.details[i].quantity;
        this.details[i].subtotal = parseFloat(
          this.details[i].quantity * this.details[i].Net_price + tax
        );
        this.total = parseFloat(this.total + this.details[i].subtotal);
      }

      if (this.sale_return.discount_type == 'percent') {
          this.sale_return.discount_percent_total = parseFloat((this.total * this.sale_return.discount) / 100);
          const total_without_discount = parseFloat(this.total -  this.sale_return.discount_percent_total);

          this.sale_return.TaxNet = parseFloat((total_without_discount * this.sale_return.tax_rate) / 100);
          this.GrandTotal = parseFloat(total_without_discount + this.sale_return.TaxNet + this.sale_return.shipping);

          var grand_total =  this.GrandTotal.toFixed(2);
          this.GrandTotal = parseFloat(grand_total);

      } else {
          this.sale_return.discount_percent_total = 0;
          const total_without_discount = parseFloat(this.total - this.sale_return.discount);

          this.sale_return.TaxNet = parseFloat((total_without_discount * this.sale_return.tax_rate) / 100);
          this.GrandTotal = parseFloat(total_without_discount + this.sale_return.TaxNet + this.sale_return.shipping);
          var grand_total =  this.GrandTotal.toFixed(2);
          this.GrandTotal = parseFloat(grand_total);
      }
      
     
  },

    //---------- keyup OrderTax
    keyup_OrderTax() {
      if (isNaN(this.sale_return.tax_rate)) {
        this.sale_return.tax_rate = 0;
      } else if(this.sale_return.tax_rate == ''){
         this.sale_return.tax_rate = 0;
        this.Calcul_Total();
      }else {
        this.Calcul_Total();
      }
    },

    //---------- keyup Discount
    keyup_Discount() {
      if (isNaN(this.sale_return.discount)) {
        this.sale_return.discount = 0;
       } else if(this.sale_return.discount == ''){
         this.sale_return.discount = 0;
        this.Calcul_Total();
      }else {
        this.Calcul_Total();
      }
    },

    //---------- keyup Shipping
    keyup_Shipping() {
      if (isNaN(this.sale_return.shipping)) {
        this.sale_return.shipping = 0;
       } else if(this.sale_return.shipping == ''){
         this.sale_return.shipping = 0;
        this.Calcul_Total();
      }else {
        this.Calcul_Total();
      }
    },
   
    //-----------------------------------verified Qty If Null || 0 ------------------------------\\
    verifiedForm() {
      if (this.details.length <= 0) {
        toastr.error('{{ __('translate.Please_Add_Product_To_List') }}');
        return false;
      } else {
        var count = 0;
        for (var i = 0; i < this.details.length; i++) {
          if (
            this.details[i].quantity != "" ||
            this.details[i].quantity !== 0
          ) {
            count += 1;
          }
        }
        if (count === 0) {
          toastr.error('{{ __('translate.Please_add_return_quantity') }}');
          return false;
        } else {
          return true;
        }
      }
    },

    //--------------------------------- Update Return -------------------------\\
    Update_Return() {
      if (this.verifiedForm()) {
        this.SubmitProcessing = true;
        NProgress.start();
        NProgress.set(0.1);
        axios
          .put('/sales-return/returns_sale/'+ this.sale_return.id, {
            date: this.sale_return.date,
            client_id: this.sale_return.client_id,
            sale_id: this.sale_return.sale_id,
            warehouse_id: this.sale_return.warehouse_id,
            statut: this.sale_return.statut,
            notes: this.sale_return.notes,
            tax_rate: this.sale_return.tax_rate?this.sale_return.tax_rate:0,
            TaxNet: this.sale_return.TaxNet?this.sale_return.TaxNet:0,
            discount: this.sale_return.discount?this.sale_return.discount:0,
            discount_type: this.sale_return.discount_type,
            discount_percent_total: this.sale_return.discount_percent_total?this.sale_return.discount_percent_total:0,
            shipping: this.sale_return.shipping?this.sale_return.shipping:0,
            GrandTotal: this.GrandTotal,
            details: this.details
          })
          .then(response => {
            NProgress.done();
            this.SubmitProcessing = false;
            window.location.href = '/sales-return/returns_sale';
            toastr.success('{{ __('translate.Updated_in_successfully') }}');
          })
          .catch(error => {
            NProgress.done();
            this.SubmitProcessing = false;
            toastr.error('{{ __('translate.There_was_something_wronge') }}');
          });
      }
    },

    

      
  },
  
  //-----------------------------Autoload function-------------------
  created() {
  }

})

</script>

@endsection