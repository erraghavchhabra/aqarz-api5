<link href="https://goSellJSLib.b-cdn.net/v2.0.0/css/gosell.css" rel="stylesheet"/>
<div id="root"></div>


<script type="text/javascript" src="https://goSellJSLib.b-cdn.net/v2.0.0/js/gosell.js"></script>
<script>

    goSell.config({
        containerID: "root",
        gateway: {
            publicKey: "{{config('payment.public_key_test')}}",
            merchantId: null,
            language: "{{app()->getLocale()}}",
            contactInfo: true,
            supportedCurrencies: "all",
            supportedPaymentMethods: "all",
            saveCardOption: false,
            customerCards: true,
            notifications: "standard",
            callback: (response) => {
                console.log("response", response);
            },
            onClose: () => {
                console.log("onClose Event");
            },
            backgroundImg: {
                url: "imgURL",
                opacity: "0.5",
            },
            labels: {
                cardNumber: "Card Number",
                expirationDate: "MM/YY",
                cvv: "CVV",
                cardHolder: "Name on Card",
                actionButton: "Pay",
            },
            style: {
                base: {
                    color: "#535353",
                    lineHeight: "18px",
                    fontFamily: "sans-serif",
                    fontSmoothing: "antialiased",
                    fontSize: "16px",
                    "::placeholder": {
                        color: "rgba(0, 0, 0, 0.26)",
                        fontSize: "15px",
                    },
                },
                invalid: {
                    color: "red",
                    iconColor: "#fa755a ",
                },
            },
        },
        customer: {
            id: null,
            first_name: "{{$user->onwer_name ?? 'provider'}}",
            middle_name: "",
            last_name: "",
            email: "{{$user->email ?? 'admin@gmail.com'}}",
            phone: {
                country_code: "966",
                number: "{{$user->mobile ?? '0599999999'}}",
            },
        },
        order: {
            amount: {{$subscription->price}},
            currency: "SAR",
            shipping: null,
            taxes: null,
        },
        transaction: {
            mode: "charge",
            charge: {
                saveCard: false,
                threeDSecure: true,
                description: "{{$subscription->plan->name ?? 'plan subscription'}}",
                statement_descriptor: "Sample",
                reference: {
                    transaction: "{{$subscription->id }}",
                    order: "{{ $subscription->id}}",
                },
                hashstring: "",
                metadata: {},
                receipt: {
                    email: false,
                    sms: true,
                },
                redirect: "{{url('api/platform/redirect_payment')}}",
                post: null,
            },
        },
    });
    setTimeout(() => {
        goSell.openPaymentPage();
    }, 1000);
</script>
