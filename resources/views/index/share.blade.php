<!DOCTYPE html>
<html>
<head>
  <title>Weibo App</title>
  <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <div class="container">
             <div class="card ">
    <div class="card-header">
      <h5>注册</h5>
    </div>
        <div class="card-body">
            @if (count($errors) > 0)
              <div class="alert alert-danger">
                  <ul>
                      @foreach($errors->all() as $error)
                      <li>{{ $error }}</li>
                      @endforeach
                  </ul>
              </div>
            @endif
             @if(session()->has('success'))
                <div class="flash-message">
                  <p class="alert alert-success">
                    {{ session()->get('success') }}
                  </p>
                </div>
              @endif
            <form method="POST" >
                 {{ csrf_field() }}
              <div class="form-group row">
                <label for="colFormLabelLg" class="col-sm-2 col-form-label col-form-label-lg">手机号</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control form-control-lg" id="colFormLabelLg" name="phone"  value="@yield(old('phone'))" required>
                </div>
              </div>
              <div class="form-group row">
                <label for="colFormLabelLg" class="col-sm-2 col-form-label col-form-label-lg">验证码</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control form-control-lg" id="colFormLabelLg"  value="{{ old('code') }}" name="code">
                </div>
                <div class="col-sm-5">
                    <button type="submit" class="btn btn-primary" name="send" value="1">发送</button>
                </div>
              </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block" name="register" value="1">注册</button>
            </form>
        </div>
    </div>
    </div>
</div>
</body>
</html>
