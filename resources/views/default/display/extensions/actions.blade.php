<form {!! HTML::attributes($attributes) !!}>
    {{ csrf_field() }}

    @foreach ($actions as $action)
        {!! $action->render() !!}
    @endforeach
</form>