<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>QBPilot form</title>

    <!-- Fonts -->

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">

    <style>

    </style>
</head>

<body>
    <div class="container">
        <h1>Hello, mail !</h1>

        @if (!empty(session('success')))
            <div class="alert alert-success">
                {{session('success')}}
            </div>
        @endif

        <form action="{{route('form.submit')}}" method="post" id="formMail">
            @csrf
            <input type="text" name="destinataires" placeholder="À" required class="form-control" value="test@test.fr">
            <input type="text" name="copieCachee" placeholder="Cc" class="form-control">
            <input type="text" name="objet" placeholder="Sans objet" class="form-control">
            <textarea name="contenu" required class="form-control">lorem ipsum</textarea>

            <input type="submit" value="Envoyer" class="btn btn-primary">


        </form>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous">
    </script>
</body>

</html>
