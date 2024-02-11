<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Jekyll v3.8.5">
    <title>Checkout example · Bootstrap</title>

    <!-- Bootstrap core CSS -->
    <link href="{{asset('assets/css/bootstrap.css')}}" rel="stylesheet">


    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }
    </style>
</head>
<body class="bg-light">
<div class="container">
    <div class="py-5 text-center">
        <h2>Payfort</h2>
        <p class="lead">Below is an example form built entirely with Bootstrap’s form controls. Each required form group
            has a validation state that can be triggered by attempting to submit the form without completing it.</p>
    </div>

    <div class="row">
        <div class="col-md-4 order-md-2 mb-4">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Your cart</span>
                <span class="badge badge-secondary badge-pill">3</span>
            </h4>
            <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between lh-condensed">
                    <div>
                        <h6 class="my-0">Product name</h6>
                        <small class="text-muted">Brief description</small>
                    </div>
                    <span class="text-muted">$120</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Total (USD)</span>
                    <strong>$20</strong>
                </li>
            </ul>
        </div>
        <div class="col-md-8 order-md-1">
            <h4 class="mb-3">Payment Provider</h4>
            <form method="POST" class="needs-validation" novalidate id="providerForm">
                {{ csrf_field() }}
                <div class="d-block my-3">3
                    <div class="custom-control custom-radio">
                        <input id="payfort" name="paymentProvider" value="payfort" type="radio"
                               class="custom-control-input" checked required>
                        <label class="custom-control-label" for="payfort">Payfort</label>
                    </div>
                </div>
                <hr class="mb-4">
                <button class="btn btn-primary btn-lg btn-block" type="submit">Continue to checkout</button>
            </form>
        </div>

        <div id="tmpForm">

        </div>
    </div>

    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">&copy; 2017-2019 Company Name</p>
        <ul class="list-inline">
            <li class="list-inline-item"><a href="#">Privacy</a></li>
            <li class="list-inline-item"><a href="#">Terms</a></li>
            <li class="list-inline-item"><a href="#">Support</a></li>
        </ul>
    </footer>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script>
    $('#providerForm')
        .on('submit', function (evt) {


            evt.preventDefault();

            const provider = $('input[name="paymentProvider"]:checked').val();

            $.post('/' + provider, function (data) {
                console.log(data);




                if (data && data.type === 'redirect') {

                    window.location.href = data.url
                } else {

                    const tmpForm = $('#tmpForm');
                    const form = prepareForm(data);

                    tmpForm.append(form);

                    form.submit();
                }
            });

        });

    function prepareForm(params) {
        const {type, url, data} = params;
        const form = $('<form>', {
            action: url,
            method: 'post',
        });

        for (let key in data) {
            if (data.hasOwnProperty(key)) {
                form.append($('<input>', {
                    type: 'hidden',
                    name: key,
                    value: data[key],
                }));
            }
        }


        return form;
    }
</script>
</body>
</html>
