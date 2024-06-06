
<div id="saveActions" class="form-group">

    <input type="hidden" name="save_action" value="{{ $saveAction['active']['value'] }}">

    <div class="btn-group" role="group">

        <button type="button" class="btn btn-success submit-form">
            <span class="fa fa-save" role="presentation" aria-hidden="true"></span> &nbsp;
            <span data-value="{{ $saveAction['active']['value'] }}">{{ $saveAction['active']['label'] }}</span>
        </button>

        <div class="btn-group" role="group">
            <button id="btnGroupDrop1" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span><span class="sr-only">&#x25BC;</span></button>
            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                @foreach( $saveAction['options'] as $value => $label)
                    <a class="dropdown-item" href="javascript:void(0);" data-value="{{ $value }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

    </div>

    <a href="{{ $crud->hasAccess('list') ? url($crud->route) : url()->previous() }}" class="btn btn-default"><span class="fa fa-ban"></span> &nbsp;{{ trans('backpack::crud.cancel') }}</a>
</div>




{{--

<div id="saveActions" class="form-group">


    @if($saveAction['active']['value'] != 'save_and_edit')
        <input type="hidden" name="save_action" value="{{ $saveAction['active']['value'] }}">
    @else
        <input type="hidden" name="save_action" value="save_and_new">
    @endif

    <div class="btn-group" role="group">

        <button data-value="{{ $saveAction['active']['value'] }}" type="button" class="btn btn-success submit-form">
            <span class="fa fa-save" role="presentation" aria-hidden="true"></span> &nbsp;
            @if($saveAction['active']['value'] != 'save_and_edit')
                <span data-value="{{ $saveAction['active']['value'] }}">{{ $saveAction['active']['label'] }}</span>
            @else
                <span data-value="save_and_new">Save and new item</span>
            @endif

        </button>

        <div class="btn-group" role="group">
            <button id="btnGroupDrop1" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span><span class="sr-only">&#x25BC;</span></button>

            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="padding: 0;  min-width: 150px;">

                @foreach( $saveAction['options'] as $value => $label)
                    @if($value != 'save_and_edit')
                        <button data-value="{{ $value }}" type="button" class="btn btn-success submit-form">
                            <span class="fa fa-save" role="presentation" aria-hidden="true"></span> &nbsp;
                            <span data-value="{{ $value }}">{{ $label }}</span>
                        </button>
                        <!--<a class="dropdown-item submit-form" href="javascript:void(0);" data-value="{{ $value }}">{{ $label }}</a>-->
                    @endif
                @endforeach

            </div>
        </div>

    </div>

    <a href="{{ $crud->hasAccess('list') ? url($crud->route) : url()->previous() }}" class="btn btn-default"><span class="fa fa-ban"></span> &nbsp;{{ trans('backpack::crud.cancel') }}</a>
</div>
--}}
