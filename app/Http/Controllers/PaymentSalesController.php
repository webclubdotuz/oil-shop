<?php

namespace App\Http\Controllers;

use Twilio\Rest\Client as Client_Twilio;
use GuzzleHttp\Client as Client_guzzle;
use App\Models\SMSMessage;
use Infobip\Api\SendSmsApi;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Illuminate\Support\Str;
use App\Models\EmailMessage;
use App\Mail\CustomEmail;
use App\utils\helpers;
use App\Models\PaymentMethod;
use App\Models\Account;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Client;
use App\Models\PaymentSale;
use App\Models\Sale;
use App\Mail\Payment_Sale;
use App\Models\Setting;
use App\Models\Currency;
use App\Services\EskizSmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use PDF;
use ArPHP\I18N\Arabic;
use Exception;

class PaymentSalesController extends Controller
{

    protected $currency;
    protected $symbol_placement;

    public function __construct()
    {
        $helpers = new helpers();
        $this->currency = $helpers->Get_Currency();
        $this->symbol_placement = $helpers->get_symbol_placement();
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $user_auth = auth()->user();
        if ($user_auth->can('payment_sales_add')) {

            $request->validate([
                'sale_id'  => 'required',
                'date'  => 'required',
                'payment_method_id'  => 'required',
            ]);

            if ($request['amount'] > 0) {

                DB::transaction(function () use ($request) {
                    $sale = Sale::findOrFail($request['sale_id']);

                    $total_paid = $sale->paid_amount + $request['amount'];
                    $due = $sale->GrandTotal - $total_paid;

                    if ($due === 0.0 || $due < 0.0) {
                        $payment_statut = 'paid';
                    } else if ($due !== $sale->GrandTotal) {
                        $payment_statut = 'partial';
                    } else if ($due === $sale->GrandTotal) {
                        $payment_statut = 'unpaid';
                    }

                    if ($sale->payment_statut == "installment") {
                        $installments = $sale->installments()->whereIn('status', ['unpaid', 'partial'])->orderBy('date', 'asc')->get();
                        $installment_count = $installments->count();
                        $req_amount = $request['amount'];
                        $ostatka = 0;
                        $loop = 1;

                        foreach ($installments as $installment) {
                            $installment_due = $installment->due;
                            $ostatka = $req_amount - $installment_due;

                            if ($ostatka >= 0) {
                                $installment->update([
                                    'status' => 'paid',
                                ]);
                                $req_amount = $ostatka;

                                PaymentSale::create([
                                    'sale_id'    => $request['sale_id'],
                                    'account_id' => $request['account_id'] ? $request['account_id'] : NULL,
                                    'installment_id' => $installment->id,
                                    'Ref'        => $this->generate_random_code_payment(),
                                    'date'       => $request['date'],
                                    'payment_method_id'  => $request['payment_method_id'],
                                    'montant'    => $installment_due,
                                    'change'     => 0,
                                    'notes'      => $request['notes'],
                                    'user_id'    => Auth::user()->id,
                                ]);

                                if ($installment_count == $loop) {
                                    $payment_statut = 'paid';

                                    $installment_info = $installment->installment_info;
                                    $installment_info->update([
                                        'status' => 'paid',
                                    ]);
                                } else {
                                    $payment_statut = 'installment';
                                }

                                $loop++;
                            } else {

                                $installment->update([
                                    'status' => 'partial',
                                ]);

                                PaymentSale::create([
                                    'sale_id'    => $request['sale_id'],
                                    'account_id' => $request['account_id'] ? $request['account_id'] : NULL,
                                    'installment_id' => $installment->id,
                                    'Ref'        => $this->generate_random_code_payment(),
                                    'date'       => $request['date'],
                                    'payment_method_id'  => $request['payment_method_id'],
                                    'montant'    => $req_amount,
                                    'change'     => 0,
                                    'notes'      => $request['notes'],
                                    'user_id'    => Auth::user()->id,
                                ]);

                                $payment_statut = 'installment';

                                break;
                            }
                        }
                    } else {

                        PaymentSale::create([
                            'sale_id'    => $request['sale_id'],
                            'account_id' => $request['account_id'] ? $request['account_id'] : NULL,
                            'Ref'        => $this->generate_random_code_payment(),
                            'date'       => $request['date'],
                            'payment_method_id'  => $request['payment_method_id'],
                            'montant'    => $request['amount'],
                            'change'     => 0,
                            'notes'      => $request['notes'],
                            'user_id'    => Auth::user()->id,
                        ]);
                    }



                    $account = Account::where('id', $request['account_id'])->exists();

                    if ($account) {
                        // Account exists, perform the update
                        $account = Account::find($request['account_id']);

                        $account->update([
                            'initial_balance' => $account->initial_balance + $request['amount'],
                        ]);
                    }

                    $sale->paid_amount = $total_paid;
                    $sale->payment_statut = $payment_statut;
                    $sale->save();
                }, 10);
            }

            return response()->json(['success' => true]);
        }
        return abort('403', __('You are not authorized'));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $user_auth = auth()->user();
        if ($user_auth->can('payment_sales_edit')) {

            $request->validate([
                'date'  => 'required',
                'payment_method_id'  => 'required',
            ]);

            DB::transaction(function () use ($id, $request) {
                $payment = PaymentSale::findOrFail($id);

                $sale = Sale::find($payment->sale_id);
                $old_total_paid = $sale->paid_amount - $payment->montant;
                $new_total_paid = $old_total_paid + $request['montant'];

                $due = $sale->GrandTotal - $new_total_paid;
                if ($due === 0.0 || $due < 0.0) {
                    $payment_statut = 'paid';
                } else if ($due !== $sale->GrandTotal) {
                    $payment_statut = 'partial';
                } else if ($due === $sale->GrandTotal) {
                    $payment_statut = 'unpaid';
                }

                try {

                    //delete old balance
                    $account = Account::where('id', $payment->account_id)->exists();

                    if ($account) {
                        // Account exists, perform the update
                        $account = Account::find($payment->account_id);
                        $account->update([
                            'initial_balance' => $account->initial_balance - $payment->montant,
                        ]);
                    }

                    $payment->update([
                        'date'    => $request['date'],
                        'payment_method_id'      => $request['payment_method_id'],
                        'account_id'             => $request['account_id'] ? $request['account_id'] : NULL,
                        'montant' => $request['montant'],
                        'notes'   => $request['notes'],
                    ]);

                    //update new account
                    $new_account = Account::where('id', $request['account_id'])->exists();

                    if ($new_account) {
                        // Account exists, perform the update
                        $new_account = Account::find($request['account_id']);
                        $new_account->update([
                            'initial_balance' => $new_account->initial_balance + $request['montant'],
                        ]);
                    }

                    $sale->update([
                        'paid_amount' => $new_total_paid,
                        'payment_statut' => $payment_statut,
                    ]);
                } catch (Exception $e) {
                    return response()->json(['message' => $e->getMessage()], 500);
                }
            }, 10);

            return response()->json(['success' => true, 'message' => 'Payment Update successfully'], 200);
        }
        return abort('403', __('You are not authorized'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $user_auth = auth()->user();
        if ($user_auth->can('payment_sales_delete')) {

            DB::transaction(function () use ($id) {
                $payment = PaymentSale::findOrFail($id);
                $sale = Sale::find($payment->sale_id);
                $total_paid = $sale->paid_amount - $payment->montant;
                $due = $sale->GrandTotal - $total_paid;

                if ($due === 0.0 || $due < 0.0) {
                    $payment_statut = 'paid';
                } else if ($due !== $sale->GrandTotal) {
                    $payment_statut = 'partial';
                } else if ($due === $sale->GrandTotal) {
                    $payment_statut = 'unpaid';
                }

                PaymentSale::whereId($id)->update([
                    'deleted_at' => Carbon::now(),
                ]);

                $account = Account::where('id', $payment->account_id)->exists();

                if ($account) {
                    // Account exists, perform the update
                    $account = Account::find($payment->account_id);
                    $account->update([
                        'initial_balance' => $account->initial_balance - $payment->montant,
                    ]);
                }

                $sale->update([
                    'paid_amount'    => $total_paid,
                    'payment_statut' => $payment_statut,
                ]);

                if ($payment->installment_id) {
                    $installment = $payment->installment;

                    if ($installment->due == 0) {
                        $installment_info = $installment->installment_info;
                        $installment_info->update([
                            'status' => 'unpaid',
                        ]);
                    } else {
                        $installment->update([
                            'status' => 'partial',
                        ]);
                    }

                    $sale->update([
                        'payment_statut' => 'installment',
                    ]);
                }
            }, 10);

            return response()->json(['success' => true, 'message' => 'Payment Delete successfully'], 200);
        }
        return abort('403', __('You are not authorized'));
    }



    //----------- Get Data for Create Payment Sale --------------\\

    public function get_data_create(Request $request, $id)
    {
        $Sale = Sale::findOrFail($id);
        $due = number_format($Sale->GrandTotal - $Sale->paid_amount, 2, '.', '');

        $payment_methods = PaymentMethod::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id', 'title']);
        $accounts = Account::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id', 'account_name']);

        $installments = $Sale->installments()->get();
        //         $installment_info = $sale->installment_info()->first();

        foreach ($installments as $installment) {
            if ($installment->due > 0) {
                $due = $installment->due;
                break;
            }
        }

        return response()->json(
            [
                'due' => $due,
                'payment_methods' => $payment_methods,
                'accounts' => $accounts,
            ]
        );
    }

    // generate_random_code_payment
    public function generate_random_code_payment()
    {
        $gen_code = 'INV/SL-' . date("Ymd") . '-' . substr(number_format(time() * mt_rand(), 0, '', ''), 0, 6);

        if (PaymentSale::where('Ref', $gen_code)->exists()) {
            $this->generate_random_code_payment();
        } else {
            return $gen_code;
        }
    }


    //----------- Payment Sale PDF --------------\\

    public function payment_sale(Request $request, $id)
    {
        $payment = PaymentSale::with('payment_method', 'sale', 'sale.client')->findOrFail($id);

        $payment_data['sale_Ref']     = $payment['sale']->Ref;
        $payment_data['client_name']  = $payment['sale']['client']->username;
        $payment_data['client_phone'] = $payment['sale']['client']->phone;
        $payment_data['client_adr']   = $payment['sale']['client']->address;
        $payment_data['client_email'] = $payment['sale']['client']->email;
        $payment_data['montant']      = $payment->montant;
        $payment_data['Ref']          = $payment->Ref;
        $payment_data['date']         = Carbon::parse($payment->date)->format('d-m-Y H:i');
        $payment_data['Reglement']    = $payment['payment_method']->title;

        $settings = Setting::where('deleted_at', '=', null)->first();

        $Html = view('pdf.payment_sale', [
            'setting' => $settings,
            'payment' => $payment_data,
        ])->render();

        $arabic = new Arabic();
        $p = $arabic->arIdentify($Html);

        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $utf8ar = $arabic->utf8Glyphs(substr($Html, $p[$i - 1], $p[$i] - $p[$i - 1]));
            $Html = substr_replace($Html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
        }

        $pdf = PDF::loadHTML($Html);

        return $pdf->download('payment_sale.pdf');
        //------------------


    }

    //------------- Send Payment Sale on Email -----------\\


    public function SendEmail(Request $request)
    {
        //PaymentSale
        $payment = PaymentSale::with('sale.client')->findOrFail($request->id);

        //settings
        $settings = Setting::where('deleted_at', '=', null)->first();

        //the custom msg of payment_received
        $emailMessage  = EmailMessage::where('name', 'payment_received')->first();

        if ($emailMessage) {
            $message_body = $emailMessage->body;
            $message_subject = $emailMessage->subject;
        } else {
            $message_body = '';
            $message_subject = '';
        }


        $payment_number = $payment->Ref;

        $total_amount = $this->render_price_with_symbol_placement(number_format($payment->montant, 2, '.', ','));

        $contact_name = $payment['sale']['client']->username;
        $business_name = $settings->CompanyName;

        //receiver email
        $receiver_email = $payment['sale']['client']->email;

        //replace the text with tags
        $message_body = str_replace('{contact_name}', $contact_name, $message_body);
        $message_body = str_replace('{business_name}', $business_name, $message_body);
        $message_body = str_replace('{payment_number}', $payment_number, $message_body);
        $message_body = str_replace('{total_amount}', $total_amount, $message_body);

        $email['subject'] = $message_subject;
        $email['body'] = $message_body;
        $email['company_name'] = $business_name;

        $this->Set_config_mail();

        $mail = Mail::to($receiver_email)->send(new CustomEmail($email));

        return $mail;
    }


    // Set config mail
    public function Set_config_mail()
    {
        $config = array(
            'driver' => env('MAIL_MAILER'),
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT'),
            'from' => array('address' => env('MAIL_FROM_ADDRESS'), 'name' =>  env('MAIL_FROM_NAME')),
            'encryption' => env('MAIL_ENCRYPTION'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'sendmail' => '/usr/sbin/sendmail -bs',
            'pretend' => false,
            'stream' => [
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ],
        );
        Config::set('mail', $config);
    }


    //-------------------Sms Notifications -----------------\\

    public function Send_SMS(Request $request)
    {
        //PaymentSale
        $payment = PaymentSale::with('sale.client')->findOrFail($request->id);

        //settings
        $settings = Setting::where('deleted_at', '=', null)->first();

        //the custom msg of payment_received
        $smsMessage  = SMSMessage::where('name', 'payment_received')->first();

        if ($smsMessage) {
            $message_text = $smsMessage->text;
        } else {
            $message_text = '';
        }

        $payment_number = $payment->Ref;

        $total_amount = $this->render_price_with_symbol_placement(number_format($payment->montant, 2, '.', ','));

        $contact_name = $payment['sale']['client']->username;
        $business_name = $settings->CompanyName;

        //receiver phone
        $receiverNumber = $payment['sale']['client']->phone;

        //replace the text with tags
        $message_text = str_replace('{contact_name}', $contact_name, $message_text);
        $message_text = str_replace('{business_name}', $business_name, $message_text);
        $message_text = str_replace('{payment_number}', $payment_number, $message_text);
        $message_text = str_replace('{total_amount}', $total_amount, $message_text);

        try {
            $sms_eskiz = new EskizSmsService();
            $sms_eskiz->sendSms($receiverNumber, $message_text);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true]);
    }



    // render_price_with_symbol_placement

    public function render_price_with_symbol_placement($amount)
    {

        if ($this->symbol_placement == 'before') {
            return $this->currency . ' ' . $amount;
        } else {
            return $amount . ' ' . $this->currency;
        }
    }
}
