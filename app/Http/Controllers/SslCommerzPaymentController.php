<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Library\SslCommerz\SslCommerzNotification;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\BillingDetails;
use App\Models\Inventory;
use App\Models\Cart;
use Mail;
use App\Mail\InvoiceSend;
use Auth;
use Carbon\Carbon;

class SslCommerzPaymentController extends Controller
{

    public function ssl_pay()
    {
        return view('ssl_pay');
    }

    public function index(Request $request)
    {
        # Here you have to receive all the order data to initate the payment.
        # Let's say, your oder transaction informations are saving in a table called "orders"
        # In "orders" table, order unique identity is "transaction_id". "status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        $post_data['total_amount'] = $request->total; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = 'Customer Name';
        $post_data['cus_email'] = 'customer@mail.com';
        $post_data['cus_add1'] = 'Customer Address';
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = '8801XXXXXXXXX';
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "Computer";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";

        #Before  going to initiate the payment order status need to insert or update as Pending.
        $update_product = DB::table('sslorders')
            ->where('transaction_id', $post_data['tran_id'])
            ->updateOrInsert([
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'amount' => $post_data['total_amount'],
                'status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency']
            ]);

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'hosted');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

    public function payViaAjax(Request $request)
    {

        # Here you have to receive all the order data to initate the payment.
        # Lets your oder trnsaction informations are saving in a table called "orders"
        # In orders table order uniq identity is "transaction_id","status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        $post_data['total_amount'] = '10'; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = 'Customer Name';
        $post_data['cus_email'] = 'customer@mail.com';
        $post_data['cus_add1'] = 'Customer Address';
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = '8801XXXXXXXXX';
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "Computer";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";


        #Before  going to initiate the payment order status need to update as Pending.
        $update_product = DB::table('sslorders')
            ->where('transaction_id', $post_data['tran_id'])
            ->updateOrInsert([
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'amount' => $post_data['total_amount'],
                'status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency']
            ]);

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

    public function success(Request $request)
    {
        $data = session('data');
             $order_id = Order::insertGetId([
                'user_id'=>Auth::guard('customerlogin')->id(),
                 'sub_total'=>$data['sub_total'],
                 'discount'=>$data['discount'],
                 'charge'=>$data['charge'],
                 'total'=>$data['total'],
                 'created_at'=>Carbon::now(),
             ]);

             BillingDetails::insert([
                 'user_id'=>Auth::guard('customerlogin')->id(),
                 'order_id'=>$order_id,
                 'name'=>$data['name'],
                 'email'=>$data['email'],
                 'phone'=>$data['phone'],
                 'company'=>$data['company'],
                 'country_id'=>$data['country_id'],
                 'city_id'=>$data['city_id'],
                 'address'=>$data['address'],
                 'notes'=>$data['notes'],
                 'created_at'=>Carbon::now(),
             ]);
             $carts = Cart::where('customer_id',Auth::guard('customerlogin')->id())->get();
             foreach ($carts as  $cart) {
                 OrderProduct::insert([
                     'user_id'=>Auth::guard('customerlogin')->id(),
                     'order_id'=>$order_id,
                     'product_id'=>$cart->product_id,
                     'color_id'=>$cart->color_id,
                     'size_id'=>$cart->size_id,
                     'quantity'=>$cart->quantity,
                     'price'=>$cart->rel_to_product->after_discount,
                     'created_at'=>Carbon::now(),
                 ]);
                    Inventory::where('product_id',$cart->product_id)->where('color_id',$cart->color_id)->where('size_id',$cart->size_id)->decrement('quantity',$cart->quantity);
             }

             //SMS SEND
             function sms_send() {
             $url = "https://bulksmsbd.net/api/smsapi";
             $api_key = "ei6gq0Dj1AvcqXnk7GbM";
             $senderid = "tusharalam";
             $number = $request->phone;
             $message = "Thank you for purchasing our product. You total amount is: ".$request->total;
         
             $data = [
                 "api_key" => $api_key,
                 "senderid" => $senderid,
                 "number" => $number,
                 "message" => $message
             ];
             $ch = curl_init();
             curl_setopt($ch, CURLOPT_URL, $url);
             curl_setopt($ch, CURLOPT_POST, 1);
             curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
             $response = curl_exec($ch);
             curl_close($ch);
             return $response;

        }

        //e-mail
        Mail::to($request->email)->send(new InvoiceSend($order_id));
            //after order cart delete
            // Cart::where('customer_id',Auth::guard('customerlogin')->id())->delete();
            
            return redirect()->route('order.success')->with('success', $request->name);

        echo "Transaction is Successful 1";

        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');

        $sslc = new SslCommerzNotification();

        #Check order status in order tabel against the transaction id or order id.
        $order_detials = DB::table('sslorders')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status', 'currency', 'amount')->first();

        if ($order_detials->status == 'Pending') {
            $validation = $sslc->orderValidate($request->all(), $tran_id, $amount, $currency);

            if ($validation == TRUE) {
                /*
                That means IPN did not work or IPN URL was not set in your merchant panel. Here you need to update order status
                in order table as Processing or Complete.
                Here you can also sent sms or email for successfull transaction to customer
                */
                $update_product = DB::table('sslorders')
                    ->where('transaction_id', $tran_id)
                    ->update(['status' => 'Processing']);

                echo "<br >Transaction is successfully Completed 2";
            } else {
                /*
                That means IPN did not work or IPN URL was not set in your merchant panel and Transation validation failed.
                Here you need to update order status as Failed in order table.
                */
                $update_product = DB::table('sslorders')
                    ->where('transaction_id', $tran_id)
                    ->update(['status' => 'Failed']);
                echo "validation Fail";
            }
        } else if ($order_detials->status == 'Processing' || $order_detials->status == 'Complete') {
            echo "Transaction is successfully Completed 3";
            
        //     // $order_id = Order::insertGetId([
        //     //     'user_id'=>Auth::guard('customerlogin')->id(),
        //     //     'sub_total'=>$request->sub_total,
        //     //     'discount'=>$request->discount,
        //     //     'charge'=>$request->charge,
        //     //     'total'=>$request->total,
        //     //     'created_at'=>Carbon::now(),
        //     // ]);

        //     // BillingDetails::insert([
        //     //     'user_id'=>Auth::guard('customerlogin')->id(),
        //     //     'order_id'=>$order_id,
        //     //     'name'=>$request->name,
        //     //     'email'=>$request->email,
        //     //     'phone'=>$request->phone,
        //     //     'company'=>$request->company,
        //     //     'country_id'=>$request->country_id,
        //     //     'city_id'=>$request->city_id,
        //     //     'address'=>$request->address,
        //     //     'notes'=>$request->notes,
        //     //     'created_at'=>Carbon::now(),
        //     // ]);
        //     // $carts = Cart::where('customer_id',Auth::guard('customerlogin')->id())->get();
        //     // foreach ($carts as  $cart) {
        //     //     OrderProduct::insert([
        //     //         'user_id'=>Auth::guard('customerlogin')->id(),
        //     //         'order_id'=>$order_id,
        //     //         'product_id'=>$cart->product_id,
        //     //         'color_id'=>$cart->color_id,
        //     //         'size_id'=>$cart->size_id,
        //     //         'quantity'=>$cart->quantity,
        //     //         'price'=>$cart->rel_to_product->after_discount,
        //     //         'created_at'=>Carbon::now(),
        //     //     ]);
        //     //     Inventory::where('product_id',$cart->product_id)->where('color_id',$cart->color_id)->where('size_id',$cart->size_id)->decrement('quantity',$cart->quantity);
        //     // }

        //     // //SMS SEND
        //     // function sms_send() {
        //     // $url = "https://bulksmsbd.net/api/smsapi";
        //     // $api_key = "ei6gq0Dj1AvcqXnk7GbM";
        //     // $senderid = "tusharalam";
        //     // $number = $request->phone;
        //     // $message = "Thank you for purchasing our product. You total amount is: ".$request->total;
         
        //     // $data = [
        //     //     "api_key" => $api_key,
        //     //     "senderid" => $senderid,
        //     //     "number" => $number,
        //     //     "message" => $message
        //     // ];
        //     // $ch = curl_init();
        //     // curl_setopt($ch, CURLOPT_URL, $url);
        //     // curl_setopt($ch, CURLOPT_POST, 1);
        //     // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //     // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //     // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //     // $response = curl_exec($ch);
        //     // curl_close($ch);
        //     // return $response;

        // }

        // //e-mail
        // Mail::to($request->email)->send(new InvoiceSend($order_id));
        //     //after order cart delete
        //     // Cart::where('customer_id',Auth::guard('customerlogin')->id())->delete();
            
        //     return redirect()->route('order.success')->with('success', $request->name);


        } else {
            #That means something wrong happened. You can redirect customer to your product page.
            echo "Invalid Transaction";
        }


    }

    public function fail(Request $request)
    {
        $tran_id = $request->input('tran_id');

        $order_detials = DB::table('sslorders')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status', 'currency', 'amount')->first();

        if ($order_detials->status == 'Pending') {
            $update_product = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->update(['status' => 'Failed']);
            echo "Transaction is Falied";
        } else if ($order_detials->status == 'Processing' || $order_detials->status == 'Complete') {
            echo "Transaction is already Successful";
        } else {
            echo "Transaction is Invalid";
        }

    }

    public function cancel(Request $request)
    {
        $tran_id = $request->input('tran_id');

        $order_detials = DB::table('orders')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status', 'currency', 'amount')->first();

        if ($order_detials->status == 'Pending') {
            $update_product = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->update(['status' => 'Canceled']);
            echo "Transaction is Cancel";
        } else if ($order_detials->status == 'Processing' || $order_detials->status == 'Complete') {
            echo "Transaction is already Successful";
        } else {
            echo "Transaction is Invalid";
        }


    }

    public function ipn(Request $request)
    {
        #Received all the payement information from the gateway
        if ($request->input('tran_id')) #Check transation id is posted or not.
        {

            $tran_id = $request->input('tran_id');

            #Check order status in order tabel against the transaction id or order id.
            $order_details = DB::table('sslorders')
                ->where('transaction_id', $tran_id)
                ->select('transaction_id', 'status', 'currency', 'amount')->first();

            if ($order_details->status == 'Pending') {
                $sslc = new SslCommerzNotification();
                $validation = $sslc->orderValidate($request->all(), $tran_id, $order_details->amount, $order_details->currency);
                if ($validation == TRUE) {
                    /*
                    That means IPN worked. Here you need to update order status
                    in order table as Processing or Complete.
                    Here you can also sent sms or email for successful transaction to customer
                    */
                    $update_product = DB::table('sslorders')
                        ->where('transaction_id', $tran_id)
                        ->update(['status' => 'Processing']);

                    echo "Transaction is successfully Completed";
                } else {
                    /*
                    That means IPN worked, but Transation validation failed.
                    Here you need to update order status as Failed in order table.
                    */
                    $update_product = DB::table('sslorders')
                        ->where('transaction_id', $tran_id)
                        ->update(['status' => 'Failed']);

                    echo "validation Fail";
                }

            } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {

                #That means Order status already updated. No need to udate database.

                echo "Transaction is already successfully Completed";
            } else {
                #That means something wrong happened. You can redirect customer to your product page.

                echo "Invalid Transaction";
            }
        } else {
            echo "Invalid Data";
        }
    }

}
