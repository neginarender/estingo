<html>
    <head></head>
    <body>
        <form action="{{ route('bypass.update-user') }}" method="post">
        <table>
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <tr>
            <td>Phone</td>
            <td><input type="text" name="mobile" placeholder="Phone No" required/></td>
            </tr>
            <tr>
            <td>Name</td>
            <td><input type="text" name="name" placeholder="Name" required/></td>
            </tr>
            <tr>
            <td>Email</td>
            <td><input type="email" name="email" placeholder="Email" required/></td>
            </tr>
            <tr>
                <td>
                    <button type="submit">Submit</button>
                </td>
                <td>
                    @if(session()->has('msg'))
                        {{ session()->get('msg') }}
                    @endif
                </td>
            </tr>
        </table>
        </form>
    </body>
</html>