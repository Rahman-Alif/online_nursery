@extends('frontend.master')

@section('content')
<!-- breadcrumb_section - start
        ================================================== -->
        <div class="breadcrumb_section">
            <div class="container">
                <ul class="breadcrumb_nav ul_li">
                    <li><a href="index.html">Home</a></li>
                    <li>Check Out</li>
                </ul>
            </div>
        </div>
        <!-- breadcrumb_section - end
        ================================================== -->


        <!-- checkout-section - start
        ================================================== -->
        <section class="checkout-section section_space">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">
                    <div class="woocommerce bg-light p-3">
                        <form name="checkout" method="post" class="checkout woocommerce-checkout" action="{{ route('order.store') }}" >
                            @csrf
                            <div class="col2-set" id="customer_details">
                                <div class="coll-1">
                                <div class="woocommerce-billing-fields">
                                    <h3>Billing Details</h3>
                                    <p class="form-row form-row form-row-first validate-required" id="billing_first_name_field">
                                        <label for="billing_first_name" class="">First Name <abbr class="required" title="required">*</abbr></label>
                                        <input type="text" class="input-text " name="name" id="billing_first_name" placeholder="" autocomplete="given-name" value="{{ Auth::guard('customerlogin')->user()->name }}" />
                                    </p>
                                    <p class="form-row form-row form-row-last validate-required validate-email" id="billing_email_field">
                                        <label for="billing_email" class="">Email Address <abbr class="required" title="required">*</abbr></label>
                                        <input type="email" class="input-text " name="email" id="billing_email" placeholder="" autocomplete="email" value="{{ Auth::guard('customerlogin')->user()->email }}" />
                                    </p>
                                    <div class="clear"></div>
                                    
                                    
                                    <p class="form-row form-row form-row-last validate-required validate-phone" id="billing_phone_field">
                                        <label for="billing_phone" class="">Phone <abbr class="required" title="required">*</abbr></label>
                                        <input type="tel" class="input-text " name="phone" id="billing_phone" placeholder="" autocomplete="tel" value="" />
                                    </p>
                                    <div class="clear"></div>
                                    
                                    <p class="form-row form-row form-row-wide address-field validate-required" id="billing_address_1_field">
                                        <label for="billing_address_1" class="">Address <abbr class="required" title="required">*</abbr></label>
                                        <input type="text" class="input-text " name="address" id="address" placeholder="Street address" autocomplete="address-line1" value="" />
                                    </p>
                                </div>
                                <p class="form-row form-row notes" id="order_comments_field">
                                        <label for="order_comments" class="">Order Notes</label>
                                        <textarea name="notes" class="input-text " id="order_comments" placeholder="Notes about your order, e.g. special notes for delivery." rows="5" cols="75"></textarea>
                                    </p>
                                </div>
                            </div>
                            <h3 id="order_review_heading">Your order</h3>
                            <div id="order_review" class="woocommerce-checkout-review-order">
                                <table class="shop_table woocommerce-checkout-review-order-table">
                                    <tr class="cart-subtotal">
                                        <th>Subtotal</th>
                                        <input type="hidden" name="sub_total" value=" {{ $cart_total }}">
                                        <td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">BDT </span>{{ $cart_total }}</span>
                                        </td>
                                    </tr>
                                    <tr class="cart-subtotal">
                                        <th>Discount</th>
                                        <input type="hidden" name="discount" value="{{ $cart_total*session('discount')/100 }}">
                                        <td><span class="woocommerce-Price-amount amount">
                                            
                                            @if (session('discount') == 0)
                                            <span>BDT 0</span>
                                            @else
                                            {{ session('discount') }} {{ (session('type') == 1?'%':'BDT') }}
                                                
                                            @endif
                                            
                                        </span>
                                        </td>
                                    </tr>
                                    <tr class="shipping">
                                        <th>Delivery Charge</th>
                                        <td data-title="Shipping">
                                        <input type="radio" class="charge"  name="charge" value="70"> Inside Dhaka
                                        <br>
                                        <input type="radio" class="charge" name="charge" value="120"> Outside Dhaka
                                        </td>
                                    </tr>
                                    <tr class="order-total">
                                        <th>Total</th>
                                        <input type="hidden" id="total2" name="total" value="{{ (session('type') == 1?$cart_total - ($cart_total*session('discount'))/100:$cart_total - session('discount')) }}">
                                        <td><strong><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">BDT </span>
                                              <div class="total">
                                                @php
                                                $per_total = $cart_total - ($cart_total*session('discount'))/100;
                                                $fix_total =$cart_total - session('discount');
                                              @endphp
                                            {{ (session('type') == 1?$per_total:$fix_total) }}</div>                                        
                                        </span></strong> </td>
                                    </tr>
                                </table>
                                <div id="payment" class="woocommerce-checkout-payment py-3 mt-5">
                                <ul class="wc_payment_methods payment_methods methods">
                                    <li class="wc_payment_method payment_method_cheque mb-2">
                                        <input id="payment_method_cheque" type="radio" class="input-radio" name="payment_method" value="1" checked='checked' data-order_button_text="" />
                                        <!--grop add span for radio button style-->
                                        <span class='grop-woo-radio-style'></span>
                                        <!--custom change-->
                                        <label for="payment_method_cheque">Cash On Delivery</label>
                                    {{-- </li>
                                    <li class="wc_payment_method payment_method_cheque mb-2">
                                        <input id="payment_method_cheque" type="radio" class="input-radio" name="payment_method" value="1" checked='checked' data-order_button_text="" />
                                        <!--grop add span for radio button style-->
                                        <span class='grop-woo-radio-style'></span>
                                        <!--custom change-->
                                        <label for="payment_method_ssl">SSL Commerz</label> --}}
                                    {{-- </li>
                                    <li class="wc_payment_method payment_method_paypal">
                                        <input id="payment_method_stripe" type="radio" class="input-radio" name="payment_method" value="3" data-order_button_text="Proceed to SSL Commerz" />
                                        <!--grop add span for radio button style-->
                                        <span class='grop-woo-radio-style'></span>
                                        <!--custom change-->
                                        <label for="payment_method_stripe">Stripe Payment</label>
                                    </li> --}}
                                </ul>
                                <div class="form-row place-order">
                                    <noscript>
                                        Since your browser does not support JavaScript, or it is disabled, please ensure you click the <em>Update Totals</em> button before placing your order. You may be charged more than the amount stated above if you fail to do so.
                                        <br/>
                                    </noscript>
                                    <input type="submit" class="button alt"  id="place_order" value="Place order" data-value="Place order" />
                                </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- checkout-section - end
        ================================================== -->
@endsection

@section('footer_script')
<script>
    // In your Javascript (external .js resource or <script> tag)
    $(document).ready(function() {
    $('#country_id').select2();
    });
    $(document).ready(function() {
    $('#city_id').select2();
    });

    $('#country_id').change(function(){
        let country_id = $(this).val();
       
        $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

        $.ajax({
            url:'/getCity',
            type:'POST',
            data:{'country_id':country_id},
            success:function(data){
                $('#city_id').html(data);
            }
        });
    });

    //delivery charge
    $('.charge').click(function(){
        let charge = $(this).val();
        let total = {{ (session('type') == 1?$cart_total - ($cart_total*session('discount'))/100:$cart_total - session('discount')) }};
        let total_price = parseInt(total)+parseInt(charge);
        $('.total').html(total_price);
        $('#total2').val(total_price);
    });
</script>
    
@endsection