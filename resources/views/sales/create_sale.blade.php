@extends('layouts.master')
@section('main-content')
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/vendor/nprogress.css')}}">
<link rel="stylesheet" href="{{asset('assets/styles/vendor/autocomplete.css')}}">
<link rel="stylesheet" href="{{asset('assets/styles/vendor/flatpickr.min.css')}}">


@endsection

<div class="breadcrumb">
  <h1>{{ __('translate.Add_Sale') }}</h1>
</div>

<div class="separator-breadcrumb border-top"></div>


<div class="row" id="section_add_sale">
  <div class="col-lg-12 mb-3">
    <validation-observer ref="create_sale">
      <form @submit.prevent="Submit_Sale">

        <div class="card">
          <div class="card-body">
            <div class="row">

              <!-- Date -->
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
                    v-model="sale.date">
                    <span class="error">@{{  validationContext.errors[0] }}</span>
                  </div>
                </validation-provider>
              </div>

              <!-- Customer -->
              <div class="form-group col-md-4">
                <validation-provider name="Customer" rules="required" v-slot="{ valid, errors }">
                  <label>{{ __('translate.Customer') }} <span class="field_required">*</span></label>
                  <v-select @input="Selected_Customer" v-model="sale.client_id" :reduce="label => label.value"
                    placeholder="{{ __('translate.Choose_Customer') }}"
                    :options="clients.map(clients => ({label: clients.username, value: clients.id}))"></v-select>
                  <span class="error">@{{ errors[0] }}</span>
                </validation-provider>
              </div>

              <!-- warehouse -->
              <div class="form-group col-md-4">
                <validation-provider name="warehouse" rules="required" v-slot="{ valid, errors }">
                  <label>{{ __('translate.warehouse') }} <span class="field_required">*</span></label>
                  <v-select @input="Selected_Warehouse" :disabled="details.length > 0"
                    placeholder="{{ __('translate.Choose_Warehouse') }}" v-model="sale.warehouse_id"
                    :reduce="(option) => option.value"
                    :options="warehouses.map(warehouses => ({label: warehouses.name, value: warehouses.id}))">
                  </v-select>
                  <span class="error">@{{ errors[0] }}</span>
                </validation-provider>
              </div>

            </div>
          </div>
        </div>

        <!-- Search Product -->
        <div class="card mt-5">
          <div class="card-body">
            <div class="row">

              <!-- Product -->
              <div class="col-md-12 mb-5 mt-3">
                <div id="autocomplete" class="autocomplete">
                    <input placeholder="{{ __('translate.Scan_Search_Product_by_Code_Name') }}"
                      @input='e => search_input = e.target.value' @keyup="search(search_input)" @focus="handleFocus"
                      @blur="handleBlur" ref="product_autocomplete" class="autocomplete-input" />
                    <ul class="autocomplete-result-list" v-show="focused">
                      <li class="autocomplete-result" v-for="product_fil in product_filter"
                        @mousedown="SearchProduct(product_fil)">@{{getResultValue(product_fil)}}</li>
                    </ul>
                </div>
              </div>

              <!-- Products -->
              <div class="col-md-12">
                <div class="table-responsive">
                  <table class="table table-hover table-md">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('translate.Product_Name') }}</th>
                        <th scope="col">{{ __('translate.Net_Unit_Price') }}</th>
                        <th scope="col">{{ __('translate.Current_Stock') }}</th>
                        <th scope="col">{{ __('translate.Qty') }}</th>
                        <th scope="col">{{ __('translate.Discount') }}</th>
                        <th scope="col">{{ __('translate.Tax') }}</th>
                        <th scope="col">{{ __('translate.SubTotal') }}</th>
                        <th scope="col">{{ __('translate.Action') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="details.length <=0">
                        <td colspan="9">{{ __('translate.No_data_Available') }}</td>
                      </tr>
                      <tr v-for="detail in details" :class="{ tr_back_error: detail.quantity < detail.qty_min }">
                        <td>@{{detail.detail_id}}</td>
                        <td>
                          <span>@{{detail.code}}</span>
                          <br>
                          <span class="badge badge-success">@{{detail.name}}</span>
                        </td>
                        <td>{{$currency}} @{{formatNumber(detail.Net_price, 2)}}</td>
                        <td>
                          <span class="badge badge-warning" v-if="detail.product_type == 'is_service'">----</span>
                          <span class="badge badge-warning" v-else>@{{detail.stock}} @{{detail.unitSale}}</span>
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            <span class="increment-decrement btn btn-light rounded-circle"
                              @click="decrement(detail ,detail.detail_id)">-</span>
                            <input class="fw-semibold cart-qty m-0 px-2"
                              @keyup="Verified_Qty(detail,detail.detail_id)" :min="0.00" :max="detail.stock"
                              v-model.number="detail.quantity">
  
                            <span class="increment-decrement btn btn-light rounded-circle"
                              @click="increment(detail ,detail.detail_id)">+</span>
                          </div>
                        </td>

                        <td>{{$currency}} @{{formatNumber(detail.DiscountNet * detail.quantity, 2)}}</td>
                        <td>{{$currency}} @{{formatNumber(detail.taxe * detail.quantity, 2)}}</td>
                        <td>{{$currency}} @{{detail.subtotal.toFixed(2)}}</td>
                        <td>
                          <a @click="Modal_Updat_Detail(detail)" class="cursor-pointer ul-link-action text-success"
                            title="Edit">
                            <i class="i-Edit"></i>
                          </a>
                          <a @click="delete_Product_Detail(detail.detail_id)"
                            class="cursor-pointer ul-link-action text-danger" title="Delete">
                            <i class="i-Close-Window"></i>
                          </a>
                        </td>
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
                        <span>{{$currency}} @{{sale.TaxNet.toFixed(2)}} (@{{formatNumber(sale.tax_rate,2)}} %)</span>
                      </td>
                    </tr>
                    <tr>
                      <td class="bold">{{ __('translate.Discount') }}</td>
                      <td v-if="sale.discount_type == 'fixed'"><span>{{$currency}} @{{sale.discount.toFixed(2)}}</span></td>
                      <td v-else> <span>{{$currency}} @{{sale.discount_percent_total.toFixed(2)}} (@{{formatNumber(sale.discount,2)}} %)</span></td>
                    </tr>
                    <tr>
                      <td class="bold">{{ __('translate.Shipping') }}</td>
                      <td>{{$currency}} @{{sale.shipping.toFixed(2)}}</td>
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

            </div>
          </div>
        </div>

        <!-- Charges -->
        <div class="card mt-5">
          <div class="card-body">
            <div class="row">

              {{-- Order_Tax --}}
              <div class="form-group col-md-4">
                <validation-provider name="Order Tax" :rules="{ regex: /^\d*\.?\d*$/}" v-slot="validationContext">
                  <label for="ordertax">{{ __('translate.Order_Tax') }} </label>
                  <div class="input-group">
                    <input :state="getValidationState(validationContext)" aria-describedby="OrderTax-feedback"
                      v-model.number="sale.tax_rate" @keyup="keyup_OrderTax()" type="text" class="form-control">

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
                    v-model.number="sale.discount" @keyup="keyup_Discount()" type="text" class="form-control">
                  <span class="error">@{{ validationContext.errors[0] }}</span>
                </validation-provider>
              
                <select class="form-select" id="inputGroupSelect02"
                  @change="Calcul_Total()" v-model="sale.discount_type">
                  <option value="fixed">Fixed</option>
                  <option value="percent">Percent %</option>
                </select>
              </div>
            
              {{-- Shipping --}}
              <div class="form-group col-md-4">
                <validation-provider name="Shipping" :rules="{ regex: /^\d*\.?\d*$/}" v-slot="validationContext">
                  <label for="shipping">{{ __('translate.Shipping') }} </label>
                  <div class="input-group">
                    <input :state="getValidationState(validationContext)" aria-describedby="Shipping-feedback"
                      v-model.number="sale.shipping" @keyup="keyup_Shipping()" type="text" class="form-control">
                      <span class="input-group-text">$</span>
                  </div>
                  <span class="error">@{{ validationContext.errors[0] }}</span>
                </validation-provider>
              </div>

              <div class="form-group col-md-12">
                <label for="note">{{ __('translate.Please_provide_any_details') }} </label>
                <textarea type="text" v-model="sale.notes" class="form-control" name="note" id="note"
                  placeholder="{{ __('translate.Please_provide_any_details') }}"></textarea>
              </div>


              <div class="col-lg-12 mt-5">
                <button type="submit" class="btn btn-primary" :disabled="paymentProcessing">
                  <span v-if="paymentProcessing" class="spinner-border spinner-border-sm" role="status"
                    aria-hidden="true"></span> <i class="i-Yes me-2 font-weight-bold"></i> {{ __('translate.Submit') }}
                </button>
              </div>
            </div>
          </div>
        </div>

      </form>
    </validation-observer>
  </div>

  <!-- Modal Update Detail Product -->
  <validation-observer ref="Update_Detail">
    <div class="modal fade" id="form_Update_Detail" tabindex="-1" role="dialog" aria-labelledby="form_Update_Detail"
      aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">@{{ detail.name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submit_Update_Detail">
              <div class="row">

                <!-- Unit Price -->
                <div class="form-group col-md-6">
                  <validation-provider name="Product Price" :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                    v-slot="validationContext">
                    <label for="Unit_price">{{ __('translate.Product_Price') }} <span
                        class="field_required">*</span></label>
                    <input :state="getValidationState(validationContext)" aria-describedby="Unit_price-feedback"
                      v-model.number="detail.Unit_price" type="text" class="form-control">
                    <span class="error">@{{ validationContext.errors[0] }}</span>
                  </validation-provider>
                </div>

                <!-- Tax Method -->
                <div class="form-group col-md-6">
                  <validation-provider name="Tax Method" rules="required" v-slot="{ valid, errors }">
                    <label>{{ __('translate.Tax_Method') }} <span class="field_required">*</span></label>
                    <v-select placeholder="{{ __('translate.Choose_Method') }}" v-model="detail.tax_method"
                      :reduce="(option) => option.value" :options="
                                                [
                                                  {label: 'Exclusive', value: '1'},
                                                  {label: 'Inclusive', value: '2'}
                                                ]">
                    </v-select>
                    <span class="error">@{{ errors[0] }}</span>
                  </validation-provider>
                </div>

                <!-- Tax Rate -->
                <div class="form-group col-md-6">
                  <validation-provider name="Order Tax" :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                    v-slot="validationContext">
                    <label for="ordertax">{{ __('translate.Order_Tax') }}
                      <span class="field_required">*</span></label>
                    <div class="input-group">
                      <input :state="getValidationState(validationContext)" aria-describedby="OrderTax-feedback"
                        v-model="detail.tax_percent" type="text" class="form-control">
                      <div class="input-group-append">
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <span class="error">@{{ validationContext.errors[0] }}</span>
                  </validation-provider>
                </div>

                <!-- Discount Method -->
                <div class="form-group col-md-6">
                  <validation-provider name="Discount_Method" rules="required" v-slot="{ valid, errors }">
                    <label>{{ __('translate.Discount_Method') }} <span class="field_required">*</span></label>
                    <v-select placeholder="{{ __('translate.Choose_Method') }}" v-model="detail.discount_Method"
                      :reduce="(option) => option.value" :options="
                                                [
                                                  {label: 'Percent %', value: '1'},
                                                  {label: 'Fixed', value: '2'}
                                                ]">
                    </v-select>
                    <span class="error">@{{ errors[0] }}</span>
                  </validation-provider>
                </div>

                <!-- Discount Rate -->
                <div class="form-group col-md-6">
                  <validation-provider name="Discount" :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                    v-slot="validationContext">
                    <label for="discount">{{ __('translate.Discount') }} <span class="field_required">*</span></label>
                    <input :state="getValidationState(validationContext)" aria-describedby="Discount-feedback"
                      v-model="detail.discount" type="text" class="form-control">
                    <span class="error">@{{ validationContext.errors[0] }}</span>
                  </validation-provider>
                </div>

                <!-- Unit Sale -->
                <div class="form-group col-md-6" v-if="detail.product_type != 'is_service'">
                  <validation-provider name="UnitSale" rules="required" v-slot="{ valid, errors }">
                    <label>{{ __('translate.Unit_Sale') }} <span class="field_required">*</span></label>
                    <v-select v-model="detail.sale_unit_id" :reduce="label => label.value"
                      placeholder="{{ __('translate.Choose_Unit_Sale') }}"
                      :options="units.map(units => ({label: units.name, value: units.id}))"></v-select>
                    <span class="error">@{{ errors[0] }}</span>
                  </validation-provider>
                </div>

                <!-- imei_number -->
                <div class="form-group col-md-12" v-show="detail.is_imei">
                  <label for="imei_number">{{ __('translate.Add_product_IMEI_Serial_number') }}</label>
                  <input v-model="detail.imei_number" type="text" class="form-control"
                    placeholder="{{ __('translate.Add_product_IMEI_Serial_number') }}">
                </div>

                <div class="col-lg-12">
                  <button type="submit" :disabled="Submit_Processing_detail" class="btn btn-primary">
                    <span v-if="Submit_Processing_detail" class="spinner-border spinner-border-sm" role="status"
                      aria-hidden="true"></span> <i class="i-Yes me-2 font-weight-bold"></i> {{ __('translate.Submit') }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </validation-observer>
</div>


@endsection

@section('page-js')
<script src="{{asset('assets/js/nprogress.js')}}"></script>
<script src="{{asset('assets/js/flatpickr.min.js')}}"></script>
<script src="{{asset('assets/js/autocomplete.js')}}"></script>

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
        el: '#section_add_sale',
      
        data: {
          focused: false,
          search_input:'',
          product_filter:[],
          paymentProcessing: false,
          Submit_Processing_detail:false,
          isLoading: true,
          warehouses:@json($warehouses),
          clients: @json($clients),
          client: {},
          products: [],
          details: [],
          detail: {},
          sales: [],
          sale: {
            id: "",
            date: moment().format('YYYY-MM-DD HH:mm'),
            statut: "completed",
            notes: "",
            client_id: "",
            warehouse_id: "",
            tax_rate: 0,
            TaxNet: 0,
            shipping: 0,
            discount: 0,
            discount_type:"fixed",
            discount_percent_total: 0,
          },
          timer:null,
          total: 0,
          GrandTotal: 0,
          units:[],
          product: {
            id: "",
            code: "",
            product_type:"",
            manage_stock:"",
            stock: "",
            quantity: 1,
            discount: "",
            DiscountNet: "",
            discount_Method: "",
            name: "",
            sale_unit_id:"",
            fix_stock:"",
            fix_price:"",
            unitSale: "",
            Net_price: "",
            Unit_price: "",
            Total_price: "",
            subtotal: "",
            product_id: "",
            detail_id: "",
            taxe: "",
            tax_percent: "",
            tax_method: "",
            product_variant_id: "",
            qty_min:"",
            is_imei: "",
            imei_number:"",
          }
        },

       
       
    methods: {

      
     handleFocus() {
      this.focused = true
    },

    handleBlur() {
      this.focused = false
    },

    formatDate(d){
        var m1 = d.getMonth()+1;
        var m2 = m1 < 10 ? '0' + m1 : m1;
        var d1 = d.getDate();
        var d2 = d1 < 10 ? '0' + d1 : d1;
        return [d.getFullYear(), m2, d2].join('-');
    },
    
      //---Validate State Fields
      getValidationState({ dirty, validated, valid = null }) {
        return dirty || validated ? valid : null;
      },


  
    //--- Submit Validate Create Sale
    Submit_Sale() {
      this.$refs.create_sale.validate().then(success => {
        if (!success) {
          toastr.error('{{ __('translate.Please_fill_the_form_correctly') }}');
        }else{
          this.Create_Sale();
        }
      });
    },
  
    //---------------------- Get_sales_units ------------------------------\\
    Get_sales_units(value) {
      axios
        .get("/products/Get_sales_units?id=" + value)
        .then(({ data }) => (this.units = data));
    },

    //---Submit Validation Update Detail
    submit_Update_Detail() {
      this.$refs.Update_Detail.validate().then(success => {
        if (!success) {
          toastr.error('{{ __('translate.Please_fill_the_form_correctly') }}');
        } else {
          this.Update_Detail();
        }
      });
    },


    //------ Show Modal Update Detail Product
    Modal_Updat_Detail(detail) {
      NProgress.start();
      NProgress.set(0.1);
      this.detail = {};
      this.Get_sales_units(detail.product_id);
      this.detail.detail_id = detail.detail_id;
      this.detail.sale_unit_id = detail.sale_unit_id;
      this.detail.name = detail.name;
      this.detail.Unit_price = detail.Unit_price;
      this.detail.fix_price = detail.fix_price;
      this.detail.fix_stock = detail.fix_stock;
      this.detail.stock = detail.stock;
      this.detail.tax_method = detail.tax_method;
      this.detail.discount_Method = detail.discount_Method;
      this.detail.discount = detail.discount;
      this.detail.quantity = detail.quantity;
      this.detail.tax_percent = detail.tax_percent;
      this.detail.is_imei = detail.is_imei;
      this.detail.imei_number = detail.imei_number;
       setTimeout(() => {
        NProgress.done();
        $('#form_Update_Detail').modal('show');
      }, 1000);
    },


    //------ Submit Update Detail Product
    Update_Detail() {
      NProgress.start();
      NProgress.set(0.1);
      this.Submit_Processing_detail = true;
      for (var i = 0; i < this.details.length; i++) {
        if (this.details[i].detail_id === this.detail.detail_id) {
          // this.convert_unit();
           for(var k=0; k<this.units.length; k++){
              if (this.units[k].id == this.detail.sale_unit_id) {
                if(this.units[k].operator == '/'){
                  this.details[i].stock       = this.detail.fix_stock  * this.units[k].operator_value;
                  this.details[i].unitSale    = this.units[k].ShortName;
                }else{
                  this.details[i].stock       = this.detail.fix_stock  / this.units[k].operator_value;
                  this.details[i].unitSale    = this.units[k].ShortName;
                }
              }
            }
            if (this.details[i].stock < this.details[i].quantity) {
              this.details[i].quantity = this.details[i].stock;
            } else {
              this.details[i].quantity =1;
            }

          this.detail.Unit_price = Number((this.detail.Unit_price).toFixed(2));

          this.details[i].Unit_price = this.detail.Unit_price;
          this.details[i].tax_percent = this.detail.tax_percent;
          this.details[i].tax_method = this.detail.tax_method;
          this.details[i].discount_Method = this.detail.discount_Method;
          this.details[i].discount = this.detail.discount;
          this.details[i].sale_unit_id = this.detail.sale_unit_id;
          this.details[i].imei_number = this.detail.imei_number;
          if (this.details[i].discount_Method == "2") {
            //Fixed
            this.details[i].DiscountNet = this.details[i].discount;
          } else {
            //Percentage %
            this.details[i].DiscountNet = parseFloat(
              (this.details[i].Unit_price * this.details[i].discount) / 100
            );
          }
          if (this.details[i].tax_method == "1") {
            //Exclusive
            this.details[i].Net_price = parseFloat(
              this.details[i].Unit_price - this.details[i].DiscountNet
            );
            this.details[i].taxe = parseFloat(
              (this.details[i].tax_percent *
                (this.details[i].Unit_price - this.details[i].DiscountNet)) /
                100
            );
          } else {
            //Inclusive
            this.details[i].Net_price = parseFloat(
              (this.details[i].Unit_price - this.details[i].DiscountNet) /
                (this.details[i].tax_percent / 100 + 1)
            );
            this.details[i].taxe = parseFloat(
              this.details[i].Unit_price -
                this.details[i].Net_price -
                this.details[i].DiscountNet
            );
          }
          this.$forceUpdate();
        }
      }
      this.Calcul_Total();
      setTimeout(() => {
        NProgress.done();
        this.Submit_Processing_detail = false;
        $('#form_Update_Detail').modal('hide');
      }, 1000);
    },

    
    // Search Products
    search(){
      if (this.timer) {
            clearTimeout(this.timer);
            this.timer = null;
      }
      if (this.search_input.length < 2) {
        return this.product_filter= [];
      }
      if (this.sale.warehouse_id != "" &&  this.sale.warehouse_id != null) {
        this.timer = setTimeout(() => {
          const product_filter = this.products.filter(product => product.code === this.search_input);
            if(product_filter.length === 1){
                this.SearchProduct(product_filter[0])
            }else{
                this.product_filter=  this.products.filter(product => {
                  return (
                    product.name.toLowerCase().includes(this.search_input.toLowerCase()) ||
                    product.code.toLowerCase().includes(this.search_input.toLowerCase()) ||
                    product.barcode.toLowerCase().includes(this.search_input.toLowerCase())
                    );
                });
            }
        }, 800);
      } else {
        toastr.error('{{ __('translate.Please_Select_Warehouse') }}');
      }
    },

    
    //------------------------- get Result Value Search Product
    getResultValue(result) {
      return result.code + " " + "(" + result.name + ")";
    },

    
    //------------------------- Submit Search Product
    SearchProduct(result) {
      this.product = {};
      if (
        this.details.length > 0 &&
        this.details.some(detail => detail.code === result.code)
      ) {
        toastr.error('{{ __('translate.Product_Already_added') }}');
      } else {
          this.product.manage_stock = result.manage_stock;

          if( result.product_type =='is_service'){
            this.product.quantity = 1;
            this.product.code = result.code;
          }else{
            this.product.code = result.code;
            this.product.stock = result.qte_sale;
            this.product.fix_stock = result.qte;
            if (result.qte_sale < 1) {
              this.product.quantity = result.qte_sale;
            }else if(result.qty_min !== 0){
                this.product.quantity = result.qty_min;
            } else {
              this.product.quantity = 1;
            }
          }
        this.product.product_variant_id = result.product_variant_id;
        this.Get_Product_Details(result.id, result.product_variant_id);
      }
      this.search_input= '';
      this.$refs.product_autocomplete.value = "";
      this.product_filter = [];
    },


    //---------------------- Event Select Warehouse ------------------------------\\
    Selected_Warehouse(value) {
      if (value === null) {
        this.sale.warehouse_id = "";
      }
      this.search_input= '';
      this.product_filter = [];
      this.Get_Products_By_Warehouse(value);
    },

    Selected_Customer(value){
      if (value === null) {
        this.sale.client_id = "";
       
      }
    },

    
     //------------------------------------ Get Products By Warehouse -------------------------\\
    Get_Products_By_Warehouse(id) {
      // Start the progress bar.
        NProgress.start();
        NProgress.set(0.1);
      axios
        .get("/products/products_by_Warehouse/" + id + "?stock=" + 1 + "&product_service=" + 1)
         .then(response => {
            this.products = response.data;
             NProgress.done();
            })
          .catch(error => {
          });
    },


    //----------------------------------------- Add Product to order list -------------------------\\
    add_product() {
      if (this.details.length > 0) {
        this.Last_Detail_id();
      } else if (this.details.length === 0) {
        this.product.detail_id = 1;
      }
      if(this.product.qty_min > this.product.fix_stock){
          toastr.error('Qté minimum de vente est '+ ' ' + '('+this.product.qty_min + ' ' + this.product.unitSale+')' + ' '+ 'Mais n\'est pas suffisant en stock');
        }else{
        this.details.push(this.product);
      }
      if(this.product.is_imei){
        this.Modal_Updat_Detail(this.product);
      }
    },

    
    //-----------------------------------Verified QTY ------------------------------\\
    Verified_Qty(detail, id) {
      for (var i = 0; i < this.details.length; i++) {
        if (this.details[i].detail_id === id) {
          if (isNaN(detail.quantity)) {
            this.details[i].quantity = detail.stock;
          }
          if (detail.quantity > detail.stock) {
            toastr.error('{{ __('translate.Low_Stock') }}');
            this.details[i].quantity = detail.stock;
          } else if(detail.quantity < detail.qty_min){
            toastr.warning('{{ __('translate.Minimum_sale_quantity_is')}}' + ' '+ detail.qty_min +' ' + detail.unitSale);
          }
          else {
            this.details[i].quantity = detail.quantity;
          }
        }
      }
      this.$forceUpdate();
      this.Calcul_Total();
    },


    //-----------------------------------increment QTY ------------------------------\\
    increment(detail, id) {
      for (var i = 0; i < this.details.length; i++) {
        if (this.details[i].detail_id == id) {
          if (detail.quantity + 1 > detail.stock) {
            toastr.error('{{ __('translate.Low_Stock') }}');
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
            if (detail.quantity - 1 > detail.stock) {
              toastr.error('{{ __('translate.Low_Stock') }}');
            } else if(detail.quantity - 1 < detail.qty_min){
              toastr.warning('{{ __('translate.Minimum_sale_quantity_is')}}' + ' '+ detail.qty_min +' ' + detail.unitSale);
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

      if (this.sale.discount_type == 'percent') {
          this.sale.discount_percent_total = parseFloat((this.total * this.sale.discount) / 100);
          const total_without_discount = parseFloat(this.total -  this.sale.discount_percent_total);

          this.sale.TaxNet = parseFloat((total_without_discount * this.sale.tax_rate) / 100);
          this.GrandTotal = parseFloat(total_without_discount + this.sale.TaxNet + this.sale.shipping);

          var grand_total =  this.GrandTotal.toFixed(2);
          this.GrandTotal = parseFloat(grand_total);

      } else {
          this.sale.discount_percent_total = 0;
          const total_without_discount = parseFloat(this.total - this.sale.discount);

          this.sale.TaxNet = parseFloat((total_without_discount * this.sale.tax_rate) / 100);
          this.GrandTotal = parseFloat(total_without_discount + this.sale.TaxNet + this.sale.shipping);
          var grand_total =  this.GrandTotal.toFixed(2);
          this.GrandTotal = parseFloat(grand_total);
      }
      
     
    },


    //-----------------------------------Delete Detail Product ------------------------------\\
    delete_Product_Detail(id) {
      for (var i = 0; i < this.details.length; i++) {
        if (id === this.details[i].detail_id) {
          this.details.splice(i, 1);
          this.Calcul_Total();
        }
      }
    },

        
        //-- check Qty of  details order if Null or zero
        verifiedForm() {
            if (this.details.length <= 0) {
              toastr.error('Veuillez ajouter le produit');
              return false;
            } else {
              var count = 0;
              for (var i = 0; i < this.details.length; i++) {
                if (
                  this.details[i].quantity == "" ||
                  this.details[i].quantity === null ||
                  this.details[i].quantity === 0
                ) {
                  // count += 1;
                  toastr.error('veuillez ajouter la quantité au produit');
                  return false;
                }
                else if(this.details[i].quantity < this.details[i].qty_min){
                  toastr.error('La quantité minimale de vente pour le produit' + ' ' + this.details[i].name + '  est' + ' '+ this.details[i].qty_min +' ' + this.details[i].unitSale);
                  return false;
                }
              }
              if (count > 0) {
                toastr.error('{{ __('translate.Please_Add_Quantity') }}');
                return false;
              } else {
                return true;
              }
            }
          },


    //---------- keyup OrderTax
    keyup_OrderTax() {
      if (isNaN(this.sale.tax_rate)) {
        this.sale.tax_rate = 0;
      } else if(this.sale.tax_rate == ''){
         this.sale.tax_rate = 0;
        this.Calcul_Total();
      }else {
        this.Calcul_Total();
      }
    },


    //---------- keyup Discount
    keyup_Discount() {
      if (isNaN(this.sale.discount)) {
        this.sale.discount = 0;
      } else if(this.sale.discount == ''){
         this.sale.discount = 0;
        this.Calcul_Total();
      }else {
        this.Calcul_Total();
      }
    },


    //---------- keyup Shipping
    keyup_Shipping() {
      if (isNaN(this.sale.shipping)) {
        this.sale.shipping = 0;
      } else if(this.sale.shipping == ''){
         this.sale.shipping = 0;
        this.Calcul_Total();
      }else {
        this.Calcul_Total();
      }
    },

    //--------------------------------- Create Sale -------------------------\\
    Create_Sale() {
      if (this.verifiedForm()) {
        // Start the progress bar.
        NProgress.start();
        NProgress.set(0.1);

          this.paymentProcessing = true;
          axios
            .post("/sale/sales", {
              date: this.sale.date,
              client_id: this.sale.client_id,
              warehouse_id: this.sale.warehouse_id,
              notes: this.sale.notes,
              tax_rate: this.sale.tax_rate?this.sale.tax_rate:0,
              TaxNet: this.sale.TaxNet?this.sale.TaxNet:0,
              discount: this.sale.discount?this.sale.discount:0,
              discount_type: this.sale.discount_type,
              discount_percent_total: this.sale.discount_percent_total?this.sale.discount_percent_total:0,
              shipping: this.sale.shipping?this.sale.shipping:0,
              GrandTotal: this.GrandTotal,
              details: this.details,
            })
            .then(response => {
              NProgress.done();
              this.paymentProcessing = false;
              toastr.success('{{ __('translate.Created_in_successfully') }}');
              window.location.href = '/sale/sales';
            })
            .catch(error => {
              NProgress.done();
              this.paymentProcessing = false;
              toastr.error('{{ __('translate.There_was_something_wronge') }}');
            });
        }
    },

    //-------------------------------- Get Last Detail Id -------------------------\\
    Last_Detail_id() {
      this.product.detail_id = 0;
      var len = this.details.length;
      this.product.detail_id = this.details[len - 1].detail_id + 1;
    },

    //---------------------------------Get Product Details ------------------------\\
    Get_Product_Details(product_id , variant_id) {
      axios.get("/products/show_product_data/" + product_id +"/"+ variant_id).then(response => {
        this.product.discount = 0;
        this.product.DiscountNet = 0;
        this.product.discount_Method = "2";
        this.product.product_id = response.data.id;
        this.product.name = response.data.name;
        this.product.product_type = response.data.product_type;
        this.product.Net_price = response.data.Net_price;
        this.product.Unit_price = response.data.Unit_price;
        this.product.taxe = response.data.tax_price;
        this.product.tax_method = response.data.tax_method;
        this.product.tax_percent = response.data.tax_percent;
        this.product.unitSale = response.data.unitSale;
        this.product.fix_price = response.data.fix_price;
        this.product.sale_unit_id = response.data.sale_unit_id;
        this.product.qty_min = response.data.qty_min;
        this.product.is_imei = response.data.is_imei;
        this.product.imei_number = '';
        this.add_product();
        this.Calcul_Total();
      });
    },
   
          
      },
      //-----------------------------Autoload function-------------------
      created() {
      }

  })

</script>

@endsection