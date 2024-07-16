@extends('layouts.master')
@section('main-content')
@section('page-css')
@endsection

<div class="breadcrumb">
    <h1>{{ __('translate.Create_Account') }}</h1>
</div>

<div class="separator-breadcrumb border-top"></div>

<div class="row" id="section_create_Account">
  <div class="col-lg-12 mb-3">
    <div class="card">

      <!--begin::form-->
      <form @submit.prevent="Create_Account()">
        <div class="card-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="account_name">{{ __('translate.Account_Name') }}<span
                  class="field_required">*</span></label>
              <input type="text" class="form-control" id="account_name"
                placeholder="{{ __('translate.Enter_Account_Name') }}" v-model="account.account_name">
              <span class="error" v-if="errors && errors.account_name">
                @{{ errors.account_name[0] }}
              </span>
            </div>

            <div class="form-group col-md-6">
              <label for="account_num">{{ __('translate.Account_Num') }}<span
                  class="field_required">*</span></label>
              <input type="text" class="form-control" id="account_num"
                placeholder="{{ __('translate.Enter_Account_Num') }}" v-model="account.account_num">
              <span class="error" v-if="errors && errors.account_num">
                @{{ errors.account_num[0] }}
              </span>
            </div>


            <div class="form-group col-md-6">
              <label for="initial_balance">{{ __('translate.Initial_Balance') }}<span
                  class="field_required">*</span></label>
              <input type="text" v-model="account.initial_balance" class="form-control" name="initial_balance"
                placeholder="{{ __('translate.Enter_Initial_Balance') }}" id="initial_balance">
              <span class="error" v-if="errors && errors.initial_balance">
                @{{ errors.initial_balance[0] }}
              </span>
            </div>

            <div class="form-group col-md-6">
              <label for="note">
                {{ __('translate.Please_provide_any_details') }}</label>
              <textarea type="text" v-model="account.note" class="form-control" name="note" id="note"
                placeholder="{{ __('translate.Please_provide_any_details') }}"></textarea>
            </div>

          </div>

          <div class="row mt-3">
            <div class="col-lg-6">
              <button type="submit" class="btn btn-primary" :disabled="SubmitProcessing">
                  <span v-if="SubmitProcessing" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <i class="i-Yes me-2 font-weight-bold"></i> {{ __('translate.Submit') }}
              </button>
            </div>
          </div>
      </form>

      <!-- end::form -->
    </div>
  </div>
</div>


@endsection

@section('page-js')

<script>
    var app = new Vue({
    el: '#section_create_Account',
    data: {
        SubmitProcessing:false,
        errors:[],
        account: {
            account_num: "",
            account_name:"",
            initial_balance:0,
            note:"",
        }, 
    },
   
   
    methods: {


        //------------------------ Create Account ---------------------------\\
        Create_Account() {
            var self = this;
            self.SubmitProcessing = true;
            axios.post("/accounting/account", {
                account_num: self.account.account_num,
                account_name: self.account.account_name,
                initial_balance: self.account.initial_balance,
                note: self.account.note,

            }).then(response => {
                    self.SubmitProcessing = false;
                    window.location.href = '/accounting/account'; 
                    toastr.success('{{ __('translate.Created_in_successfully') }}');                    
                    self.errors = {};
            })
            .catch(error => {
                self.SubmitProcessing = false;
                if (error.response.status == 422) {
                    self.errors = error.response.data.errors;
                }
                toastr.error('{{ __('translate.There_was_something_wronge') }}');
            });
        },

    },
    //-----------------------------Autoload function-------------------
    created () {
        
    },

})

</script>

@endsection