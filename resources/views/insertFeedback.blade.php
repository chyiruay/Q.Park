@extends('layout')
@section('content')

<div class="row">
<div class="col-sm-1"></div>
<div class="col-sm-10">
<div class="card" style="background-color:lightpink; text-align:center; margin-top:3%; font-size:120%;">
    <div class="card-body">
        <strong>We value your feedback</strong>
    </div>
</div>
</div>
    <div class="col-sm-2"></div>
    <div class="col-sm-8">
        <br></br>
        <div class="card" style="margin-bottom:50px;">
                <div class="card-body" style="background-color:lightgray;">
                    <form method="POST", action="{{ route('addFeedback')}}">
                        @CSRF                   
                        <div class="form-group">
                            <label for="Feedback content"><strong>Tell us how we can improve:</strong></label>
                            <input type="text" class="form-control" id="feedbackContent" name="feedbackContent" >
                        </div>
                        <button type="submit" class="btn btn-primary" style="background-color:green; border:green; border-radius:10px;">Submit</button>
                    </form>
                </div>
            </div>
        </div>
        <br></br>
    </div>
    <div class="col-sm-3"></div>
</div>

@endsection